<?php
use Devob\Lib\Builder\VueComponentLoader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$loader = new VueComponentLoader($templateFolder, 'devob-favorites', $arResult, [
    'dataKeys' => ['ITEMS', 'TOTAL', 'IDS', 'SIGNED_PARAMETERS', 'IS_AUTHORIZED', 'HL_BLOCK_ID'],
    'globalVarName' => 'favoritesTpl'
]);

$loader->initialize();
