<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

if ($arResult['COMPONENT_PAGE'] === 'auth') {
    echo '<div class="personal-auth-required">';
    echo '<p>' . htmlspecialcharsbx(GetMessage('PERSONAL_AUTH_REQUIRED') ?: 'Авторизуйтесь, чтобы получить доступ к личному кабинету.') . '</p>';
    if (!empty($arResult['AUTH_URL'])) {
        echo '<a href="' . htmlspecialcharsbx($arResult['AUTH_URL']) . '">';
        echo htmlspecialcharsbx(GetMessage('PERSONAL_AUTH_GO') ?: 'Перейти к авторизации');
        echo '</a>';
    }
    echo '</div>';
    return;
}

echo '<div class="personal-component">';
echo '<pre>';
echo htmlspecialcharsbx(print_r($arResult, true));
echo '</pre>';
echo '</div>';
