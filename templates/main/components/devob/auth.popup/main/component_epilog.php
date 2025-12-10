<?php

use Devob\Lib\Builder\VueComponentLoader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


$loader = new VueComponentLoader($templateFolder, $arResult['COMPONENT_ID'], $arResult, [
    'dataKeys' => ['CAPTCHA_KEY', 'IS_AUTHORIZED'],
    'globalVarName' => 'authPopupTpl'
]);

$loader->initialize();
?>
