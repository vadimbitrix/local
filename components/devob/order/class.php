<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use Devob\Lib\StoreLocationHelper;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use Bitrix\Main\UserPhoneAuthTable;
use Bitrix\Main\UserTable;
use Bitrix\Sale;
use Bitrix\Sale\Location\LocationTable;
use Devob\Helpers\Utilities;
use Devob\Lib\CdekApiClient;
use Devob\Lib\SmsClient;

class DevobOrderComponent extends CBitrixComponent implements Controllerable
{
    private const CURRENCY = 'RUB';
    private const DEFAULT_PACKAGE_WEIGHT = 1000; // грамм
    private const DEFAULT_PACKAGE_DIMENSION = 10; // сантиметров
    private const DEFAULT_TERMINAL_TARIFF_CODE = 368;
    private const DEFAULT_COURIER_TARIFFS = [
        136 => 'Посылка склад-склад',
        137 => 'Посылка склад-дверь',
        139 => 'Посылка дверь-дверь',
        233 => 'Экономичная посылка склад-дверь',
        483 => 'Посылка онлайн склад-дверь',
    ];
    private const SMS_SESSION_KEY = 'DEVOB_ORDER_SMS_CODES';
    private const SMS_RESEND_INTERVAL = 60;
    private const SMS_MAX_ATTEMPTS = 3;
    private const SMS_DEFAULT_LIFETIME_MINUTES = 5;

    /** @var SmsClient|null */
    private $smsClient = null;

    /** @var string|null */
    private $lastSmsError = null;

    public function configureActions(): array
    {
        $post = new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]);

        return [
            'suggestAddress' => ['prefilters' => [$post]],
            'loadTerminals'  => ['prefilters' => [$post]],
            'calculateTerminalDelivery' => ['prefilters' => [$post]],
            'calculateCourierDelivery' => ['prefilters' => [$post]],
            'getBasketCitiesInfo' => ['prefilters' => [$post]],
            'createOrder'    => ['prefilters' => [$post]],
            'sendSmsCode'    => ['prefilters' => [$post]],
            'verifySmsCode'  => ['prefilters' => [$post]],
        ];
    }

    public function executeComponent()
    {
        $basketData = $this->collectBasket();

        $this->arResult['COMPONENT_ID']      = 'devob-order';
        $this->arResult['CART']              = $basketData;
        $this->arResult['USER']              = $this->getUserData();
        $this->arResult['DELIVERY_METHODS']  = $this->getDeliveryMethods();
        $this->arResult['PAYMENT_METHODS']   = $this->getPaymentMethods();
        $this->arResult['PICKUP_HINTS']      = $this->buildPickupHints($basketData['items']);
        $this->arResult['SETTINGS']          = $this->getClientSettings();

        $this->includeComponentTemplate();
    }

    public function suggestAddressAction(string $query, int $limit = 6): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['success' => true, 'items' => []];
        }

        $client = $this->getCdekClient();
        if (!$client || !$client->isConfigured()) {
            return ['success' => false, 'error' => 'CDEK API credentials are not configured.'];
        }

        try {
            $cities = $client->searchCities($query, $limit);
        } catch (SystemException $exception) {
            return ['success' => false, 'error' => $exception->getMessage()];
        }

        if (empty($cities)) {
            return ['success' => true, 'items' => []];
        }

        $items = [];
        $pickupCache = [];

        foreach ($cities as $city) {
            if (!is_array($city)) {
                continue;
            }

            $cityName = (string)($city['city'] ?? $city['name'] ?? '');
            $regionName = (string)($city['region'] ?? $city['region_name'] ?? '');
            $countryCode = $city['country_code'] ?? null;
            $postalCodes = $city['postal_codes'] ?? [];
            if (!is_array($postalCodes)) {
                $postalCodes = $postalCodes ? [$postalCodes] : [];
            }

            $code = isset($city['code']) ? (int)$city['code'] : null;

            $hasTerminals = false;
            if ($code) {
                if (!array_key_exists($code, $pickupCache)) {
                    try {
                        $pickupCache[$code] = $client->hasPickupPoints($code);
                    } catch (SystemException $exception) {
                        $pickupCache[$code] = false;
                    }
                }

                $hasTerminals = $pickupCache[$code];
            }

            $valueParts = array_filter([$cityName, $regionName], static fn($value) => $value !== null && $value !== '');
            if (empty($valueParts) && !empty($city['name'])) {
                $valueParts[] = (string)$city['name'];
            }

            $value = implode(', ', $valueParts);

            $items[] = [
                'value' => $value,
                'unrestrictedValue' => $value,
                'city' => $cityName,
                'region' => $regionName,
                'country' => $city['country'] ?? null,
                'countryCode' => $countryCode,
                'postalCode' => isset($postalCodes[0]) ? (string)$postalCodes[0] : '',
                'fiasId' => $city['fias_guid'] ?? $city['fias'] ?? null,
                'kladrId' => $city['kladr_code'] ?? null,
                'cdekCityCode' => $code,
                'hasTerminals' => $hasTerminals,
                'geoLat' => isset($city['latitude']) ? (float)$city['latitude'] : null,
                'geoLon' => isset($city['longitude']) ? (float)$city['longitude'] : null,
                'timeZone' => $city['time_zone'] ?? null,
            ];
        }

        return ['success' => true, 'items' => $items];
    }

    public function loadTerminalsAction(array $address): array
    {
        $client = $this->getCdekClient();
        if (!$client || !$client->isConfigured()) {
            return ['success' => false, 'error' => 'CDEK API credentials are not configured.'];
        }

        $cityCode = $this->resolveCityCode($client, $address);
        if (!$cityCode) {
            return ['success' => false, 'error' => 'Не удалось определить код города СДЭК для выбранного адреса'];
        }

        try {
            $points = $client->getDeliveryPoints([
                'city_code' => $cityCode,
                'type' => 'PVZ',
                'is_handout' => true,
            ]);
        } catch (SystemException $exception) {
            return ['success' => false, 'error' => $exception->getMessage()];
        }

        $items = [];
        foreach ($points as $point) {
            $items[] = [
                'code' => $point['code'] ?? '',
                'name' => $point['name'] ?? '',
                'address' => $point['location']['address'] ?? '',
                'workTime' => $point['work_time'] ?? '',
                'phones' => $this->formatPhones($point['phones'] ?? []),
                'note' => $point['note'] ?? '',
                'latitude' => isset($point['location']['latitude']) ? (float)$point['location']['latitude'] : null,
                'longitude' => isset($point['location']['longitude']) ? (float)$point['location']['longitude'] : null,
            ];
        }

        return [
            'success' => true,
            'items' => $items,
            'yandexApiKey' => $this->getClientSettings()['yandexApiKey'] ?? '',
        ];
    }

    public function calculateTerminalDeliveryAction(array $address): array
    {
        // Проверяем валидность корзины
        $validation = $this->validateBasketCities();

        if (!$validation['valid']) {
            $errorMessage = $validation['error'] ?? 'В корзине товары из разных городов. Пожалуйста, оформляйте товары из одного города отдельно.';

            return [
                'success' => false,
                'error' => $errorMessage,
                'conflicts' => $validation['conflicts'] ?? [],
            ];
        }

        $client = $this->getCdekClient();
        if (!$client || !$client->isConfigured()) {
            return ['success' => false, 'error' => 'CDEK API credentials are not configured.'];
        }

        $cityCode = $this->resolveCityCode($client, $address);
        if (!$cityCode) {
            return ['success' => false, 'error' => 'Не удалось определить код города СДЭК для выбранного адреса'];
        }

        $packages = $this->buildDeliveryPackages();
        if (empty($packages)) {
            return ['success' => false, 'error' => 'Корзина пуста или содержит товары из разных городов'];
        }

        $senderCityCode = $packages[0]['from_city_code'] ?? null;

        if (!$senderCityCode) {
            return ['success' => false, 'error' => 'Не удалось определить город отправки товаров'];
        }

        // Убираем from_city_code из пакета
        $packageData = $packages[0];
        unset($packageData['from_city_code']);

        // Пробуем тарифы для маркетплейса (склад-склад)
        $tariffs = [
            368 => 'Посылка онлайн склад-склад',
            136 => 'Посылка склад-склад',
            234 => 'Экономичная посылка склад-склад',
        ];

        $errors = [];

        foreach ($tariffs as $tariffCode => $tariffName) {
            try {
                $response = $client->calculateTariff($cityCode, [$packageData], [
                    'from_city_code' => $senderCityCode,
                    'tariff_code' => $tariffCode,
                ]);

                $price = isset($response['total_sum']) ? (float)$response['total_sum'] : null;
                if ($price === null || $price <= 0) {
                    continue; // Пробуем следующий тариф
                }

                // Форматируем цену и декодируем HTML-сущности
                $formattedPrice = Utilities::formatCurrency($price, self::CURRENCY);
                $formattedPrice = html_entity_decode($formattedPrice, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                return [
                    'success' => true,
                    'price' => $price,
                    'pricePrint' => $formattedPrice,
                    'tariffCode' => $tariffCode,
                    'tariffName' => $tariffName,
                    'raw' => $response,
                    'fromCityCode' => $senderCityCode,
                    'fromCityName' => $validation['cityName'] ?? '',
                ];

            } catch (SystemException $exception) {
                $errors[$tariffCode] = $exception->getMessage();
                continue;
            }
        }

        // Если ни один тариф не подошёл
        $errorDetails = implode('; ', array_map(
            fn($code, $msg) => "Тариф {$code}: {$msg}",
            array_keys($errors),
            $errors
        ));

        return [
            'success' => false,
            'error' => "Доставка СДЭК недоступна для маршрута {$validation['cityName']} → выбранный город. {$errorDetails}"
        ];
    }

    public function calculateCourierDeliveryAction(array $address): array
    {
        $validation = $this->validateBasketCities();

        if (!$validation['valid']) {
            $errorMessage = $validation['error'] ?? 'В корзине товары из разных городов. Пожалуйста, оформляйте товары из одного города отдельно.';

            return [
                'success' => false,
                'error' => $errorMessage,
                'conflicts' => $validation['conflicts'] ?? [],
            ];
        }

        $client = $this->getCdekClient();
        if (!$client || !$client->isConfigured()) {
            return ['success' => false, 'error' => 'CDEK API credentials are not configured.'];
        }

        $cityCode = $this->resolveCityCode($client, $address);
        if (!$cityCode) {
            return ['success' => false, 'error' => 'Не удалось определить код города СДЭК для выбранного адреса'];
        }

        $packages = $this->buildDeliveryPackages();
        if (empty($packages)) {
            return ['success' => false, 'error' => 'Корзина пуста или содержит товары из разных городов'];
        }

        $packageData = $packages[0];
        $senderCityCode = $packageData['from_city_code'] ?? null;
        unset($packageData['from_city_code']);

        if (!$senderCityCode) {
            $senderCityCode = $this->getSenderCityCode();
        }

        if (!$senderCityCode) {
            return ['success' => false, 'error' => 'Не удалось определить город отправки товаров'];
        }

        $tariffs = $this->getCourierTariffs();
        if (empty($tariffs)) {
            return ['success' => false, 'error' => 'Не настроены тарифы курьерской доставки СДЭК'];
        }

        $errors = [];

        foreach ($tariffs as $tariffCode => $tariffName) {
            try {
                $response = $client->calculateTariff($cityCode, [$packageData], [
                    'from_city_code' => $senderCityCode,
                    'tariff_code' => $tariffCode,
                ]);

                $price = isset($response['total_sum']) ? (float)$response['total_sum'] : null;
                if ($price === null || $price <= 0) {
                    continue;
                }

                $formattedPrice = Utilities::formatCurrency($price, self::CURRENCY);
                $formattedPrice = html_entity_decode($formattedPrice, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                return [
                    'success' => true,
                    'price' => $price,
                    'pricePrint' => $formattedPrice,
                    'tariffCode' => $tariffCode,
                    'tariffName' => $tariffName,
                    'raw' => $response,
                    'fromCityCode' => $senderCityCode,
                    'fromCityName' => $validation['cityName'] ?? '',
                ];
            } catch (SystemException $exception) {
                $errors[$tariffCode] = $exception->getMessage();
                continue;
            }
        }

        $errorDetails = implode('; ', array_map(
            static fn($code, $msg) => "Тариф {$code}: {$msg}",
            array_keys($errors),
            $errors
        ));

        $cityName = $validation['cityName'] ?? '';
        $cityPart = $cityName !== '' ? $cityName . ' → выбранный адрес' : 'указанный маршрут';

        return [
            'success' => false,
            'error' => "Курьерская доставка СДЭК недоступна для {$cityPart}. {$errorDetails}",
        ];
    }

    public function sendSmsCodeAction(string $phone, array $recipient = []): array
    {
        $phone = $this->normalizePhone($phone);
        $recipient = $this->sanitizeRecipientData($recipient);
        if ($phone === '') {
            return [
                'success' => false,
                'error' => 'Укажите корректный номер телефона',
            ];
        }

        $validationError = $this->validateRecipientData($recipient);
        if ($validationError !== null) {
            return [
                'success' => false,
                'error' => $validationError,
            ];
        }

        $sessionData = $this->getSmsSessionData($phone);
        $now = time();
        if ($sessionData && isset($sessionData['last_sent'])) {
            $elapsed = $now - (int)$sessionData['last_sent'];
            if ($elapsed < self::SMS_RESEND_INTERVAL) {
                $retryAfter = self::SMS_RESEND_INTERVAL - $elapsed;

                return [
                    'success' => false,
                    'error' => 'Повторная отправка будет доступна чуть позже',
                    'retryAfter' => $retryAfter,
                ];
            }
        }

        $code = $this->generateSmsCode();
        if (!$this->sendSmsMessage($phone, $code)) {
            return [
                'success' => false,
                'error' => $this->getLastSmsError() ?: 'Не удалось отправить SMS. Попробуйте позже',
                'retryAfter' => self::SMS_RESEND_INTERVAL,
            ];
        }

        $this->saveSmsSession($phone, $code, $recipient);

        $lifetime = $this->getSmsCodeLifetime();

        return [
            'success' => true,
            'message' => 'Код подтверждения отправлен на указанный номер',
            'retryAfter' => self::SMS_RESEND_INTERVAL,
            'expiresIn' => $lifetime * 60,
        ];
    }

    public function verifySmsCodeAction(string $phone, string $code, array $recipient = []): array
    {
        global $USER;

        $phone = $this->normalizePhone($phone);
        if ($phone === '') {
            return [
                'success' => false,
                'error' => 'Укажите корректный номер телефона',
            ];
        }

        $code = trim((string)$code);
        if ($code === '') {
            return [
                'success' => false,
                'error' => 'Введите код из SMS',
            ];
        }

        $sessionData = $this->getSmsSessionData($phone);
        if (!$sessionData) {
            return [
                'success' => false,
                'error' => 'Сначала запросите код подтверждения',
            ];
        }

        if (!empty($sessionData['verified'])) {
            $userId = (int)($sessionData['user_id'] ?? 0);
            $authorized = is_object($USER) && method_exists($USER, 'IsAuthorized') && $USER->IsAuthorized();

            return [
                'success' => true,
                'message' => 'Телефон уже подтверждён',
                'userId' => $userId > 0 ? $userId : null,
                'authorized' => $authorized,
                'phone' => $phone,
            ];
        }

        $now = time();
        $expiresAt = (int)($sessionData['expires'] ?? 0);
        if ($expiresAt > 0 && $now > $expiresAt) {
            $this->clearSmsSession($phone);

            return [
                'success' => false,
                'error' => 'Срок действия кода истёк. Запросите новый код',
            ];
        }

        $attempts = (int)($sessionData['attempts'] ?? 0);
        if ($attempts >= self::SMS_MAX_ATTEMPTS) {
            $this->clearSmsSession($phone);

            return [
                'success' => false,
                'error' => 'Превышено количество попыток. Запросите новый код',
            ];
        }

        if ((string)($sessionData['code'] ?? '') !== $code) {
            $attempts++;
            $this->updateSmsSession($phone, ['attempts' => $attempts]);

            return [
                'success' => false,
                'error' => 'Неверный код подтверждения',
                'attemptsLeft' => max(self::SMS_MAX_ATTEMPTS - $attempts, 0),
            ];
        }

        $sessionRecipient = isset($sessionData['recipient']) && is_array($sessionData['recipient'])
            ? $sessionData['recipient']
            : [];

        $recipient = $this->mergeRecipientData($sessionRecipient, $recipient);
        $validationError = $this->validateRecipientData($recipient);
        if ($validationError !== null) {
            return [
                'success' => false,
                'error' => $validationError,
            ];
        }
        $userId = $this->getOrCreateUserByPhone($phone, $recipient);
        if ($userId <= 0) {
            return [
                'success' => false,
                'error' => 'Не удалось создать пользователя для указанного номера',
            ];
        }

        $authorized = false;
        if (is_object($USER) && method_exists($USER, 'Authorize')) {
            $authorized = (bool)$USER->Authorize($userId, true, false);
        }

        $this->updateSmsSession($phone, [
            'verified' => true,
            'verified_at' => $now,
            'user_id' => $userId,
            'code' => null,
            'attempts' => $attempts,
            'recipient' => $recipient,
        ]);

        return [
            'success' => true,
            'message' => 'Телефон успешно подтверждён',
            'userId' => $userId,
            'authorized' => $authorized || (is_object($USER) && $USER->IsAuthorized()),
            'phone' => $phone,
        ];
    }

    public function createOrderAction(array $form): array
    {
        $errors = [];

        $form['deliveryType'] = (string)($form['deliveryType'] ?? '');
        $form['paymentType'] = (string)($form['paymentType'] ?? '');
        $form['recipient'] = is_array($form['recipient'] ?? null) ? $form['recipient'] : [];
        $form['courier'] = is_array($form['courier'] ?? null) ? $form['courier'] : [];
        $form['terminal'] = is_array($form['terminal'] ?? null) ? $form['terminal'] : [];
        $form['address'] = is_array($form['address'] ?? null) ? $form['address'] : [];

        $userData = $this->getUserData();
        $isAuthorized = !empty($userData['isAuthorized']);
        if ($isAuthorized) {
            $authorizedPhone = preg_replace('/[^0-9+]/', '', (string)($userData['phone'] ?? ''));
            $form['recipient']['phone'] = $authorizedPhone;
        }

        if ($form['deliveryType'] === '') {
            $errors[] = 'Не выбран способ доставки';
        }

        if ($form['deliveryType'] === 'courier') {
            if (empty($form['courier']['city']) || empty($form['courier']['street']) || empty($form['courier']['house'])) {
                $errors[] = 'Для доставки курьером необходимо заполнить адрес полностью';
            }
        }

        if ($form['deliveryType'] === 'terminal' && empty($form['terminal']['code'])) {
            $errors[] = 'Выберите пункт выдачи заказа';
        }

        if ($form['paymentType'] === '') {
            $errors[] = 'Выберите способ оплаты';
        }

        if (!empty($form['recipient']['phone'])) {
            $form['recipient']['phone'] = preg_replace('/[^0-9+]/', '', $form['recipient']['phone']);
        } else {
            $errors[] = 'Укажите номер телефона получателя';
        }

        if (!empty($form['recipient']['email']) && !filter_var($form['recipient']['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Укажите корректный e-mail получателя';
        }

        if (empty($form['recipient']['firstName']) && empty($form['recipient']['lastName'])) {
            $errors[] = 'Укажите имя получателя';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        if (!Loader::includeModule('sale')) {
            return ['success' => false, 'errors' => ['Модуль Интернет-магазина не установлен']];
        }

        $basket = $this->loadBasket();
        if (!$basket || $basket->isEmpty()) {
            return ['success' => false, 'errors' => ['Корзина пуста']];
        }

        $siteId = Context::getCurrent()->getSite();

        $personTypeId = $this->resolvePersonTypeId($siteId);
        if ($personTypeId <= 0) {
            return ['success' => false, 'errors' => ['Не настроены типы плательщиков для сайта']];
        }

        $deliveryServiceId = $this->resolveDeliveryServiceId($form['deliveryType']);
        if ($deliveryServiceId <= 0) {
            return ['success' => false, 'errors' => ['Не удалось определить службу доставки для выбранного способа']];
        }

        $paySystemId = $this->resolvePaySystemId($form['paymentType']);
        if ($paySystemId <= 0) {
            return ['success' => false, 'errors' => ['Не удалось определить способ оплаты']];
        }

        global $USER;
        $userId = (is_object($USER) && method_exists($USER, 'IsAuthorized') && $USER->IsAuthorized())
            ? (int)$USER->GetID()
            : 0;

        $normalizedPhone = $this->normalizePhone((string)($form['recipient']['phone'] ?? ''));
        if ($userId <= 0) {
            $smsData = $normalizedPhone !== '' ? $this->getSmsSessionData($normalizedPhone) : null;
            if (!$smsData || empty($smsData['verified'])) {
                return ['success' => false, 'errors' => ['Подтвердите номер телефона перед оформлением заказа']];
            }

            $userId = (int)($smsData['user_id'] ?? 0);
            if ($userId <= 0) {
                $userId = $this->getOrCreateUserByPhone($normalizedPhone, $form['recipient']);
                if ($userId <= 0) {
                    return ['success' => false, 'errors' => ['Не удалось определить пользователя для заказа']];
                }
                $this->updateSmsSession($normalizedPhone, ['user_id' => $userId]);
            }
        }

        try {
            $order = Sale\Order::create($siteId, $userId);
            $order->setPersonTypeId($personTypeId);
            $order->setField('CURRENCY', self::CURRENCY);
            $order->setBasket($basket);

            $comment = trim((string)($form['courier']['comment'] ?? ''));
            if ($comment !== '') {
                $order->setField('USER_DESCRIPTION', $comment);
            }

            $this->fillOrderProperties($order, $form);

            $shipmentCollection = $order->getShipmentCollection();
            $deliveryService = Sale\Delivery\Services\Manager::getObjectById($deliveryServiceId);
            if (!$deliveryService) {
                return ['success' => false, 'errors' => ['Служба доставки не найдена']];
            }

            $shipment = $shipmentCollection->createItem($deliveryService);
            $shipment->setField('DELIVERY_ID', $deliveryServiceId);
            $shipment->setField('DELIVERY_NAME', $deliveryService->getName());

            $shipmentItemCollection = $shipment->getShipmentItemCollection();
            foreach ($basket as $basketItem) {
                $shipmentItem = $shipmentItemCollection->createItem($basketItem);
                $shipmentItem->setQuantity($basketItem->getQuantity());
            }

            $paymentCollection = $order->getPaymentCollection();
            $paySystemService = Sale\PaySystem\Manager::getObjectById($paySystemId);
            if (!$paySystemService) {
                return ['success' => false, 'errors' => ['Платёжная система не найдена']];
            }

            $payment = $paymentCollection->createItem($paySystemService);
            $payment->setField('PAY_SYSTEM_ID', $paySystemId);
            $payment->setField('PAY_SYSTEM_NAME', $paySystemService->getField('NAME'));

            $order->doFinalAction(true);
            $payment->setField('SUM', $order->getPrice());
            $payment->setField('CURRENCY', $order->getCurrency());

            $saveResult = $order->save();
            if (!$saveResult->isSuccess()) {
                return ['success' => false, 'errors' => $saveResult->getErrorMessages()];
            }

            $orderId = (int)$order->getId();
            $accountNumber = (string)$order->getField('ACCOUNT_NUMBER');

            if (method_exists(Sale\Basket::class, 'deleteAll')) {
                Sale\Basket::deleteAll(Sale\Fuser::getId(), $siteId);
            }

            $orderDate = $order->getField('DATE_INSERT');
            $orderDateIso = null;
            if ($orderDate instanceof DateTime) {
                $orderDateIso = $orderDate->format('c');
            }

            if ($normalizedPhone !== '') {
                $this->clearSmsSession($normalizedPhone);
            }

            // Получаем данные для оплаты
            $paymentData = $this->getPaymentFormData($payment);

            return [
                'success' => true,
                'message' => $accountNumber
                    ? 'Заказ №' . $accountNumber . ' успешно оформлен'
                    : 'Заказ успешно оформлен',
                'orderId' => $orderId,
                'accountNumber' => $accountNumber,
                'orderDate' => $orderDateIso,
                'payment' => $paymentData,
            ];
        } catch (\Throwable $exception) {
            return ['success' => false, 'errors' => [$exception->getMessage()]];
        }
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string)$phone);

        if ($digits === '') {
            return '';
        }

        if (substr($digits, 0, 1) === '8') {
            $digits = '7' . substr($digits, 1);
        }

        if (substr($digits, 0, 1) !== '7') {
            $digits = '7' . $digits;
        }

        return strlen($digits) >= 11 ? $digits : '';
    }

    private function generateSmsCode(): string
    {
        try {
            return (string)random_int(100000, 999999);
        } catch (\Throwable $exception) {
            return (string)mt_rand(1000, 9999);
        }
    }

    private function getSmsCodeLifetime(): int
    {
        $lifetime = (int)Option::get('askaron.settings', 'UF_SMS_CODE_LIFETIME', self::SMS_DEFAULT_LIFETIME_MINUTES);

        return $lifetime > 0 ? $lifetime : self::SMS_DEFAULT_LIFETIME_MINUTES;
    }

    private function getSmsSessionData(string $phone): ?array
    {
        $phone = $this->normalizePhone($phone);
        if ($phone === '') {
            return null;
        }

        if (!isset($_SESSION[self::SMS_SESSION_KEY][$phone]) || !is_array($_SESSION[self::SMS_SESSION_KEY][$phone])) {
            return null;
        }

        return $_SESSION[self::SMS_SESSION_KEY][$phone];
    }

    private function saveSmsSession(string $phone, string $code, array $recipient = []): void
    {
        $phone = $this->normalizePhone($phone);
        if ($phone === '') {
            return;
        }

        if (!isset($_SESSION[self::SMS_SESSION_KEY]) || !is_array($_SESSION[self::SMS_SESSION_KEY])) {
            $_SESSION[self::SMS_SESSION_KEY] = [];
        }

        $existing = $this->getSmsSessionData($phone);
        $lifetimeSeconds = $this->getSmsCodeLifetime() * 60;
        $recipientData = $this->mergeRecipientData($existing['recipient'] ?? [], $recipient);

        $_SESSION[self::SMS_SESSION_KEY][$phone] = [
            'code' => $code,
            'attempts' => 0,
            'last_sent' => time(),
            'expires' => time() + $lifetimeSeconds,
            'verified' => false,
            'verified_at' => null,
            'user_id' => $existing['user_id'] ?? null,
            'recipient' => $recipientData,
        ];
    }

    private function sanitizeRecipientData(array $recipient): array
    {
        return [
            'firstName' => trim((string)($recipient['firstName'] ?? '')),
            'lastName' => trim((string)($recipient['lastName'] ?? '')),
            'middleName' => trim((string)($recipient['middleName'] ?? '')),
            'email' => trim((string)($recipient['email'] ?? '')),
        ];
    }

    private function mergeRecipientData(array $original, array $override): array
    {
        $original = $this->sanitizeRecipientData($original);
        $override = $this->sanitizeRecipientData($override);

        foreach ($override as $key => $value) {
            if ($value !== '') {
                $original[$key] = $value;
            }
        }

        return $original;
    }

    private function validateRecipientData(array $recipient): ?string
    {
        $hasName = ($recipient['firstName'] ?? '') !== '' || ($recipient['lastName'] ?? '') !== '';
        if (!$hasName) {
            return 'Укажите имя получателя';
        }

        $email = $recipient['email'] ?? '';
        if ($email === '') {
            return 'Укажите e-mail получателя';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Укажите корректный e-mail получателя';
        }

        return null;
    }

    private function updateSmsSession(string $phone, array $patch): void
    {
        $phone = $this->normalizePhone($phone);
        if ($phone === '') {
            return;
        }

        if (!isset($_SESSION[self::SMS_SESSION_KEY][$phone]) || !is_array($_SESSION[self::SMS_SESSION_KEY][$phone])) {
            return;
        }

        $_SESSION[self::SMS_SESSION_KEY][$phone] = array_merge(
            $_SESSION[self::SMS_SESSION_KEY][$phone],
            $patch
        );
    }

    private function clearSmsSession(string $phone): void
    {
        $phone = $this->normalizePhone($phone);
        if ($phone === '') {
            return;
        }

        if (isset($_SESSION[self::SMS_SESSION_KEY][$phone])) {
            unset($_SESSION[self::SMS_SESSION_KEY][$phone]);
        }
    }

    private function getSmsClient(): ?SmsClient
    {
        if ($this->smsClient instanceof SmsClient) {
            return $this->smsClient;
        }

        $login = Option::get('askaron.settings', 'UF_SMS_LOGIN', '');
        $password = Option::get('askaron.settings', 'UF_SMS_PASSWORD', '');

        if ($login === '' || $password === '') {
            $this->setLastSmsError('Сервис SMS не настроен');
            $this->smsClient = null;

            return null;
        }

        $callbackUrl = Option::get('askaron.settings', 'UF_SMS_CALLBACK_URL', '');
        $debug = Option::get('askaron.settings', 'UF_SMS_DEBUG', 'N') === 'Y';

        try {
            $this->smsClient = new SmsClient(
                login: $login,
                password: $password,
                usePost: true,
                useHttps: true,
                charset: 'utf-8',
                debug: $debug,
                callbackUrl: $callbackUrl !== '' ? $callbackUrl : null,
                logFile: $_SERVER['DOCUMENT_ROOT'] . '/local/logs/sms_order.log'
            );
        } catch (\Throwable $exception) {
            $this->smsClient = null;
            $this->setLastSmsError($exception->getMessage());

            return null;
        }

        return $this->smsClient;
    }

    private function sendSmsMessage(string $phone, string $code): bool
    {
        $this->setLastSmsError(null);

        $client = $this->getSmsClient();
        if (!$client) {
            return false;
        }

        $template = Option::get('askaron.settings', 'UF_SMS_TEMPLATE', 'Код подтверждения: {CODE}');
        $message = str_replace('{CODE}', $code, $template);

        try {
            if (method_exists($client, 'optimizeMessage')) {
                $message = $client->optimizeMessage($message, 0);
            }

            $sender = Option::get('askaron.settings', 'UF_SMS_SENDER', false);
            if ($sender === '') {
                $sender = false;
            }

            $result = $client->sendSms(
                phones: $phone,
                message: $message,
                translit: 0,
                sender: $sender
            );

            $count = (int)($result[1] ?? 0);
            if ($count <= 0) {
                $this->setLastSmsError('Сервис SMS отклонил запрос');

                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            $this->setLastSmsError($exception->getMessage());

            return false;
        }
    }

    private function setLastSmsError(?string $message): void
    {
        $this->lastSmsError = $message;
    }

    private function getLastSmsError(): ?string
    {
        return $this->lastSmsError;
    }

    private function getOrCreateUserByPhone(string $phone, array $recipient = []): int
    {
        $phone = $this->normalizePhone($phone);
        if ($phone === '') {
            return 0;
        }
        // Сначала ищем в UserPhoneAuthTable
        $phoneAuth = UserPhoneAuthTable::getList([
            'select' => ['USER_ID'],
            'filter' => ['=PHONE_NUMBER' => $phone],
            'limit' => 1,
        ])->fetch();
        if ($phoneAuth) {
            $user = UserTable::getList([
                'select' => ['ID', 'ACTIVE', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'],
                'filter' => ['=ID' => (int)$phoneAuth['USER_ID']],
                'limit' => 1,
            ])->fetch();
        } else {
            // Если не найден в UserPhoneAuthTable, ищем в обычных полях
            $user = UserTable::getList([
                'select' => ['ID', 'ACTIVE', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'],
                'filter' => [
                    'LOGIC' => 'OR',
                    '=LOGIN' => $phone,
                    '=PERSONAL_PHONE' => $phone,
                    '=PERSONAL_MOBILE' => $phone,
                ],
                'limit' => 1,
            ])->fetch();
        }


        if ($user) {
            $userId = (int)$user['ID'];

            if (($user['ACTIVE'] ?? 'Y') !== 'Y') {
                $userEntity = new \CUser();
                $userEntity->Update($userId, ['ACTIVE' => 'Y']);
            }

            $this->updateUserProfileFields($userId, $recipient, $phone, $user);
            $this->ensurePhoneAuth($userId, $phone);

            return $userId;
        }

        $password = $this->generateRandomPassword();
        $email = trim((string)($recipient['email'] ?? ''));
        if ($email === '') {
            $email = $phone . '@guest.local';
        }

        $fields = [
            'LOGIN' => $phone,
            'PHONE_NUMBER' => $phone,
            'PERSONAL_MOBILE' => $phone,
            'PERSONAL_PHONE' => $phone,
            'NAME' => trim((string)($recipient['firstName'] ?? '')) ?: 'Покупатель',
            'LAST_NAME' => trim((string)($recipient['lastName'] ?? '')),
            'SECOND_NAME' => trim((string)($recipient['middleName'] ?? '')),
            'EMAIL' => $email,
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $password,
            'ACTIVE' => 'Y',
        ];

        $userEntity = new \CUser();
        $userId = (int)$userEntity->Add($fields);
        if ($userId <= 0) {
            $this->setLastSmsError($userEntity->LAST_ERROR ?: 'Не удалось создать пользователя');

            return 0;
        }

        $this->ensurePhoneAuth($userId, $phone);

        return $userId;
    }

    private function updateUserProfileFields(int $userId, array $recipient, string $phone, array $existing = []): void
    {
        $existing = $existing ?: UserTable::getList([
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'],
            'filter' => ['=ID' => $userId],
        ])->fetch() ?: [];

        $fields = [];

        $firstName = trim((string)($recipient['firstName'] ?? ''));
        if ($firstName !== '' && ($existing['NAME'] ?? '') === '') {
            $fields['NAME'] = $firstName;
        }

        $lastName = trim((string)($recipient['lastName'] ?? ''));
        if ($lastName !== '' && ($existing['LAST_NAME'] ?? '') === '') {
            $fields['LAST_NAME'] = $lastName;
        }

        $middleName = trim((string)($recipient['middleName'] ?? ''));
        if ($middleName !== '' && ($existing['SECOND_NAME'] ?? '') === '') {
            $fields['SECOND_NAME'] = $middleName;
        }

        $email = trim((string)($recipient['email'] ?? ''));
        if ($email !== '' && ($existing['EMAIL'] ?? '') === '') {
            $fields['EMAIL'] = $email;
        }

        $fields['PERSONAL_MOBILE'] = $phone;

        if ($fields) {
            $user = new \CUser();
            $user->Update($userId, $fields);
        }
    }

    private function ensurePhoneAuth(int $userId, string $phone): void
    {
        $phone = $this->normalizePhone($phone);
        if ($phone === '' || $userId <= 0) {
            return;
        }

        $existing = UserPhoneAuthTable::getList([
            'select' => ['USER_ID', 'PHONE_NUMBER'],
            'filter' => ['=USER_ID' => $userId],
            'limit' => 1,
        ])->fetch();

        try {
            if ($existing) {
                if ($existing['PHONE_NUMBER'] !== $phone) {
                    UserPhoneAuthTable::update($userId, ['PHONE_NUMBER' => $phone]);
                }
            } else {
                UserPhoneAuthTable::add([
                    'USER_ID' => $userId,
                    'PHONE_NUMBER' => $phone,
                ]);
            }
        } catch (\Throwable $exception) {
            // Игнорируем ошибки обновления записи авторизации по телефону
        }
    }

    private function generateRandomPassword(): string
    {
        try {
            $random = bin2hex(random_bytes(4));
        } catch (\Throwable $exception) {
            $random = (string)mt_rand(100000, 999999);
        }

        return $random . 'Aa1';
    }

    private function resolvePersonTypeId(string $siteId): int
    {
        static $cache = [];

        if (isset($cache[$siteId])) {
            return $cache[$siteId];
        }

        $personTypes = Sale\PersonType::load($siteId);
        if (!empty($personTypes)) {
            $cache[$siteId] = (int)array_key_first($personTypes);
            return $cache[$siteId];
        }

        $cache[$siteId] = 0;

        return 0;
    }

    private function resolveDeliveryServiceId(string $deliveryType): int
    {
        $deliveryType = strtolower(trim($deliveryType));
        if ($deliveryType === '') {
            return 0;
        }

        $optionMap = [
            'terminal' => 'UF_DELIVERY_TERMINAL_SERVICE_ID',
            'courier' => 'UF_DELIVERY_COURIER_SERVICE_ID',
            'pickup' => 'UF_DELIVERY_PICKUP_SERVICE_ID',
        ];

        if (isset($optionMap[$deliveryType])) {
            $serviceId = (int)Option::get('askaron.settings', $optionMap[$deliveryType], 0);
            if ($serviceId > 0) {
                return $serviceId;
            }
        }

        $keywords = [
            'terminal' => ['terminal', 'терминал', 'пвз', 'pvz', 'cdek'],
            'courier' => ['courier', 'курьер'],
            'pickup' => ['pickup', 'самовывоз'],
        ];

        $services = Sale\Delivery\Services\Manager::getActiveList();
        if (!empty($services)) {
            foreach ($services as $service) {
                $name = mb_strtolower((string)($service['NAME'] ?? ''));
                foreach ($keywords[$deliveryType] ?? [] as $keyword) {
                    if ($keyword !== '' && mb_strpos($name, $keyword) !== false) {
                        return (int)$service['ID'];
                    }
                }
            }

            $firstService = reset($services);
            if (!empty($firstService['ID'])) {
                return (int)$firstService['ID'];
            }
        }

        return 0;
    }

    private function resolvePaySystemId(string $paymentType): int
    {
        $paymentType = strtolower(trim($paymentType));
        if ($paymentType === '') {
            return 0;
        }

        $optionMap = [
            'card-online' => 'UF_PAY_SYSTEM_CARD_ONLINE_ID',
        ];

        if (isset($optionMap[$paymentType])) {
            $paySystemId = (int)Option::get('askaron.settings', $optionMap[$paymentType], 0);
            if ($paySystemId > 0) {
                return $paySystemId;
            }
        }

        $keywords = [
            'card-online' => ['online', 'онлайн', 'card', 'карта'],
        ];

        $paySystems = Sale\PaySystem\Manager::getList(['filter' => ['ACTIVE' => 'Y']])->fetchAll();
        if (!empty($paySystems)) {
            foreach ($paySystems as $paySystem) {
                $name = mb_strtolower((string)($paySystem['NAME'] ?? ''));
                foreach ($keywords[$paymentType] ?? [] as $keyword) {
                    if ($keyword !== '' && mb_strpos($name, $keyword) !== false) {
                        return (int)$paySystem['ID'];
                    }
                }
            }

            $firstPaySystem = reset($paySystems);
            if (!empty($firstPaySystem['ID'])) {
                return (int)$firstPaySystem['ID'];
            }
        }

        return 0;
    }

    private function fillOrderProperties(Sale\Order $order, array $form): void
    {
        $propertyCollection = $order->getPropertyCollection();
        if (!$propertyCollection) {
            return;
        }

        $recipient = is_array($form['recipient'] ?? null) ? $form['recipient'] : [];
        $terminal = is_array($form['terminal'] ?? null) ? $form['terminal'] : [];
        $courier = is_array($form['courier'] ?? null) ? $form['courier'] : [];
        $address = is_array($form['address'] ?? null) ? $form['address'] : [];

        $phone = isset($recipient['phone']) ? preg_replace('/[^0-9+]/', '', (string)$recipient['phone']) : '';
        $email = isset($recipient['email']) ? trim((string)$recipient['email']) : '';

        $fioParts = array_filter([
            isset($recipient['lastName']) ? trim((string)$recipient['lastName']) : '',
            isset($recipient['firstName']) ? trim((string)$recipient['firstName']) : '',
            isset($recipient['middleName']) ? trim((string)$recipient['middleName']) : '',
        ]);
        $fio = trim(implode(' ', $fioParts));

        $deliveryAddress = '';
        $pickupAddress = '';
        $pickupSchedule = '';
        $pickupStoreName = '';
        $pickupStoreId = '';
        if (($form['deliveryType'] ?? '') === 'courier') {
            $addressParts = array_filter([
                isset($courier['city']) ? trim((string)$courier['city']) : '',
                isset($courier['street']) ? trim((string)$courier['street']) : '',
                isset($courier['house']) ? trim((string)$courier['house']) : '',
                isset($courier['flat']) ? trim((string)$courier['flat']) : '',
            ]);
            $deliveryAddress = implode(', ', $addressParts);
        } elseif (($form['deliveryType'] ?? '') === 'terminal') {
            $deliveryAddress = trim((string)($terminal['address'] ?? ''));
        } elseif (($form['deliveryType'] ?? '') === 'pickup') {
            $defaultPickupAddress = Option::get('askaron.settings', 'DEFAULT_PICKUP_ADDRESS', 'г. Казань, ул. Фатыха Амирхана, 71');
            $defaultPickupSchedule = Option::get('askaron.settings', 'DEFAULT_PICKUP_SCHEDULE', 'Пн-Пт с 10:00 до 21:00, Сб-Вс с 10:00 до 18:00');

            $pickupLocation = $this->resolvePickupStoreFromBasket($order);
            if ($pickupLocation) {
                $pickupAddress = trim((string)($pickupLocation['address'] ?? ''));
                if ($pickupAddress === '') {
                    $pickupAddress = trim((string)($pickupLocation['title'] ?? ''));
                }

                $pickupSchedule = trim((string)($pickupLocation['schedule'] ?? ''));
                $pickupStoreName = trim((string)($pickupLocation['title'] ?? ''));
                $pickupStoreId = isset($pickupLocation['storeId']) ? (string)$pickupLocation['storeId'] : '';

                if ($pickupAddress === '') {
                    $pickupAddress = $defaultPickupAddress;
                }

                if ($pickupSchedule === '') {
                    $pickupSchedule = $defaultPickupSchedule;
                }
            } else {
                $pickupAddress = $defaultPickupAddress;
                $pickupSchedule = $defaultPickupSchedule;
            }

            $deliveryAddress = $pickupAddress;
        }

        $comment = isset($courier['comment']) ? trim((string)$courier['comment']) : '';

        $terminalPhones = [];
        if (!empty($terminal['phones'])) {
            $phones = is_array($terminal['phones']) ? $terminal['phones'] : [$terminal['phones']];
            foreach ($phones as $terminalPhone) {
                $terminalPhones[] = is_string($terminalPhone) ? $terminalPhone : '';
            }
            $terminalPhones = array_filter(array_map('trim', $terminalPhones));
        }

        $deliveryTypeNameMap = [
            'terminal' => 'Терминал',
            'courier' => 'Доставка курьером',
            'pickup' => 'Самовывоз',
        ];

        try {
            $formJson = Json::encode($form, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $exception) {
            $formJson = '';
        }

        $locationCode = $this->resolveLocationCodeByAddress($address);

        $propertiesValues = [
            'PHONE' => $phone,
            'TEL' => $phone,
            'EMAIL' => $email,
            'MAIL' => $email,
            'FIO' => $fio,
            'NAME' => isset($recipient['firstName']) ? trim((string)$recipient['firstName']) : '',
            'FIRST_NAME' => isset($recipient['firstName']) ? trim((string)$recipient['firstName']) : '',
            'LAST_NAME' => isset($recipient['lastName']) ? trim((string)$recipient['lastName']) : '',
            'SECOND_NAME' => isset($recipient['middleName']) ? trim((string)$recipient['middleName']) : '',
            'CONTACT_PERSON' => $fio,
            'RECIPIENT' => $fio,
            'DELIVERY_TYPE' => $deliveryTypeNameMap[$form['deliveryType']] ?? $form['deliveryType'],
            'DELIVERY_METHOD' => $deliveryTypeNameMap[$form['deliveryType']] ?? $form['deliveryType'],
            'DELIVERY_ADDRESS' => $deliveryAddress,
            'ADDRESS' => $deliveryAddress,
            'ZIP' => isset($address['postalCode']) ? trim((string)$address['postalCode']) : '',
            'POSTCODE' => isset($address['postalCode']) ? trim((string)$address['postalCode']) : '',
            'KLADR' => isset($address['kladrId']) ? trim((string)$address['kladrId']) : '',
            'FIAS' => isset($address['fiasId']) ? trim((string)$address['fiasId']) : '',
            'CDEK_CITY_CODE' => isset($address['cdekCityCode']) ? (string)$address['cdekCityCode'] : '',
            'LOCATION' => $locationCode,
            'LOCATION_CODE' => $locationCode,
            'TERMINAL_CODE' => isset($terminal['code']) ? trim((string)$terminal['code']) : '',
            'TERMINAL_NAME' => isset($terminal['name']) ? trim((string)$terminal['name']) : '',
            'TERMINAL_ADDRESS' => isset($terminal['address']) ? trim((string)$terminal['address']) : '',
            'TERMINAL_WORK_TIME' => isset($terminal['workTime']) ? trim((string)$terminal['workTime']) : '',
            'TERMINAL_PHONES' => $terminalPhones ? implode(', ', $terminalPhones) : '',
            'PICKUP_ADDRESS' => $pickupAddress,
            'PICKUP_STORE' => $pickupStoreName,
            'PICKUP_STORE_NAME' => $pickupStoreName,
            'PICKUP_STORE_ID' => $pickupStoreId,
            'PICKUP_SCHEDULE' => $pickupSchedule,
            'PICKUP_WORK_TIME' => $pickupSchedule,
            'DELIVERY_DATA_JSON' => $formJson,
            'COMMENT' => $comment,
            'ORDER_COMMENT' => $comment,
        ];

        foreach ($propertyCollection as $propertyValue) {
            $code = strtoupper((string)$propertyValue->getField('CODE'));
            if ($code === '') {
                continue;
            }

            if (array_key_exists($code, $propertiesValues)) {
                $value = $propertiesValues[$code];
                if (is_array($value)) {
                    $value = array_filter(array_map('trim', $value));
                }

                if (is_string($value)) {
                    $value = trim($value);
                }

                if ($value !== '' && $value !== []) {
                    $propertyValue->setValue($value);
                }
            }
        }
    }

    private function resolveLocationCodeByAddress(array $address): ?string
    {
        $cityName = isset($address['city']) ? trim((string)$address['city']) : '';
        $regionName = isset($address['region']) ? trim((string)$address['region']) : '';
        if ($cityName === '') {
            return null;
        }

        $languageId = Context::getCurrent()->getLanguage() ?: 'ru';
        $cacheKey = mb_strtolower($cityName . '|' . $regionName . '|' . $languageId);

        static $cache = [];
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $location = LocationTable::getList([
            'filter' => [
                '=NAME.LANGUAGE_ID' => $languageId,
                '=NAME.NAME' => $cityName,
                '=TYPE.CODE' => ['CITY', 'VILLAGE', 'TOWN', 'CITY_DISTRICT'],
            ],
            'select' => ['CODE'],
            'order' => ['TYPE.SORT' => 'ASC'],
            'limit' => 1,
        ])->fetch();

        if ($location && !empty($location['CODE'])) {
            $cache[$cacheKey] = (string)$location['CODE'];
            return $cache[$cacheKey];
        }

        $location = LocationTable::getList([
            'filter' => [
                '=NAME.LANGUAGE_ID' => $languageId,
                '%=NAME.NAME' => $cityName,
                '=TYPE.CODE' => ['CITY', 'VILLAGE', 'TOWN', 'CITY_DISTRICT'],
            ],
            'select' => ['CODE'],
            'order' => ['TYPE.SORT' => 'ASC'],
            'limit' => 1,
        ])->fetch();

        if ($location && !empty($location['CODE'])) {
            $cache[$cacheKey] = (string)$location['CODE'];
            return $cache[$cacheKey];
        }

        $cache[$cacheKey] = null;

        return null;
    }

    private function collectBasket(): array
    {
        $basket = $this->loadBasket();
        $currency = self::CURRENCY;

        if (!$basket) {
            return [
                'items' => [],
                'totalQty' => 0,
                'totalSum' => 0,
                'totalSumPrint' => Utilities::formatCurrency(0, $currency),
            ];
        }

        $items = [];
        $totalSum = 0;
        $totalQty = 0;

        foreach ($basket as $item) {
            $quantity = (int)$item->getQuantity();
            if ($quantity <= 0) {
                $quantity = 1;
            }

            $price = (float)$item->getPrice();
            $itemCurrency = $item->getCurrency() ?: $currency;
            $sum = $price * $quantity;

            $items[] = [
                'basketId' => $item->getId(),
                'productId' => $item->getProductId(),
                'name' => $item->getField('NAME'),
                'qty' => $quantity,
                'price' => $price,
                'pricePrint' => Utilities::formatCurrency($price, $itemCurrency),
                'sum' => $sum,
                'sumPrint' => Utilities::formatCurrency($sum, $itemCurrency),
                'url' => $item->getField('DETAIL_PAGE_URL') ?: '',
                'image' => Utilities::getProductImage((int)$item->getProductId()),
            ];

            $totalSum += $sum;
            $totalQty += $quantity;
        }

        return [
            'items' => $items,
            'totalQty' => $totalQty,
            'totalSum' => $totalSum,
            'totalSumPrint' => Utilities::formatCurrency($totalSum, $currency),
        ];
    }

    private function loadBasket(): ?Sale\Basket
    {
        if (!Loader::includeModule('sale')) {
            return null;
        }

        $siteId = Context::getCurrent()->getSite();
        return Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), $siteId);
    }

    private function getUserData(): array
    {
        global $USER;
        if (!$USER || !$USER->IsAuthorized()) {
            return [
                'isAuthorized' => false,
                'phone' => '',
                'email' => '',
                'firstName' => '',
                'lastName' => '',
                'middleName' => '',
            ];
        }

        $userId = (int)$USER->GetID();
        $userFields = $USER->GetByID($userId)->Fetch();
        $phone = Bitrix\Main\UserPhoneAuthTable::getList(['filter' => ['USER_ID' => $userId]])->fetch();

        return [
            'isAuthorized' => true,
            'phone' => $phone['PHONE_NUMBER'] ?? '',
            'email' => $userFields['EMAIL'] ?? '',
            'firstName' => $userFields['NAME'] ?? '',
            'lastName' => $userFields['LAST_NAME'] ?? '',
            'middleName' => $userFields['SECOND_NAME'] ?? '',
        ];
    }

    private function getDeliveryMethods(): array
    {
        return [
            [
                'code' => 'terminal',
                'title' => 'Терминал',
                'description' => 'В удобный для вас терминал СДЭК',
                'price' => 'от 300 ₽',
            ],
            [
                'code' => 'courier',
                'title' => 'Доставка курьером',
                'description' => 'Доставим товар до вашей двери',
                'price' => 'от 500 ₽',
            ],
            [
                'code' => 'pickup',
                'title' => 'Самовывоз',
                'description' => 'Забрать товары из нашего офиса',
                'price' => 'Бесплатно',
            ],
        ];
    }

    private function getPaymentMethods(): array
    {
        return [
            [
                'code' => 'card-online',
                'title' => 'Картой онлайн',
                'description' => 'Банковские карты Visa, MasterCard, Мир',
            ],
        ];
    }

    private function buildPickupHints(array $items): array
    {
        $defaultAddress = Option::get('askaron.settings', 'DEFAULT_PICKUP_ADDRESS', 'г. Казань, ул. Фатыха Амирхана, 71');
        $defaultSchedule = Option::get('askaron.settings', 'DEFAULT_PICKUP_SCHEDULE', 'Пн-Пт с 10:00 до 21:00, Сб-Вс с 10:00 до 18:00');

        $result = [];
        foreach ($items as $item) {
            $storeLocation = $this->getProductStoreLocation((int)$item['productId']);

            $address = $defaultAddress;
            $schedule = $defaultSchedule;

            if (is_array($storeLocation)) {
                $storeAddress = trim((string)($storeLocation['address'] ?? ''));
                if ($storeAddress !== '') {
                    $address = $storeAddress;
                }

                $storeSchedule = trim((string)($storeLocation['description'] ?? ''));
                if ($storeSchedule !== '') {
                    $schedule = $storeSchedule;
                }
            }

            $result[] = [
                'productId' => $item['productId'],
                'name' => $item['name'],
                'address' => $address,
                'schedule' => $schedule,
            ];
        }

        return $result;
    }

    private function getClientSettings(): array
    {
        return [
            'yandexApiKey' => Option::get('askaron.settings', 'UF_YANDEX_MAP_KEY', ''),
        ];
    }

    private function resolveCityCode(CdekApiClient $client, array $address): ?int
    {
        $cityCode = isset($address['cdekCityCode']) ? (int)$address['cdekCityCode'] : null;
        if ($cityCode) {
            return $cityCode;
        }

        $fiasId = (string)($address['fiasId'] ?? '');
        if ($fiasId !== '') {
            try {
                $cityInfo = $client->findCityByFias($fiasId);
                if (!empty($cityInfo['code'])) {
                    return (int)$cityInfo['code'];
                }
            } catch (SystemException $exception) {
                // ignore and try other methods
            }
        }

        $postalCode = (string)($address['postalCode'] ?? '');
        if ($postalCode !== '') {
            try {
                $cityInfo = $client->findCityByPostalCode($postalCode);
                if (!empty($cityInfo['code'])) {
                    return (int)$cityInfo['code'];
                }
            } catch (SystemException $exception) {
                // ignore
            }
        }

        return null;
    }

    private function buildDeliveryPackages(): array
    {
        // Проверяем валидность корзины перед сборкой посылок
        $validation = $this->validateBasketCities();

        if (!$validation['valid']) {
            return [];
        }

        $basket = $this->loadBasket();
        if (!$basket || $basket->isEmpty()) {
            return [];
        }

        $totalWeight = 0.0;
        $maxLength = 0;
        $maxWidth = 0;
        $maxHeight = 0;

        foreach ($basket as $item) {
            $quantity = (int)$item->getQuantity();
            if ($quantity <= 0) {
                $quantity = 1;
            }

            $weight = (float)$item->getWeight();
            if ($weight <= 0) {
                $weight = (float)$item->getField('WEIGHT');
            }
            if ($weight <= 0) {
                $weight = self::DEFAULT_PACKAGE_WEIGHT;
            }

            $dimensions = $this->decodeDimensions($item->getField('DIMENSIONS'));
            $length = $this->normalizeDimension($dimensions['LENGTH'] ?? 0);
            $width = $this->normalizeDimension($dimensions['WIDTH'] ?? 0);
            $height = $this->normalizeDimension($dimensions['HEIGHT'] ?? 0);

            if ($length <= 0) {
                $length = self::DEFAULT_PACKAGE_DIMENSION;
            }
            if ($width <= 0) {
                $width = self::DEFAULT_PACKAGE_DIMENSION;
            }
            if ($height <= 0) {
                $height = self::DEFAULT_PACKAGE_DIMENSION;
            }

            $totalWeight += $weight * $quantity;
            $maxLength = max($maxLength, $length);
            $maxWidth = max($maxWidth, $width);
            $maxHeight = max($maxHeight, $height);
        }

        return [[
            'weight' => (int)ceil($totalWeight ?: self::DEFAULT_PACKAGE_WEIGHT),
            'length' => (int)ceil($maxLength ?: self::DEFAULT_PACKAGE_DIMENSION),
            'width' => (int)ceil($maxWidth ?: self::DEFAULT_PACKAGE_DIMENSION),
            'height' => (int)ceil($maxHeight ?: self::DEFAULT_PACKAGE_DIMENSION),
            'from_city_code' => $validation['cityCode'],
        ]];
    }

    private function decodeDimensions($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = @unserialize($raw, ['allowed_classes' => false]);
            if (is_array($decoded)) {
                return $decoded;
            }

            try {
                $decoded = Json::decode($raw);
                if (is_array($decoded)) {
                    return $decoded;
                }
            } catch (SystemException $exception) {
                // ignore invalid JSON
            }
        }

        return [];
    }

    private function normalizeDimension($value): int
    {
        $dimension = (float)$value;
        if ($dimension <= 0) {
            return 0;
        }

        if ($dimension > 100) {
            $dimension = $dimension / 10;
        }

        return (int)ceil($dimension);
    }

    private function getSenderCityCode(): ?int
    {
        $code = (int)Option::get('askaron.settings', 'UF_CDEK_SENDER_CITY_CODE', 0);
        if ($code <= 0) {
            $code = (int)Option::get('askaron.settings', 'CDEK_SENDER_CITY_CODE', 0);
        }

        return $code > 0 ? $code : null;
    }

    private function getTerminalTariffCode(): int
    {
        $code = (int)Option::get('askaron.settings', 'UF_CDEK_TERMINAL_TARIFF', 0);
        if ($code <= 0) {
            $code = (int)Option::get('askaron.settings', 'CDEK_TERMINAL_TARIFF', 0);
        }

        if ($code <= 0) {
            $code = self::DEFAULT_TERMINAL_TARIFF_CODE;
        }

        return $code;
    }

    private function getCourierTariffs(): array
    {
        $tariffs = [];

        $singleTariff = (int)Option::get('askaron.settings', 'UF_CDEK_COURIER_TARIFF', 0);
        if ($singleTariff > 0) {
            $tariffs[$singleTariff] = '';
        }

        $rawList = trim((string)Option::get('askaron.settings', 'UF_CDEK_COURIER_TARIFFS', ''));
        if ($rawList !== '') {
            $decoded = null;
            try {
                $decoded = Json::decode($rawList);
            } catch (SystemException $exception) {
                $decoded = null;
            }

            if (is_array($decoded)) {
                foreach ($decoded as $key => $value) {
                    if (is_array($value)) {
                        $code = isset($value['code']) ? (int)$value['code'] : (int)$key;
                        $name = isset($value['name']) ? (string)$value['name'] : '';
                    } else {
                        $code = is_numeric($key) ? (int)$value : (int)$key;
                        $name = is_numeric($key) ? '' : (string)$value;
                    }

                    if ($code > 0) {
                        $tariffs[$code] = $name;
                    }
                }
            }

            if (empty($tariffs)) {
                $parts = preg_split('/[;,]+/', $rawList);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if ($part === '') {
                        continue;
                    }

                    $separators = [':', '|', '='];
                    $code = null;
                    $name = '';

                    foreach ($separators as $separator) {
                        if (strpos($part, $separator) !== false) {
                            [$codePart, $namePart] = array_map('trim', explode($separator, $part, 2));
                            $code = (int)$codePart;
                            $name = $namePart;
                            break;
                        }
                    }

                    if ($code === null) {
                        $code = (int)$part;
                    }

                    if ($code > 0) {
                        $tariffs[$code] = $name;
                    }
                }
            }
        }

        if (empty($tariffs)) {
            return self::DEFAULT_COURIER_TARIFFS;
        }

        foreach ($tariffs as $code => $name) {
            if (!is_string($name) || $name === '') {
                $tariffs[$code] = 'Тариф ' . $code;
            }
        }

        return $tariffs;
    }

    private function getCdekClient(): ?CdekApiClient
    {
        if (!class_exists(CdekApiClient::class)) {
            return null;
        }

        static $client = null;
        if ($client === null) {
            $client = new CdekApiClient();
        }

        return $client;
    }

    private function formatPhones(array $phones): array
    {
        $result = [];
        foreach ($phones as $phone) {
            if (!empty($phone['number'])) {
                $formatted = preg_replace('/[^0-9+]/', '', $phone['number']);
                if (!empty($phone['comment'])) {
                    $result[] = $formatted . ' (' . $phone['comment'] . ')';
                } else {
                    $result[] = $formatted;
                }
            }
        }

        return $result;
    }

    /**
     * Получает код СДЭК по названию города из HL блока
     *
     * @param string $cityName Название города
     * @return int|null Код города СДЭК или null
     */
    private function getCityCodeFromHL(string $cityName): ?int
    {
        $cityName = trim($cityName);

        if (empty($cityName)) {
            return null;
        }

        try {
            $hlBlock = \Devob\Helpers\HLBlock::getInstance();

            $city = $hlBlock->getElement(3, [
                'UF_NAME' => $cityName
            ], ['UF_CODE']);


            if ($city && !empty($city['UF_CODE'])) {
                $code = (int)$city['UF_CODE'];
                return $code;
            }

        } catch (\Exception $e) {
        }

        return null;
    }



    /**
     * Проверяет, что все товары в корзине из одного города
     *
     * @return array Массив с результатами валидации
     */
    private function validateBasketCities(): array
    {
        $basket = $this->loadBasket();
        if (!$basket || $basket->isEmpty()) {
            return ['valid' => true, 'cityCode' => null, 'conflicts' => []];
        }

        $citiesInBasket = [];
        $productsByCities = [];
        $cityNames = []; // Для отображения названий городов

        foreach ($basket as $item) {
            $productId = $item->getProductId();

            // === ПОЛУЧАЕМ ГОРОД ИЗ СКЛАДА ТОВАРА ===
            $storeLocation = $this->getProductStoreLocation($productId);
            $cityCode = $storeLocation['storeId'] ?? null;  // Код города из HL блока
            $cityName = $storeLocation['city'] ?? null;      // Название города

            // Если у товара не указан город, используем город по умолчанию из настроек
            if (!$cityCode) {
                $cityCode = $this->getSenderCityCode();
                $cityName = $this->getCityNameByCode($cityCode);
            }

            // Если и в настройках нет города - ошибка конфигурации
            if (!$cityCode) {
                return [
                    'valid' => false,
                    'cityCode' => null,
                    'conflicts' => [],
                    'error' => 'У товара "' . $item->getField('NAME') . '" не указан город отправки',
                ];
            }

            if (!isset($productsByCities[$cityCode])) {
                $productsByCities[$cityCode] = [
                    'city_name' => $cityName,
                    'products' => [],
                ];
            }

            $productsByCities[$cityCode]['products'][] = [
                'id' => $productId,
                'name' => $item->getField('NAME'),
            ];

            $citiesInBasket[$cityCode] = $cityCode;
        }

        // Проверяем, есть ли товары из разных городов
        if (count($citiesInBasket) > 1) {
            return [
                'valid' => false,
                'cityCode' => null,
                'conflicts' => $productsByCities,
                'citiesCount' => count($citiesInBasket),
            ];
        }

        // Все товары из одного города
        $singleCityCode = array_key_first($citiesInBasket);

        return [
            'valid' => true,
            'cityCode' => $singleCityCode,
            'cityName' => $productsByCities[$singleCityCode]['city_name'] ?? '',
            'conflicts' => [],
        ];
    }

    /**
     * Получает название города по коду СДЭК из HL блока
     *
     * @param int $cityCode Код города СДЭК
     * @return string Название города или пустая строка
     */
    private function getCityNameByCode(int $cityCode): string
    {
        try {
            $hlBlock = \Devob\Helpers\HLBlock::getInstance();

            $city = $hlBlock->getElement(3, [
                'UF_CODE' => $cityCode
            ], ['UF_NAME']);

            return $city['UF_NAME'] ?? '';

        } catch (\Exception $e) {
            \AddMessage2Log('Ошибка получения названия города: ' . $e->getMessage(), 'cdek_cities');
        }

        return '';
    }

    /**
     * Action для проверки корзины на товары из разных городов
     *
     * @return array Результат проверки
     */
    public function getBasketCitiesInfoAction(): array
    {
        $validation = $this->validateBasketCities();

        if (!$validation['valid']) {
            $errorMessage = $validation['error'] ?? 'В корзине товары из разных городов отправки';

            return [
                'success' => false,
                'valid' => false,
                'error' => $errorMessage,
                'conflicts' => $validation['conflicts'] ?? [],
            ];
        }

        return [
            'success' => true,
            'valid' => true,
            'cityCode' => $validation['cityCode'],
            'cityName' => $validation['cityName'] ?? '',
        ];
    }

    private function resolvePickupStoreFromBasket(Sale\Order $order): ?array
    {
        $basket = $order->getBasket();
        if (!$basket || $basket->isEmpty()) {
            return null;
        }

        $resolvedStoreId = null;
        $resolvedLocation = null;

        foreach ($basket as $basketItem) {
            $productId = (int)$basketItem->getProductId();
            if ($productId <= 0) {
                return null;
            }

            $storeLocation = $this->getProductStoreLocation($productId);
            if (empty($storeLocation) || empty($storeLocation['storeId'])) {
                return null;
            }

            $storeId = (int)$storeLocation['storeId'];
            if ($resolvedStoreId === null) {
                $resolvedStoreId = $storeId;
                $resolvedLocation = $storeLocation;
                continue;
            }

            if ($resolvedStoreId !== $storeId) {
                return null;
            }

            if ($resolvedLocation === null) {
                $resolvedLocation = $storeLocation;
                continue;
            }

            foreach (['address', 'title', 'description'] as $field) {
                if (($resolvedLocation[$field] ?? '') === '' && ($storeLocation[$field] ?? '') !== '') {
                    $resolvedLocation[$field] = $storeLocation[$field];
                }
            }
        }

        if ($resolvedStoreId === null) {
            return null;
        }

        return [
            'storeId' => $resolvedStoreId,
            'address' => isset($resolvedLocation['address']) ? trim((string)$resolvedLocation['address']) : '',
            'title' => isset($resolvedLocation['title']) ? trim((string)$resolvedLocation['title']) : '',
            'schedule' => isset($resolvedLocation['description']) ? trim((string)$resolvedLocation['description']) : '',
        ];
    }

    private function getProductStoreLocation(int $productId): array
    {
        // Используй свой StoreLocationHelper
        $helper = new StoreLocationHelper();
        return $helper->getLocationForProduct($productId);
    }

    /**
     * Получает данные для формы оплаты
     *
     * @param Sale\Payment $payment Объект платежа
     * @return array Массив с URL оплаты
     */
    private function getPaymentFormData(\Bitrix\Sale\Payment $payment): array
    {
        try {
            $paymentId = $payment->getId();
            if (!$paymentId) {
                $paymentId = 0;
            }

            // Используем payment_init.php для инициализации
            $request = \Bitrix\Main\Context::getCurrent()->getRequest();
            $protocol = $request->isHttps() ? 'https' : 'http';
            $host = $request->getHttpHost();
            $paymentUrl = sprintf('%s://%s/local/ajax/payment_init.php?paymentid=%d', $protocol, $host, $paymentId);

            return [
                'paymentId' => $paymentId,
                'url' => $paymentUrl,
                'needRedirect' => true,
            ];

        } catch (\Exception $exception) {
            return [
                'paymentId' => null,
                'url' => null,
                'needRedirect' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }
}
