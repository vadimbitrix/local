<?php

namespace Vadim24\Helpers;

/**
 * Класс CookieHelper предоставляет методы для работы с куками.
 */
class Cookie
{
    /**
     * Получает значение куки.
     *
     * @param string $name Имя куки.
     * @return mixed Значение куки, десериализованное, или пустой массив, если куки нет.
     */
    public static function getCookie(string $name)
    {
        $arResult = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getCookie($name);
        if ($arResult)
        {
            return unserialize($arResult);
        }
        else
        {
            return [];
        }
    }

    /**
     * Устанавливает куки.
     *
     * @param string $name Имя куки.
     * @param mixed $value Значение куки.
     * @param int|null $expires Время жизни куки.
     */
    public static function setCookie(string $name, $value, $expires = null)
    {
        $cookie = new \Bitrix\Main\Web\Cookie($name, serialize($value), $expires);
        $cookie->setSpread(\Bitrix\Main\Web\Cookie::SPREAD_DOMAIN);
        $cookie->setHttpOnly(false);
        \Bitrix\Main\Application::getInstance()->getContext()->getResponse()->addCookie($cookie);
        \Bitrix\Main\Application::getInstance()->getContext()->getResponse()->flush("");
    }

    /**
     * Удаляет куки.
     *
     * @param string $name Имя куки.
     */
    public static function deleteCookie(string $name)
    {
        $cookie = new \Bitrix\Main\Web\Cookie($name, '', time() - 3600);
        $cookie->setSpread(\Bitrix\Main\Web\Cookie::SPREAD_DOMAIN);
        $cookie->setHttpOnly(false);
        \Bitrix\Main\Application::getInstance()->getContext()->getResponse()->addCookie($cookie);
        \Bitrix\Main\Application::getInstance()->getContext()->getResponse()->flush("");
    }

    /**
     * Проверяет, установлена ли куки.
     *
     * @param string $name Имя куки.
     * @return bool Возвращает true, если куки установлена.
     */
    public static function hasCookie(string $name): bool
    {
        return \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getCookie($name) !== null;
    }
}
