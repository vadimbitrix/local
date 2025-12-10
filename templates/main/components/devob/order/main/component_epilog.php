<?php
use Bitrix\Main\Page\Asset;
use Devob\Lib\Builder\VueComponentLoader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Asset::getInstance()->addCss($templateFolder . '/style.css');

$loader = new VueComponentLoader($templateFolder, $arResult['COMPONENT_ID'], $arResult, [
    'dataKeys' => ['CART', 'USER', 'DELIVERY_METHODS', 'PAYMENT_METHODS', 'PICKUP_HINTS', 'SETTINGS'],
    'globalVarName' => 'orderTpl'
]);

$loader->initialize();
?>
