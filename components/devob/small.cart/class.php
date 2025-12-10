<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Загружаем класс базовой корзины.
 * Bitrix сам найдёт file class.php у компонента devob:cart
 */
CBitrixComponent::includeComponentClass('devob:cart');

class DevobSmallCartComponent extends DevobCartComponent
{
    /**
     * Переопределяем только представление
     * @return void
     */
    public function executeComponent()
    {
        parent::executeComponent();
    }
}
