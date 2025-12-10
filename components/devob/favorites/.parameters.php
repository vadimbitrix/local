<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentParameters = [
    'GROUPS' => [],
    'PARAMETERS' => [
        'HL_BLOCK_ID' => [
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('DEVOB_FAVORITES_PARAM_HL_BLOCK_ID') ?: 'ID HL-блока',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'COOKIE_TTL' => [
            'PARENT' => 'ADDITIONAL_SETTINGS',
            'NAME' => Loc::getMessage('DEVOB_FAVORITES_PARAM_COOKIE_TTL') ?: 'Время жизни cookie (секунды)',
            'TYPE' => 'STRING',
            'DEFAULT' => '31536000',
        ],
    ],
];
