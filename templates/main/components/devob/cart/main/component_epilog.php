<?php

use Devob\Lib\Builder\VueComponentLoader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


$loader = new VueComponentLoader($templateFolder, 'devob-cart', $arResult, [
    'dataKeys' => ['ITEMS','TOTALQTY','TOTALSUM','TOTALSUM_PRINT'],
    'globalVarName' => 'cartTpl'
]);

$loader->initialize();
?>
