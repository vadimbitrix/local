<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

global $APPLICATION;

if (!empty($arResult['IDS'])) {
    global $favoritesFilter;
    $favoritesFilter = [
        'ID' => $arResult['IDS'],
        'ACTIVE' => 'Y',
    ];
    ?>
    <div class="favorites-page">
        <?php
        $APPLICATION->IncludeComponent(
            'bitrix:catalog.section',
            'main',
            [
                'IBLOCK_TYPE' => '1c_catalog',
                'IBLOCK_ID' => '8',
                'FILTER_NAME' => 'favoritesFilter',
                'INCLUDE_SUBSECTIONS' => 'Y',
                'SHOW_ALL_WO_SECTION' => 'Y',
                'ELEMENT_SORT_FIELD' => 'sort',
                'ELEMENT_SORT_ORDER' => 'asc',
                'ELEMENT_SORT_FIELD2' => 'id',
                'ELEMENT_SORT_ORDER2' => 'desc',
                'CACHE_TYPE' => 'A',
                'CACHE_TIME' => '36000000',
                'CACHE_FILTER' => 'Y',
                'CACHE_GROUPS' => 'Y',
                'SET_TITLE' => 'N',
                'SET_STATUS_404' => 'N',
                'SHOW_404' => 'N',
                'HIDE_NOT_AVAILABLE' => 'N',
                'HIDE_NOT_AVAILABLE_OFFERS' => 'N',
                'PAGE_ELEMENT_COUNT' => '20',
                'LINE_ELEMENT_COUNT' => '3',
                'PRICE_CODE' => ['BASE'],
                'PRICE_VAT_INCLUDE' => 'N',
                'CONVERT_CURRENCY' => 'Y',
                'CURRENCY_ID' => 'RUB',
                'ADD_PROPERTIES_TO_BASKET' => 'Y',
                'PRODUCT_ID_VARIABLE' => 'id',
                'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
                'PRODUCT_PROPS_VARIABLE' => 'prop',
                'SECTION_ID' => '',
                'SECTION_CODE' => '',
                'SECTION_URL' => '',
                'DETAIL_URL' => '',
                'USE_PRODUCT_QUANTITY' => 'Y',
                'DISPLAY_TOP_PAGER' => 'N',
                'DISPLAY_BOTTOM_PAGER' => 'Y',
                'PAGER_SHOW_ALWAYS' => 'N',
                'PAGER_TEMPLATE' => '.default',
                'PAGER_TITLE' => 'Товары',
                'PROPERTY_CODE' => [],
                'OFFERS_FIELD_CODE' => [],
                'OFFERS_PROPERTY_CODE' => [],
                'OFFERS_CART_PROPERTIES' => [],
                'OFFERS_LIMIT' => '0',
                'LAZY_LOAD' => 'N',
                'LOAD_ON_SCROLL' => 'N',
                'VUE_FILTER_DATA' => [],
                'ACTIVE_FILTERS' => [],
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );
        ?>
    </div>
    <?php
    unset($GLOBALS['favoritesFilter']);
} else {
    ?>
    <div class="favorites-page favorites-page--empty">
        <div class="favorites-empty">
            <p>У вас пока нет избранных товаров.</p>
            <a class="favorites-empty__link" href="/catalog/">Перейти в каталог</a>
        </div>
    </div>
    <?php
}
