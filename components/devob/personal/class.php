<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Order as SaleOrder;
use Bitrix\Sale\OrderStatus;

Loc::loadMessages(__FILE__);

class DevobPersonalComponent extends CBitrixComponent
{
    private const DEFAULT_SEF_FOLDER = '/personal/';
    private const NO_PHOTO_PATH = '/upload/no-photo-white.png';

    private array $defaultUrlTemplates = [
        'index' => '',
        'edit' => 'edit/',
        'orders' => 'orders/',
        'favorites' => 'favorites/',
    ];

    public function onPrepareComponentParams($params)
    {
        $params = is_array($params) ? $params : [];

        $params['SEF_MODE'] = ($params['SEF_MODE'] ?? 'Y') === 'N' ? 'N' : 'Y';
        $params['SEF_FOLDER'] = trim((string)($params['SEF_FOLDER'] ?? '')) ?: self::DEFAULT_SEF_FOLDER;
        $params['SEF_URL_TEMPLATES'] = is_array($params['SEF_URL_TEMPLATES'] ?? null)
            ? $params['SEF_URL_TEMPLATES']
            : [];

        $params['SET_TITLE'] = ($params['SET_TITLE'] ?? 'Y') === 'N' ? 'N' : 'Y';
        $params['AUTH_URL'] = trim((string)($params['AUTH_URL'] ?? '/auth/'));

        $archiveStatuses = $params['ARCHIVE_STATUSES'] ?? ['F'];
        if (!is_array($archiveStatuses)) {
            $archiveStatuses = [$archiveStatuses];
        }
        $params['ARCHIVE_STATUSES'] = array_values(
            array_unique(
                array_filter(
                    array_map('strval', $archiveStatuses)
                )
            )
        );

        $params['FAVORITES_HL_BLOCK_ID'] = isset($params['FAVORITES_HL_BLOCK_ID'])
            ? (int)$params['FAVORITES_HL_BLOCK_ID']
            : 0;
        $params['FAVORITES_COMPONENT_PARAMS'] = is_array($params['FAVORITES_COMPONENT_PARAMS'] ?? null)
            ? $params['FAVORITES_COMPONENT_PARAMS']
            : [];

        return $params;
    }

    public function executeComponent()
    {
        $userId = CurrentUser::get()->getId();
        if (!$userId) {
            $this->arResult['COMPONENT_PAGE'] = 'auth';
            $this->arResult['AUTH_URL'] = $this->arParams['AUTH_URL'];
            $this->includeComponentTemplate();
            return;
        }

        $pageData = $this->resolveComponentPage();
        $componentPage = $pageData['PAGE'];

        $userData = $this->loadUserData($userId);
        if (!$userData) {
            throw new SystemException(Loc::getMessage('DEVOB_PERSONAL_USER_NOT_FOUND'));
        }

        $this->arResult['USER'] = $userData;
        $this->arResult['COMPONENT_PAGE'] = $componentPage;
        $this->arResult['VARIABLES'] = $pageData['VARIABLES'];
        $this->arResult['FOLDER'] = $this->arParams['SEF_FOLDER'];
        $this->arResult['URL_TEMPLATES'] = $pageData['TEMPLATES'];
        $this->arResult['NAVIGATION'] = $this->buildNavigation($componentPage);
        $this->arResult['PAGE_TITLE'] = $this->resolvePageTitle($componentPage);

        if ($this->arParams['SET_TITLE'] === 'Y' && $this->arResult['PAGE_TITLE']) {
            global $APPLICATION;
            $APPLICATION->SetTitle($this->arResult['PAGE_TITLE']);
        }

        switch ($componentPage) {
            case 'edit':
                $this->prepareProfilePage();
                $this->prepareEditPage($userData);
                break;
            case 'orders':
                $this->prepareProfilePage();
                $this->prepareOrdersPage($userId);
                break;
            case 'favorites':
                $this->prepareProfilePage();
                $this->prepareFavoritesPage();
                break;
            default:
                $this->prepareProfilePage();
                break;
        }

        $this->includeComponentTemplate();
    }

    private function resolveComponentPage(): array
    {
        $templates = CComponentEngine::makeComponentUrlTemplates(
            $this->defaultUrlTemplates,
            $this->arParams['SEF_URL_TEMPLATES']
        );

        $variables = [];
        $componentPage = 'index';

        if ($this->arParams['SEF_MODE'] === 'Y') {
            $componentPage = CComponentEngine::parseComponentPath(
                $this->arParams['SEF_FOLDER'],
                $templates,
                $variables
            );
            if (!$componentPage) {
                $componentPage = 'index';
            }
        } else {
            $requestPage = (string)Context::getCurrent()->getRequest()->get('page');
            if (isset($templates[$requestPage])) {
                $componentPage = $requestPage;
            }
        }

        CComponentEngine::initComponentVariables(
            $componentPage,
            [],
            [],
            $variables
        );

        return [
            'PAGE' => $componentPage,
            'VARIABLES' => $variables,
            'TEMPLATES' => $templates,
        ];
    }

    private function buildNavigation(string $componentPage): array
    {
        $navItems = [
            [
                'code' => 'index',
                'title' => Loc::getMessage('DEVOB_PERSONAL_NAV_MAIN'),
            ],
            [
                'code' => 'orders',
                'title' => Loc::getMessage('DEVOB_PERSONAL_NAV_ORDERS'),
            ],
            [
                'code' => 'favorites',
                'title' => Loc::getMessage('DEVOB_PERSONAL_NAV_FAVORITES'),
            ],
        ];

        foreach ($navItems as &$item) {
            $item['url'] = $this->getPageUrl($item['code']);
            $item['is_active'] = $item['code'] === $componentPage;
        }
        unset($item);

        return $navItems;
    }

    private function resolvePageTitle(string $componentPage): string
    {
        $map = [
            'index' => 'DEVOB_PERSONAL_PAGE_TITLE_MAIN',
            'edit' => 'DEVOB_PERSONAL_PAGE_TITLE_EDIT',
            'orders' => 'DEVOB_PERSONAL_PAGE_TITLE_ORDERS',
            'favorites' => 'DEVOB_PERSONAL_PAGE_TITLE_FAVORITES',
        ];

        $messageCode = $map[$componentPage] ?? null;
        if (!$messageCode) {
            return '';
        }

        return (string)Loc::getMessage($messageCode);
    }

    private function getPageUrl(string $page): string
    {
        $templates = $this->arResult['URL_TEMPLATES'] ?? CComponentEngine::makeComponentUrlTemplates(
            $this->defaultUrlTemplates,
            $this->arParams['SEF_URL_TEMPLATES']
        );

        $template = $templates[$page] ?? $this->defaultUrlTemplates['index'];

        if ($this->arParams['SEF_MODE'] === 'Y') {
            return rtrim($this->arParams['SEF_FOLDER'], '/') . '/' . ltrim($template, '/');
        }

        $uri = new Uri(Context::getCurrent()->getRequest()->getRequestUri());
        $uri->deleteParams(['page']);
        if ($page !== 'index') {
            $uri->addParams(['page' => $page]);
        }

        return $uri->getUri();
    }

    private function loadUserData(int $userId): ?array
    {
        $userResult = CUser::GetByID($userId);
        $user = $userResult ? $userResult->Fetch() : null;
        if (!$user) {
            return null;
        }

        $fullName = trim(CUser::FormatName(CSite::GetNameFormat(), $user, true, false));
        if ($fullName === '') {
            $fullName = trim($user['LOGIN']);
        }
        $phone = Bitrix\Main\UserPhoneAuthTable::getList(['filter' =>[ 'USER_ID' => $user['ID']]])->fetch();
        $initials = $this->buildInitials($user);

        $photoSrc = '';
        if (!empty($user['PERSONAL_PHOTO'])) {
            $file = CFile::ResizeImageGet(
                (int)$user['PERSONAL_PHOTO'],
                ['width' => 120, 'height' => 120],
                BX_RESIZE_IMAGE_EXACT,
                false
            );
            if (!empty($file['src'])) {
                $photoSrc = $file['src'];
            }
        }

        return [
            'ID' => (int)$user['ID'],
            'NAME' => (string)$user['NAME'],
            'LAST_NAME' => (string)$user['LAST_NAME'],
            'SECOND_NAME' => (string)$user['SECOND_NAME'],
            'EMAIL' => (string)$user['EMAIL'],
            'PHONE_NUMBER' => (string)$phone['PHONE_NUMBER'],
            'LOGIN' => (string)$user['LOGIN'],
            'FULL_NAME' => $fullName,
            'DISPLAY_NAME' => $fullName ?: $user['LOGIN'],
            'INITIALS' => $initials,
            'PHOTO_SRC' => $photoSrc,
        ];
    }

    private function buildInitials(array $user): string
    {
        $parts = [];
        foreach (['NAME', 'LAST_NAME'] as $field) {
            $value = trim((string)($user[$field] ?? ''));
            if ($value !== '') {
                $parts[] = mb_strtoupper(mb_substr($value, 0, 1));
            }
        }

        if (empty($parts) && !empty($user['LOGIN'])) {
            $parts[] = mb_strtoupper(mb_substr((string)$user['LOGIN'], 0, 1));
        }

        return implode('', $parts);
    }

    private function prepareProfilePage(): void
    {
        $request = Context::getCurrent()->getRequest();
        $baseUri = new Uri($request->getRequestUri());
        $baseUri->deleteParams(['logout', 'sessid']);
        $logoutUri = clone $baseUri;
        $logoutUri->addParams([
            'logout' => 'yes',
            'sessid' => bitrix_sessid(),
        ]);

        $this->arResult['PROFILE'] = [
            'EDIT_URL' => $this->getPageUrl('edit'),
            'ORDERS_URL' => $this->getPageUrl('orders'),
            'FAVORITES_URL' => $this->getPageUrl('favorites'),
            'LOGOUT_URL' => $logoutUri->getUri(),
        ];
    }

    private function prepareEditPage(array $userData): void
    {
        $request = Context::getCurrent()->getRequest();
        $values = [
            'NAME' => $userData['NAME'],
            'LAST_NAME' => $userData['LAST_NAME'],
            'SECOND_NAME' => $userData['SECOND_NAME'],
            'EMAIL' => $userData['EMAIL'],
            'PHONE_NUMBER' => $userData['PHONE_NUMBER'],
        ];
        $errors = [];
        $success = false;

        if ($request->isPost() && check_bitrix_sessid() && $request->getPost('save') === 'Y') {
            $updatedValues = [
                'NAME' => trim((string)$request->getPost('NAME')),
                'LAST_NAME' => trim((string)$request->getPost('LAST_NAME')),
                'SECOND_NAME' => trim((string)$request->getPost('SECOND_NAME')),
                'EMAIL' => trim((string)$request->getPost('EMAIL')),
            ];

            $values = array_merge($values, $updatedValues);

            $user = new CUser();
            $fields = $updatedValues;
            if ($fields['EMAIL'] === '') {
                unset($fields['EMAIL']);
            }

            if (!$user->Update($userData['ID'], $fields)) {
                $errors[] = $user->LAST_ERROR ?: Loc::getMessage('DEVOB_PERSONAL_SAVE_ERROR');
            } else {
                $success = true;
                $this->arResult['USER'] = $this->loadUserData($userData['ID']);
            }
        }

        $this->arResult['EDIT'] = [
            'VALUES' => $values,
            'ERRORS' => $errors,
            'SUCCESS' => $success,
            'BACK_URL' => $this->getPageUrl('index'),
        ];
    }

    private function prepareOrdersPage(int $userId): void
    {
        $orders = [
            'ACTIVE' => [],
            'ARCHIVE' => [],
        ];

        if (!Loader::includeModule('sale')) {
            $this->arResult['ORDERS'] = $orders;
            $this->arResult['ORDER_STATUSES'] = [];
            return;
        }

        $statuses = OrderStatus::getAllStatusesNames(LANGUAGE_ID);

        $productIds = [];
        $orderRes = CSaleOrder::GetList(
            ['DATE_INSERT' => 'DESC'],
            ['USER_ID' => $userId],
            false,
            false,
            [
                'ID',
                'ACCOUNT_NUMBER',
                'DATE_INSERT',
                'PRICE',
                'CURRENCY',
                'STATUS_ID',
                'CANCELED',
                'PAYED',
                'USER_DESCRIPTION',
            ]
        );

        while ($order = $orderRes->Fetch()) {
            $order['DATE_INSERT'] = $order['DATE_INSERT'] instanceof \Bitrix\Main\Type\DateTime
                ? $order['DATE_INSERT']
                : new \Bitrix\Main\Type\DateTime($order['DATE_INSERT']);

            $orderObject = SaleOrder::load((int)$order['ID']);
            if (!$orderObject) {
                continue;
            }

            $currency = $orderObject->getCurrency();
            $basketItems = [];

            foreach ($orderObject->getBasket()->getBasketItems() as $basketItem) {
                $productId = (int)$basketItem->getProductId();
                if ($productId > 0) {
                    $productIds[$productId] = true;
                }

                $quantity = (float)$basketItem->getQuantity();
                $basketItems[] = [
                    'PRODUCT_ID' => $productId,
                    'NAME' => (string)$basketItem->getField('NAME'),
                    'DETAIL_PAGE_URL' => (string)$basketItem->getField('DETAIL_PAGE_URL'),
                    'QUANTITY' => $quantity,
                    'QUANTITY_FORMATTED' => $this->formatQuantity($quantity),
                    'MEASURE' => (string)$basketItem->getField('MEASURE_NAME'),
                    'PRICE_VALUE' => (float)$basketItem->getPrice(),
                    'PRICE_FORMATTED' => SaleFormatCurrency($basketItem->getPrice(), $currency),
                    'SUM_VALUE' => (float)$basketItem->getFinalPrice(),
                    'SUM_FORMATTED' => SaleFormatCurrency($basketItem->getFinalPrice(), $currency),
                ];
            }

            $payments = [];
            $paidAmount = 0.0;
            foreach ($orderObject->getPaymentCollection() as $payment) {
                $paymentSum = (float)$payment->getSum();
                $paymentCurrency = (string)($payment->getField('CURRENCY') ?: $currency);
                $isPaid = $payment->isPaid();

                if ($isPaid) {
                    $paidAmount += $paymentSum;
                }

                $payments[] = [
                    'ID' => $payment->getId(),
                    'NAME' => (string)$payment->getPaymentSystemName(),
                    'SUM_VALUE' => $paymentSum,
                    'SUM_FORMATTED' => SaleFormatCurrency($paymentSum, $paymentCurrency),
                    'CURRENCY' => $paymentCurrency,
                    'IS_PAID' => $isPaid,
                ];
            }

            $shipments = [];
            foreach ($orderObject->getShipmentCollection() as $shipment) {
                if ($shipment->isSystem()) {
                    continue;
                }

                $shipmentPrice = (float)$shipment->getPrice();
                $shipments[] = [
                    'ID' => $shipment->getId(),
                    'NAME' => (string)$shipment->getDeliveryName(),
                    'PRICE_VALUE' => $shipmentPrice,
                    'PRICE_FORMATTED' => SaleFormatCurrency($shipmentPrice, $currency),
                    'IS_DEDUCTED' => $shipment->getField('DEDUCTED') === 'Y',
                    'TRACKING_NUMBER' => (string)$shipment->getField('TRACKING_NUMBER'),
                ];
            }

            $properties = [];
            $propertyCollection = $orderObject->getPropertyCollection();
            if ($propertyCollection) {
                foreach ($propertyCollection as $property) {
                    $propertyData = $property->getProperty();
                    if (($propertyData['IS_UTIL'] ?? 'N') === 'Y') {
                        continue;
                    }

                    $value = $property->getValue();
                    if (is_array($value)) {
                        $value = array_filter(array_map('trim', $value));
                        $value = implode(', ', $value);
                    }

                    $value = trim((string)$value);
                    if ($value === '') {
                        continue;
                    }

                    $properties[] = [
                        'CODE' => (string)($propertyData['CODE'] ?? ''),
                        'NAME' => (string)($property->getField('NAME') ?? $propertyData['NAME'] ?? ''),
                        'VALUE' => $value,
                    ];
                }
            }

            $statusId = (string)$order['STATUS_ID'];
            $isCanceled = $order['CANCELED'] === 'Y';
            $isPaid = $orderObject->isPaid();
            $paymentStatusCode = 'WAIT';
            if ($isPaid || $paidAmount >= (float)$orderObject->getPrice()) {
                $paymentStatusCode = 'PAID';
            } elseif ($paidAmount > 0.0) {
                $paymentStatusCode = 'PARTIAL';
            }

            $paymentStatusClasses = [
                'PAID' => 'success',
                'PARTIAL' => 'warning',
                'WAIT' => 'process',
            ];
            $paymentStatusText = (string)Loc::getMessage('DEVOB_PERSONAL_ORDER_PAYMENT_STATUS_' . $paymentStatusCode);
            if ($paymentStatusText === '') {
                $paymentStatusText = $paymentStatusCode;
            }

            $orderData = [
                'ID' => (int)$order['ID'],
                'ACCOUNT_NUMBER' => (string)$order['ACCOUNT_NUMBER'],
                'DATE_INSERT' => $order['DATE_INSERT']->toString(),
                'DATE_FORMATTED' => FormatDate('d F Y, H:i', $order['DATE_INSERT']->getTimestamp()),
                'PRICE_VALUE' => (float)$orderObject->getPrice(),
                'PRICE_FORMATTED' => SaleFormatCurrency($orderObject->getPrice(), $currency),
                'ITEMS_PRICE_FORMATTED' => SaleFormatCurrency($orderObject->getBasket()->getPrice(), $currency),
                'DELIVERY_PRICE_FORMATTED' => SaleFormatCurrency($orderObject->getDeliveryPrice(), $currency),
                'DISCOUNT_PRICE_FORMATTED' => SaleFormatCurrency($orderObject->getDiscountPrice(), $currency),
                'STATUS_ID' => $statusId,
                'STATUS_NAME' => $statuses[$statusId] ?? $statusId,
                'STATUS_CLASS' => $isCanceled ? 'canceled' : ($isPaid ? 'success' : 'process'),
                'CANCELED' => $isCanceled,
                'PAYED' => $order['PAYED'] === 'Y',
                'PAYMENT_STATUS' => $paymentStatusCode,
                'PAYMENT_STATUS_TEXT' => $paymentStatusText,
                'PAYMENT_STATUS_CLASS' => $paymentStatusClasses[$paymentStatusCode] ?? 'process',
                'PAYMENTS' => $payments,
                'PAYMENT_SUMMARY' => implode(', ', array_filter(array_map(static fn($payment) => trim($payment['NAME']), $payments))),
                'SHIPMENTS' => $shipments,
                'DELIVERY_SUMMARY' => implode(', ', array_filter(array_map(static fn($shipment) => trim($shipment['NAME']), $shipments))),
                'ITEMS' => $basketItems,
                'PROPERTIES' => $properties,
                'COMMENT' => trim((string)$orderObject->getField('USER_DESCRIPTION')),
            ];

            $isArchive = $isCanceled || in_array($statusId, $this->arParams['ARCHIVE_STATUSES'], true);

            $orders[$isArchive ? 'ARCHIVE' : 'ACTIVE'][] = $orderData;
        }

        $productsInfo = $this->loadProductsInfo(array_keys($productIds));

        foreach ($orders as &$orderGroup) {
            foreach ($orderGroup as &$order) {
                foreach ($order['ITEMS'] as &$item) {
                    $productId = $item['PRODUCT_ID'];
                    $productInfo = $productsInfo[$productId] ?? null;

                    $item['IMAGE'] = $productInfo['IMAGE'] ?? '';
                    $item['URL'] = $productInfo['URL'] ?: $item['DETAIL_PAGE_URL'];
                    if ($productInfo && !empty($productInfo['NAME'])) {
                        $item['NAME'] = $item['NAME'] ?: $productInfo['NAME'];
                    }

                    $item['MEASURE'] = $item['MEASURE'] ?: Loc::getMessage('DEVOB_PERSONAL_ORDER_ITEM_MEASURE_DEFAULT');
                    unset($item['DETAIL_PAGE_URL']);
                }
                unset($item);
            }
            unset($order);
        }
        unset($orderGroup);

        $this->arResult['ORDERS'] = $orders;
        $this->arResult['ORDER_STATUSES'] = $statuses;
    }

    private function prepareFavoritesPage(): void
    {
        $params = array_merge(
            ['HL_BLOCK_ID' => $this->arParams['FAVORITES_HL_BLOCK_ID']],
            $this->arParams['FAVORITES_COMPONENT_PARAMS']
        );

        $this->arResult['FAVORITES_COMPONENT'] = [
            'NAME' => 'devob:favorites',
            'TEMPLATE' => 'page',
            'PARAMS' => $params,
        ];
    }

    private function loadProductsInfo(array $productIds): array
    {
        $productIds = array_values(array_filter(array_map('intval', $productIds)));
        if (empty($productIds)) {
            return [];
        }

        if (!Loader::includeModule('iblock')) {
            return [];
        }

        $result = [];
        $select = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'DETAIL_PAGE_URL',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE',
            'PROPERTY_MORE_PHOTO',
        ];

        $elementRes = CIBlockElement::GetList([], ['ID' => $productIds], false, false, $select);
        while ($element = $elementRes->GetNext()) {
            $pictureId = $this->resolveProductImageId($element);
            $pictureSrc = '';
            if ($pictureId > 0) {
                $file = CFile::ResizeImageGet(
                    $pictureId,
                    ['width' => 120, 'height' => 120],
                    BX_RESIZE_IMAGE_EXACT,
                    false
                );
                if (!empty($file['src'])) {
                    $pictureSrc = $file['src'];
                }
            }
            if ($pictureSrc === '') {
                $pictureSrc = self::NO_PHOTO_PATH;
            }
            $result[(int)$element['ID']] = [
                'NAME' => (string)($element['~NAME'] ?? $element['NAME'] ?? ''),
                'URL' => (string)($element['DETAIL_PAGE_URL'] ?? ''),
                'IMAGE' => $pictureSrc,
            ];
        }

        return $result;
    }

    private function resolveProductImageId(array $element): int
    {
        $candidates = [
            (int)($element['PREVIEW_PICTURE'] ?? 0),
            (int)($element['DETAIL_PICTURE'] ?? 0),
        ];

        $morePhotoId = $this->extractFirstMorePhotoId($element);
        if ($morePhotoId > 0) {
            $candidates[] = $morePhotoId;
        }

        foreach ($candidates as $candidateId) {
            if ($candidateId > 0) {
                return $candidateId;
            }
        }

        return 0;
    }

    private function extractFirstMorePhotoId(array $element): int
    {

        if (empty($element['IBLOCK_ID']) || empty($element['ID'])) {
            return 0;
        }

        $propertyRes = CIBlockElement::GetProperty(
            (int)$element['IBLOCK_ID'],
            (int)$element['ID'],
            ['sort' => 'asc', 'value_id' => 'asc'],
            ['CODE' => 'MORE_PHOTO']
        );

        while ($property = $propertyRes->Fetch()) {
            $candidateId = $this->normalizeFileValue($property['VALUE'] ?? null);
            if ($candidateId > 0) {
                return $candidateId;
            }
        }

        return 0;
    }

    private function normalizeFileValue($value): int
    {
        if (is_array($value)) {
            if (array_key_exists('VALUE', $value)) {
                return $this->normalizeFileValue($value['VALUE']);
            }
            foreach ($value as $item) {
                $id = $this->normalizeFileValue($item);
                if ($id > 0) {
                    return $id;
                }
            }

            return 0;
        }

        if ($value === null || $value === '' || is_bool($value)) {
            return 0;
        }

        $id = (int)$value;

        return $id > 0 ? $id : 0;
    }

    private function formatQuantity(float $quantity): string
    {
        $rounded = round($quantity);
        if (abs($quantity - $rounded) < 0.00001) {
            return (string)$rounded;
        }

        $formatted = number_format($quantity, 3, ',', ' ');
        return rtrim(rtrim($formatted, '0'), ',');
    }
}
