<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
    "NAME" => "Всплывающее окно авторизации",
    "DESCRIPTION" => "Компонент для авторизации и регистрации пользователей с поддержкой SMS и каптчи",
    "ICON" => "/images/icon.gif",
    "PATH" => array(
        "ID" => "devob",
        "NAME" => "Devob Components",
        "CHILD" => array(
            "ID" => "auth",
            "NAME" => "Авторизация"
        )
    ),
);
