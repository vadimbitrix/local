<?php

use Devob\Lib\Builder\VueComponentLoader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$loader = new VueComponentLoader($templateFolder, 'devob-small-cart', $arResult, [
    'dataKeys' => ['ITEMS','TOTALQTY','TOTALSUM','TOTALSUM_PRINT'],
    'globalVarName' => 'smallCartTpl'
]);

$loader->initialize();
?>
