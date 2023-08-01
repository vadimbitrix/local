<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo LANGUAGE_ID ?>" lang="<?php echo LANGUAGE_ID ?>">
<head>
    <? global $APPLICATION, $USER;
    use \Bitrix\Main\Page\Asset;
    use \Bitrix\Main\Localization\Loc;
    Loc::loadLanguageFile(__FILE__);
    $APPLICATION->ShowHead();

    // STRING
    Asset::getInstance()->addString("<link rel='shortcut icon' href='" . SITE_TEMPLATE_PATH . "/dist/img/favicon.ico' />");
    Asset::getInstance()->addString("<meta name='viewport' content='width=device-width, initial-scale=1, viewport-fit=cover'>");

    // CSS
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/dist/css/libs/jquery.fancybox.min.css');
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/dist/css/libs/swiper.min.css');
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/dist/css/style.min.css');

    // JS
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/dist/js/libs/jquery-3.5.1.min.js');
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/dist/js/libs/maskInputPhone.js');
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/dist/js/libs/jquery.fancybox.min.js');
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/dist/js/libs/swiper.min.js');
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/dist/js/script.min.js');


    // Настройки++ (Удобная утилита для хранения одиночных свойств)
    // !!!!! Не забудьте установить модуль Настройки++ !!!!!!
    $test = \Bitrix\Main\Config\Option::get("askaron.settings", "UF_TEST");

    // Properties
//    $section = $APPLICATION->GetDirProperty("section");

//    if(defined("ERROR_404")){
//        $section = 'not-found';
//    }

    ?>
    <title><? $APPLICATION->ShowTitle() ?></title>
</head>
<body>
<? $APPLICATION->ShowPanel(); ?>
<main>
