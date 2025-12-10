<?php

namespace Vadim24\Helpers;

use Bitrix\Main\Diag\Debug as BitrixDebug;

/**
 * Класс Debug предоставляет методы для отладки в Bitrix.
 */
class Debug
{
    /**
     * Выводит данные в консоль браузера, если пользователь является администратором.
     *
     * @param mixed $data Данные для вывода.
     */
    public static function consoleLog($data)
    {
        global $USER;
        if ($USER->IsAdmin()) {
            $json = json_encode(unserialize(str_replace(['NAN;', 'INF;'], '0;', serialize($data))));
            echo '<script>';
            echo 'console.log("---------CONSOLE DEBUG-------");';
            echo 'console.log(' . $json . ');';
            echo 'console.log("---------END DEBUG-------");';
            echo '</script>';
        }
    }

    /**
     * Выводит данные с использованием print_r в удобочитаемом виде.
     *
     * @param mixed $o Объект или массив для вывода.
     */
    public static function pr($o)
    {
        $bt = debug_backtrace();
        $bt = $bt[0];
        $dRoot = $_SERVER["DOCUMENT_ROOT"];
        $dRoot = str_replace("/", "\\", $dRoot);
        $bt["file"] = str_replace($dRoot, "", $bt["file"]);
        $dRoot = str_replace("\\", "/", $dRoot);
        $bt["file"] = str_replace($dRoot, "", $bt["file"]);
        echo "<div style='font-size:9pt; color:#000; background:#fff; border:1px dashed #000;'>";
        echo "<div style='padding:3px 5px; background:#99CCFF; font-weight:bold;'>File: {$bt["file"]} [{$bt["line"]}]</div>";
        echo "<pre style='padding:10px; color: black'>" . print_r($o, true) . "</pre>";
        echo "</div>";
    }

    /**
     * Выводит данные в файл, определенный в настройках Bitrix.
     *
     * @param mixed $data Данные для логирования.
     * @param string $title Заголовок лога.
     * @param string $filename Путь к файлу относительно корня сайта.
     */
    public static function logToFile($data, $title = '', $filename = '')
    {
        BitrixDebug::writeToFile($data, $title, $_SERVER["DOCUMENT_ROOT"] . $filename);
    }

    /**
     * Выводит данные в стандартный файл лога Bitrix.
     *
     * @param mixed $data Данные для логирования.
     */
    public static function log($data)
    {
        AddMessage2Log($data);
    }

    /**
     * Выводит данные с использованием var_dump в удобочитаемом виде.
     *
     * @param mixed $data Данные для вывода.
     */
    public static function dump($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
}
