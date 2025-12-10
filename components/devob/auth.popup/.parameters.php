<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arTemplateParameters = array(
    "YANDEX_CAPTCHA_KEY" => array(
        "NAME" => "Ключ Яндекс SmartCaptcha (публичный)",
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    "YANDEX_CAPTCHA_SECRET" => array(
        "NAME" => "Секретный ключ Яндекс SmartCaptcha",
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    "SMS_PROVIDER" => array(
        "NAME" => "SMS провайдер",
        "TYPE" => "LIST",
        "VALUES" => array(
            "smsru" => "SMS.ru",
            "smscenter" => "SMS Center (SMSC)",
            "custom" => "Пользовательский"
        ),
        "DEFAULT" => "smsru",
    ),
    "SMS_API_KEY" => array(
        "NAME" => "API ключ SMS провайдера",
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    "SMS_LOGIN" => array(
        "NAME" => "Логин SMS провайдера (для SMSC)",
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    "SMS_PASSWORD" => array(
        "NAME" => "Пароль SMS провайдера (для SMSC)",
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    "PHONE_VALIDATION" => array(
        "NAME" => "Валидация телефона",
        "TYPE" => "CHECKBOX",
        "DEFAULT" => "Y",
    ),
    "AUTO_LOGIN" => array(
        "NAME" => "Автоматический вход после регистрации",
        "TYPE" => "CHECKBOX",
        "DEFAULT" => "Y",
    ),
    "SUCCESS_PAGE" => array(
        "NAME" => "Страница после успешной авторизации",
        "TYPE" => "STRING",
        "DEFAULT" => "/",
    ),
    "SMS_SENDER" => array(
        "NAME" => "Имя отправителя SMS",
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    "SMS_CODE_LIFETIME" => array(
        "NAME" => "Время жизни SMS кода (минут)",
        "TYPE" => "STRING",
        "DEFAULT" => "5",
    ),
);
