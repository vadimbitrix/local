<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Devob\Lib\SmsClient;

class DevobAuthPopupComponent extends CBitrixComponent implements Controllerable
{
    private ?SmsClient $smsClient = null;
    private ?string $lastSmsError = null;
    public function configureActions()
    {
        return [
            'login' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
            'register' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
            'sendSms' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                ],
            ],
            'verifySms' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                ],
            ],
            'sendRecoverySms' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
            'verifyRecoveryCode' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
            'resetPassword' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    public function executeComponent()
    {
        global $USER;
        global $APPLICATION;

        $this->arResult['CURRENT_USER'] = $USER;
        $this->arResult['IS_AUTHORIZED'] = $USER->IsAuthorized();
        $this->arResult['CAPTCHA_KEY'] = $this->arParams['YANDEX_CAPTCHA_KEY'];
        $this->arResult['COMPONENT_ID'] = 'devob_auth_popup_' . substr(md5($this->getTemplateName() . time()), 0, 6);

        // Проверяем, если пользователь уже авторизован
        if ($this->arResult['IS_AUTHORIZED'] && $_REQUEST['logout'] === 'Y') {
            $USER->Logout();
            LocalRedirect($APPLICATION->GetCurPageParam('', ['logout']));
        }

        $this->includeComponentTemplate();
    }

    public function loginAction()
    {
        global $USER;

        $request = Application::getInstance()->getContext()->getRequest();

        $login = $request->getPost('login');
        $password = $request->getPost('password');
        $captchaToken = $request->getPost('captcha_token');

        // Проверка каптчи
        if (!$this->verifyCaptcha($captchaToken)) {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_CAPTCHA_ERROR')
            ];
        }

        $authResult = $USER->Login($login, $password, 'Y');

        if ($authResult === true) {
            return [
                'success' => true,
                'redirect' => $this->arParams['SUCCESS_PAGE'] ?: '/personal/'
            ];
        }

        return [
            'success' => false,
            'error' => Loc::getMessage('DEVOB_AUTH_LOGIN_ERROR')
        ];
    }

    public function registerAction()
    {
        $request = Application::getInstance()->getContext()->getRequest();

        $phone = $request->getPost('phone');
        $password = $request->getPost('password');
        $name = $request->getPost('name');
        $captchaToken = $request->getPost('captcha_token');

        // Проверка каптчи
        if (!$this->verifyCaptcha($captchaToken)) {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_CAPTCHA_ERROR')
            ];
        }

        // Валидация телефона
        if ($this->arParams['PHONE_VALIDATION'] === 'Y' && !$this->validatePhone($phone)) {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_PHONE_ERROR')
            ];
        }

        if ($this->isPhoneExists($phone)) {
            return [
                'success' => false,
                'error'   => Loc::getMessage('DEVOB_AUTH_PHONE_EXISTS')
            ];
        }

        // Создаем пользователя
        $user = new CUser();
        $arFields = [
            'LOGIN' => $phone,
            'PHONE_NUMBER' => $phone,
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $password,
            'NAME' => $name,
            'ACTIVE' => 'N', // Активируем после подтверждения SMS
        ];

        $userID = $user->Add($arFields);

        \Bitrix\Main\Diag\Debug::dumpToFile([
            'userID' => $userID,
            'is_numeric' => is_numeric($userID),
            'user_errors' => $user->LAST_ERROR
        ], 'user_creation', 'LogSMS');

        if (intval($userID) > 0) {
            // Отправляем SMS код
            $smsCode = $this->generateSmsCode();
            $this->saveSmsCode($phone, $smsCode, $userID, 'register');

            if ($this->sendSms($phone, $smsCode)) {
                return [
                    'success' => true,
                    'need_sms_confirmation' => true,
                    'phone' => $phone
                ];
            }

            return [
                'success' => false,
                'error' => $this->getLastSmsError() ?: Loc::getMessage('DEVOB_AUTH_SMS_SEND_ERROR')
            ];
        }

        return [
            'success' => false,
            'error' => $user->LAST_ERROR ?: ($this->getLastSmsError() ?: Loc::getMessage('DEVOB_AUTH_REGISTER_ERROR'))
        ];
    }

    public function sendSmsAction()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $phone = $this->normalizePhone($request->getPost('phone'));
        $existingUserID = $this->getUserIdByPhone($phone);

        $smsCode = $this->generateSmsCode();
        $this->saveSmsCode($phone, $smsCode, $existingUserID, 'register');

        if ($this->sendSms($phone, $smsCode)) {
            return ['success' => true];
        }

        return [
            'success' => false,
            'error' => $this->getLastSmsError() ?: Loc::getMessage('DEVOB_AUTH_SMS_SEND_ERROR')
        ];
    }

    public function verifySmsAction()
    {
        global $USER;

        $request = Application::getInstance()->getContext()->getRequest();
        $phone = $this->normalizePhone($request->getPost('phone'));
        $code = $request->getPost('code');
        $userID = $this->getUserIdByPhone($phone);

        if ($this->verifySmsCode($phone, $code)) {
            if ($userID) {
                $user = new CUser();
                $updateResult = $user->Update($userID, ['ACTIVE' => 'Y']);

                if (!$updateResult) {
                    \Bitrix\Main\Diag\Debug::dumpToFile([
                        'message' => 'User activation failed',
                        'userId' => $userID,
                        'error' => $user->LAST_ERROR
                    ], 'user_activation_error', 'LogSMS');

                    return [
                        'success' => false,
                        'error' => 'Ошибка активации пользователя: ' . $user->LAST_ERROR,
                    ];
                }

                // Проверяем, что пользователь действительно активен
                $rsUser = \CUser::GetByID($userID);
                $arUser = $rsUser->Fetch();

                \Bitrix\Main\Diag\Debug::dumpToFile([
                    'message' => 'User data after activation',
                    'userId' => $userID,
                    'active' => $arUser['ACTIVE'] ?? 'not found',
                    'login' => $arUser['LOGIN'] ?? 'not found'
                ], 'user_check_after_activation', 'LogSMS');

                if (!$arUser || $arUser['ACTIVE'] !== 'Y') {
                    return [
                        'success' => false,
                        'error' => 'Пользователь не активирован',
                    ];
                }

                // Явно инициализируем сессию если она не запущена
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }

                // Авторизуем с сохранением в сессию
                $authorizeResult = $USER->Authorize($userID, true, false);

                \Bitrix\Main\Diag\Debug::dumpToFile([
                    'message' => 'Authorize attempt',
                    'userId' => $userID,
                    'authorize_result' => $authorizeResult,
                    'is_authorized_after' => $USER->IsAuthorized(),
                    'user_id_after' => $USER->GetID(),
                    'session_id' => session_id()
                ], 'authorize_attempt', 'LogSMS');

                if ($authorizeResult === false) {
                    return [
                        'success' => false,
                        'error' => 'Ошибка авторизации: ' . ($USER->LAST_ERROR ?? 'неизвестная ошибка'),
                    ];
                }

                // Проверяем результат авторизации
                if (!$USER->IsAuthorized()) {
                    return [
                        'success' => false,
                        'error' => 'Авторизация не применилась',
                    ];
                }

                // УДАЛЯЕМ данные ПОСЛЕ успешной обработки
                unset($_SESSION['SMS_CODES'][$phone]);

                return [
                    'success' => true,
                    'auto_login' => $this->arParams['AUTO_LOGIN'] === 'Y',
                    'redirect' => $this->arParams['SUCCESS_PAGE'] ?: '/personal/profile/',
                    'need_reload' => true
                ];
            } else {
                \Bitrix\Main\Diag\Debug::dumpToFile('userID не найден', 'no_user_id', 'LogSMS');
            }
        }

        return [
            'success' => false,
            'error' => Loc::getMessage('DEVOB_AUTH_SMS_VERIFY_ERROR')
        ];
    }

    public function sendRecoverySmsAction()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $rawPhone = $request->getPost('phone');
        $captchaToken = $request->getPost('captcha_token');

        if (!$this->verifyCaptcha($captchaToken)) {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_CAPTCHA_ERROR')
            ];
        }

        if (empty($rawPhone)) {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_PHONE_REQUIRED')
            ];
        }

        $phone = $this->normalizePhone($rawPhone);
        \Bitrix\Main\Diag\Debug::dumpToFile($rawPhone, '$rawPhone', 'LogPhone');
        \Bitrix\Main\Diag\Debug::dumpToFile($phone, '$phone', 'LogPhone');
        $user = $this->findActiveUserByPhone($phone);
        \Bitrix\Main\Diag\Debug::dumpToFile($user, '$user', 'LogPhone');

        if (!$user) {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_USER_NOT_FOUND')
            ];
        }

        $smsCode = $this->generateSmsCode();
        $this->saveSmsCode($phone, $smsCode, (int)$user['ID'], 'recovery');

        if ($this->sendSms($phone, $smsCode)) {
            return [
                'success' => true,
                'phone' => $phone,
                'message' => Loc::getMessage('DEVOB_AUTH_RECOVERY_SMS_SENT')
            ];
        }

        return [
            'success' => false,
            'error' => $this->getLastSmsError() ?: Loc::getMessage('DEVOB_AUTH_SMS_SEND_ERROR')
        ];
    }

    public function verifyRecoveryCodeAction()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $phone = $this->normalizePhone($request->getPost('phone'));
        $code = $request->getPost('code');

        if (empty($phone) || empty($code)) {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_CODE_REQUIRED')
            ];
        }

        $sessionData = $_SESSION['SMS_CODES'][$phone] ?? null;

        if (!$sessionData || ($sessionData['purpose'] ?? 'register') !== 'recovery') {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_CODE_NOT_FOUND')
            ];
        }

        if (time() > ($sessionData['expires'] ?? 0)) {
            unset($_SESSION['SMS_CODES'][$phone]);
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_CODE_EXPIRED')
            ];
        }

        if (($sessionData['attempts'] ?? 0) >= 3) {
            unset($_SESSION['SMS_CODES'][$phone]);
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_CODE_ATTEMPTS')
            ];
        }

        if ($this->verifySmsCode($phone, $code)) {
            $_SESSION['SMS_CODES'][$phone]['verified'] = true;
            $_SESSION['SMS_CODES'][$phone]['verified_at'] = time();

            return [
                'success' => true,
                'message' => Loc::getMessage('DEVOB_AUTH_RECOVERY_CODE_SUCCESS')
            ];
        }

        $updatedSession = $_SESSION['SMS_CODES'][$phone] ?? null;

        if (!$updatedSession) {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_CODE_ATTEMPTS')
            ];
        }

        $attemptsLeft = max(0, 3 - ($updatedSession['attempts'] ?? 0));

        return [
            'success' => false,
            'error' => str_replace('#ATTEMPTS#', (string)$attemptsLeft, Loc::getMessage('DEVOB_AUTH_RECOVERY_CODE_INVALID'))
        ];
    }

    public function resetPasswordAction()
    {
        global $USER;

        $request = Application::getInstance()->getContext()->getRequest();
        $phone = $this->normalizePhone($request->getPost('phone'));
        $password = (string)$request->getPost('password');
        $confirmPassword = (string)$request->getPost('confirm_password');

        if (mb_strlen($password) < 6) {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_PASSWORD_SHORT')
            ];
        }

        if ($password !== $confirmPassword) {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_PASSWORD_MISMATCH')
            ];
        }

        $sessionData = $_SESSION['SMS_CODES'][$phone] ?? null;

        if (!$sessionData || ($sessionData['purpose'] ?? 'register') !== 'recovery') {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_SESSION_NOT_FOUND')
            ];
        }

        if (empty($sessionData['verified'])) {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_CODE_NOT_VERIFIED')
            ];
        }

        if (time() > ($sessionData['expires'] ?? 0)) {
            unset($_SESSION['SMS_CODES'][$phone]);
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_CODE_EXPIRED')
            ];
        }

        $userId = (int)($sessionData['user_id'] ?? 0);

        if ($userId <= 0) {
            return [
                'success' => false,
                'error' => Loc::getMessage('DEVOB_AUTH_RECOVERY_USER_NOT_FOUND')
            ];
        }

        $user = new CUser();
        $updateResult = $user->Update($userId, [
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $password,
        ]);

        if (!$updateResult) {
            return [
                'success' => false,
                'error' => $user->LAST_ERROR ?: Loc::getMessage('DEVOB_AUTH_RECOVERY_PASSWORD_SAVE_ERROR')
            ];
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!$USER->IsAuthorized() || (int)$USER->GetID() !== $userId) {
            $authorizeResult = $USER->Authorize($userId, true, false);

            if ($authorizeResult === false && !$USER->IsAuthorized()) {
                return [
                    'success' => false,
                    'error' => Loc::getMessage('DEVOB_AUTH_SMS_AUTO_LOGIN_ERROR')
                ];
            }
        }

        unset($_SESSION['SMS_CODES'][$phone]);

        return [
            'success' => true,
            'redirect' => '/personal/profile/',
            'message' => Loc::getMessage('DEVOB_AUTH_RECOVERY_SUCCESS')
        ];
    }


    /**
     * Получение инстанса SMS клиента с настройками из опций
     * @return SmsClient|null
     */
    private function getSmsClient(): ?SmsClient
    {
        if ($this->smsClient === null) {
            try {
                // Получаем настройки из опций модуля
                $login = Option::get("askaron.settings", "UF_SMS_LOGIN", "");
                $password = Option::get("askaron.settings", "UF_SMS_PASSWORD", "");
                $sender = Option::get("askaron.settings", "UF_SMS_SENDER", "");
                $debug = Option::get("askaron.settings", "UF_SMS_DEBUG", "N") === "Y";
                $callbackUrl = Option::get("askaron.settings", "UF_SMS_CALLBACK_URL", "");

                // Проверяем обязательные настройки
                if (empty($login) || empty($password)) {
                    \Bitrix\Main\Diag\Debug::dumpToFile([
                        'error' => 'SMS настройки не заданы',
                        'login' => !empty($login) ? 'установлен' : 'пустой',
                        'password' => !empty($password) ? 'установлен' : 'пустой'
                    ], 'sms_config_missing', 'LogSMS');

                    return null;
                }

                // Создаем клиент
                $this->smsClient = new SmsClient(
                    login: $login,
                    password: $password,
                    usePost: true,
                    useHttps: true,
                    charset: 'utf-8',
                    debug: $debug,
                    callbackUrl: !empty($callbackUrl) ? $callbackUrl : null,
                    logFile: $_SERVER['DOCUMENT_ROOT'] . '/local/logs/sms_auth.log'
                );

                \Bitrix\Main\Diag\Debug::dumpToFile([
                    'status' => 'SMS клиент успешно создан',
                    'login' => $login,
                    'debug' => $debug,
                    'callback_url' => $callbackUrl
                ], 'sms_client_created', 'LogSMS');

            } catch (\Throwable $e) {
                \Bitrix\Main\Diag\Debug::dumpToFile([
                    'error' => 'Ошибка создания SMS клиента',
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 'sms_client_error', 'LogSMS');

                return null;
            }
        }

        return $this->smsClient;
    }

    /**
     * Отправка SMS через SmsClient
     */
    private function sendSms($phone, $code): bool
    {
        $phone = $this->normalizePhone($phone);
        $this->setLastSmsError(null);

        // Получаем шаблон сообщения из настроек
        $template = Option::get("askaron.settings", "UF_SMS_TEMPLATE", "Код подтверждения: {CODE}");
        $message = str_replace('{CODE}', $code, $template);

        \Bitrix\Main\Diag\Debug::dumpToFile([
            'phone' => $phone,
            'message' => $message,
            'code' => $code
        ], 'send_sms_start', 'LogSMS');

        // Получаем клиент
        $client = $this->getSmsClient();

        if (!$client) {
            $this->setLastSmsError(Loc::getMessage('DEVOB_AUTH_SMS_CLIENT_ERROR'));
            \Bitrix\Main\Diag\Debug::dumpToFile(
                'SMS клиент не инициализирован',
                'client_not_initialized',
                'LogSMS'
            );
            return false;
        }

        try {
            // Оптимизируем сообщение под одну SMS (если метод добавлен)
            if (method_exists($client, 'optimizeMessage')) {
                $message = $client->optimizeMessage($message, 0);
            }

            // Получаем имя отправителя
            $sender = Option::get("askaron.settings", "UF_SMS_SENDER", false);
            if (empty($sender)) {
                $sender = false;
            }

            // Отправляем SMS
            [$id, $cnt, $cost, $balance] = $client->sendSms(
                phones: $phone,
                message: $message,
                translit: 0,
                sender: $sender
            );

            // Логируем результат
            \Bitrix\Main\Diag\Debug::dumpToFile([
                'success' => $cnt > 0,
                'sms_id' => $id,
                'count' => $cnt,
                'cost' => $cost,
                'balance' => $balance,
                'phone' => $phone,
                'message' => $message
            ], 'sms_send_result', 'LogSMS');

            // Проверяем успешность
            if ($cnt > 0) {
                // Сохраняем ID сообщения для отслеживания
                $this->saveSmsId($phone, $id);
                $this->setLastSmsError(null);
                return true;
            }

            // Обработка ошибок SMSC
            $errorCode = abs($cnt);
            $this->logSmsError($errorCode, $phone);

            if (!$this->getLastSmsError()) {
                $this->setLastSmsError(Loc::getMessage('DEVOB_AUTH_SMS_SEND_ERROR'));
            }

            return false;

        } catch (\Throwable $e) {
            $this->setLastSmsError(Loc::getMessage('DEVOB_AUTH_SMS_SEND_EXCEPTION'));
            \Bitrix\Main\Diag\Debug::dumpToFile([
                'error' => 'Исключение при отправке SMS',
                'message' => $e->getMessage(),
                'phone' => $phone,
                'trace' => $e->getTraceAsString()
            ], 'sms_send_exception', 'LogSMS');

            return false;
        }
    }

    /**
     * Сохранение ID SMS для отслеживания статуса
     */
    private function saveSmsId($phone, $smsId): void
    {
        if (!isset($_SESSION['SMS_IDS'])) {
            $_SESSION['SMS_IDS'] = [];
        }

        $_SESSION['SMS_IDS'][$phone] = [
            'id' => $smsId,
            'time' => time()
        ];

        \Bitrix\Main\Diag\Debug::dumpToFile([
            'phone' => $phone,
            'sms_id' => $smsId
        ], 'save_sms_id', 'LogSMS');
    }

    /**
     * Логирование ошибок SMSC
     */
    private function logSmsError($errorCode, $phone): void
    {
        $errors = [
            1 => 'Ошибка в параметрах',
            2 => 'Неверный логин или пароль',
            3 => 'Недостаточно средств на счете',
            4 => 'IP-адрес временно заблокирован',
            5 => 'Неверный формат даты',
            6 => 'Сообщение запрещено',
            7 => 'Неверный формат номера телефона',
            8 => 'Сообщение на данный номер не может быть доставлено',
            9 => 'Отправка более одного одинакового запроса'
        ];

        $errorMsg = $errors[$errorCode] ?? 'Неизвестная ошибка';

        \Bitrix\Main\Diag\Debug::dumpToFile([
            'error_code' => $errorCode,
            'error_message' => $errorMsg,
            'phone' => $phone
        ], 'smsc_error', 'LogSMS');

        $this->setLastSmsError($errorMsg);
    }

    private function setLastSmsError(?string $message): void
    {
        $this->lastSmsError = $message ? trim((string)$message) : null;
    }

    private function getLastSmsError(): ?string
    {
        return $this->lastSmsError;
    }

    private function findActiveUserByPhone(string $phone): ?array
    {
        $variants = $this->generatePhoneVariants($phone);

        foreach ($variants as $variant) {
            \Bitrix\Main\Diag\Debug::dumpToFile([
                'search_variant' => $variant,
                'normalized_input' => $phone
            ], 'phone_search_variant', 'LogPhone');

            $rsUser = \CUser::GetList(
                $by = 'ID',
                $order = 'ASC',
                [
                    'LOGIC' => 'AND',
                    '=ACTIVE' => 'Y',
                    [
                        'LOGIC' => 'OR',
                        '=LOGIN' => $variant,
                        '=PERSONAL_PHONE' => $variant,
                        '=PERSONAL_MOBILE' => $variant,
                        '=PHONE_NUMBER' => $variant
                    ]
                ],
                ['SELECT' => ['ID', 'LOGIN', 'ACTIVE', 'PHONE_NUMBER', 'PERSONAL_PHONE', 'PERSONAL_MOBILE']]
            );

            $user = $rsUser->Fetch();
            if ($user) {
                if (
                    $user['LOGIN'] !== $variant &&
                    $user['PERSONAL_PHONE'] !== $variant &&
                    $user['PERSONAL_MOBILE'] !== $variant &&
                    $user['PHONE_NUMBER'] !== $variant
                ) {
                    \Bitrix\Main\Diag\Debug::dumpToFile([
                        'filter_variant' => $variant,
                        'wrong_user' => $user,
                    ], 'user_filter_mismatch', 'LogPhone');
                    continue; // пропускаем и ищем дальше
                }

                \Bitrix\Main\Diag\Debug::dumpToFile([
                    'found_user' => $user,
                    'matched_variant' => $variant
                ], 'user_found_by_variant', 'LogPhone');

                return $user;
            }

        }

        return null;
    }

    private function generatePhoneVariants(string $phone): array
    {
        $clean = preg_replace('/\D/', '', $phone);  // Только цифры: 79995605544

        $variants = [$clean];  // 79995605544

        // +7 вариант
        if (substr($clean, 0, 1) === '7' && strlen($clean) === 11) {
            $variants[] = '+7' . substr($clean, 1);  // +79995605544
        }

        // Если был с 8, добавить вариант с 7
        if (substr($clean, 0, 1) === '8') {
            $variants[] = '7' . substr($clean, 1);  // 79995605544 из 89995605544
        }

        return array_unique($variants);
    }

    private function isPhoneExists(string $phone): bool
    {
        return (bool)$this->findActiveUserByPhone($phone);
    }



    private function verifyCaptcha($token)
    {
        $secretKey = $this->arParams['YANDEX_CAPTCHA_SECRET'];

        if (empty($secretKey) || empty($token)) {
            return true; // Если настройки не заданы - пропускаем
        }

        $params = [
            'secret' => $secretKey,
            'token' => $token,
            'ip' => $this->getUserIP()
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://smartcaptcha.yandexcloud.net/validate?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Ошибка проверки капчи: " . $error);
            return true;
        }

        if ($httpCode !== 200) {
            error_log("Неверный HTTP код от Яндекс SmartCaptcha: " . $httpCode);
            return true;
        }

        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Ошибка парсинга JSON ответа от SmartCaptcha");
            return true;
        }

        return isset($result['status']) && $result['status'] === 'ok';
    }

    /**
     * Получение реального IP пользователя
     */
    private function getUserIP()
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private function normalizePhone($phone)
    {
        // Удаляем все кроме цифр
        $phone = preg_replace('/\D/', '', $phone);

        // Если начинается с 8, заменяем на 7
        if (substr($phone, 0, 1) === '8') {
            $phone = '7' . substr($phone, 1);
        }

        // Если не начинается с 7, добавляем 7
        if (substr($phone, 0, 1) !== '7') {
            $phone = '7' . $phone;
        }

        return $phone;
    }

    private function validatePhone($phone)
    {
        // Базовая валидация российского номера
        return preg_match('/^\+?7[0-9]{10}$/', $phone);
    }

    private function generateSmsCode()
    {
        return rand(1000, 9999);
    }

    private function saveSmsCode($phone, $code, $userID = null, string $purpose = 'register')
    {
        $phone = $this->normalizePhone($phone);
        $lifetime = intval($this->arParams['SMS_CODE_LIFETIME']) ?: 5;

        // Если userID не передан, пытаемся получить из существующих данных
        if ($userID === null) {
            $existingData = $_SESSION['SMS_CODES'][$phone] ?? null;
            $userID = $existingData['user_id'] ?? null;
        }

        $_SESSION['SMS_CODES'][$phone] = [
            'code' => $code,
            'time' => time(),
            'user_id' => $userID,
            'attempts' => 0,
            'expires' => time() + ($lifetime * 60),
            'purpose' => $purpose,
            'verified' => false,
            'verified_at' => null
        ];

        \Bitrix\Main\Diag\Debug::dumpToFile([
            'phone' => $phone,
            'code' => $code,
            'user_id' => $userID,
            'action' => 'SMS код сохранен'
        ], 'save_sms_code', 'LogSMS');
    }

    private function verifySmsCode($phone, $code)
    {

        \Bitrix\Main\Diag\Debug::dumpToFile([
            'incoming_phone' => $phone,
            'incoming_code' => $code,
            'session_data' => $_SESSION['SMS_CODES'] ?? 'empty',
            'phone_exists_in_session' => isset($_SESSION['SMS_CODES'][$phone]) ? 'YES' : 'NO'
        ], 'verify_debug', 'LogSMS');

        $savedData = $_SESSION['SMS_CODES'][$phone] ?? null;
        \Bitrix\Main\Diag\Debug::dumpToFile('SMS код в сессии', 'SESSION', 'LogSMS');
        if (!$savedData) {
            \Bitrix\Main\Diag\Debug::dumpToFile('SMS код не найден в сессии', 'no_saved_data', 'LogSMS');
            return false;
        }

        // Проверяем истечение времени
        if (time() > $savedData['expires']) {
            unset($_SESSION['SMS_CODES'][$phone]);
            \Bitrix\Main\Diag\Debug::dumpToFile('SMS код истёк', 'code_expired', 'LogSMS');
            return false;
        }

        // Увеличиваем счетчик попыток
        $_SESSION['SMS_CODES'][$phone]['attempts']++;

        // Ограничиваем количество попыток
        if ($_SESSION['SMS_CODES'][$phone]['attempts'] > 3) {
            unset($_SESSION['SMS_CODES'][$phone]);
            \Bitrix\Main\Diag\Debug::dumpToFile('Превышено количество попыток', 'too_many_attempts', 'LogSMS');
            return false;
        }

        \Bitrix\Main\Diag\Debug::dumpToFile([
            'saved_code' => $savedData['code'],
            'incoming_code' => $code,
            'codes_match' => $savedData['code'] == $code ? 'YES' : 'NO',
            'saved_code_type' => gettype($savedData['code']),
            'incoming_code_type' => gettype($code)
        ], 'code_comparison', 'LogSMS');

        if ($savedData['code'] == $code) {
            \Bitrix\Main\Diag\Debug::dumpToFile('Код верный, возвращаем true', 'code_correct', 'LogSMS');
            $_SESSION['SMS_CODES'][$phone]['verified'] = true;
            $_SESSION['SMS_CODES'][$phone]['verified_at'] = time();
            return true;
        }

        return false;
    }

    private function getUserIdByPhone($phone)
    {
        $savedData = $_SESSION['SMS_CODES'][$phone] ?? null;
        return $savedData['user_id'] ?? null;
    }
}
