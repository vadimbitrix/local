<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo LANGUAGE_ID ?>" lang="<?php echo LANGUAGE_ID ?>">
<head>
    <?
    global $APPLICATION;
    $APPLICATION->ShowHead();
    $APPLICATION->IncludeFile("/local/include/system/head.php", [], ["SHOW_BORDER" => false]);

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
<div class="wrapper">
    <? $APPLICATION->IncludeFile("/local/include/system/scripts_after_body.php", [], ["SHOW_BORDER" => false]); ?>
    <? $APPLICATION->ShowPanel(); ?>
    <? $APPLICATION->IncludeFile("/local/include/system/header.php", [], ["SHOW_BORDER" => false]); ?>
    <div class="content">
<main>
