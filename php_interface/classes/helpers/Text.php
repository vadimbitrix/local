<?php

namespace Vadim24\Helpers;

/**
 * Класс Text предоставляет различные методы для манипуляции строками.
 */
class Text
{
    /**
     * Сокращает текст до указанной длины без разделения слов.
     *
     * @param string $text Текст для сокращения.
     * @param int $length Максимальная длина сокращенного текста.
     * @param string $suffix Суффикс, добавляемый если текст сокращен.
     * @return string Сокращенный текст.
     */
    public static function truncate(string $text, int $length, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length)
        {
            return $text;
        }

        $result = mb_substr($text, 0, $length - mb_strlen($suffix));
        if (mb_substr($text, $length, 1) != ' ' && mb_strrpos($result, ' ') != false)
        {
            $result = mb_substr($result, 0, mb_strrpos($result, ' '));
        }

        return $result . $suffix;
    }

    /**
     * Преобразует любую строку в URL-дружественный вид.
     *
     * @param string $text Текст для преобразования.
     * @return string URL-дружественная версия текста.
     */
    public static function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        if (empty($text))
        {
            return 'n-a';
        }

        return $text;
    }

    /**
     * Преобразует строку в формат "заглавные буквы".
     *
     * @param string $text Текст для преобразования.
     * @return string Преобразованный текст.
     */
    public static function toTitleCase(string $text): string
    {
        return mb_convert_case($text, MB_CASE_TITLE, "UTF-8");
    }

    /**
     * Поиск и замена первого вхождения строки.
     *
     * @param string $search Искомая строка.
     * @param string $replace Строка замены.
     * @param string $text Исходный текст.
     * @return string Изменённый текст.
     */
    public static function str_replace_once(string $search, string $replace, string $text): string
    {
        $pos = strpos($text, $search);
        if ($pos !== false) {
            return substr_replace($text, $replace, $pos, strlen($search));
        }
        return $text;
    }

    /**
     * Преобразует первый символ в верхний регистр.
     *
     * @param string $str Строка.
     * @param string $encoding Кодировка, по умолчанию UTF-8.
     * @return string Изменённая строка.
     */
    public static function mb_ucfirst(string $str, string $encoding = 'UTF-8'): string
    {
        $str = mb_ereg_replace('^[\ ]+', '', $str);
        $firstChar = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        return $firstChar . mb_substr($str, 1, mb_strlen($str), $encoding);
    }

    /**
     * Возвращает правильную форму слова для указанного числа.
     *
     * @param int $num Число.
     * @param string $form_for_1 Форма для 1.
     * @param string $form_for_2 Форма для 2-4.
     * @param string $form_for_5 Форма для 5-0, 11-14.
     * @return string Подходящая форма слова.
     */
    public static function trueWordTitle(int $num, string $form_for_1, string $form_for_2, string $form_for_5): string
    {
        $num = abs($num) % 100;
        $num_x = $num % 10;
        if ($num > 10 && $num < 20) return $form_for_5;
        if ($num_x > 1 && $num_x < 5) return $form_for_2;
        if ($num_x == 1) return $form_for_1;
        return $form_for_5;
    }

    /**
     * Очищает текст от HTML-тегов, переносов строк и лишних пробелов.
     *
     * @param string $text_desc HTML текст.
     * @return string Очищенный текст.
     */
    public static function html2line(string $text_desc): string
    {
        $search = array(
            "'<script[^>]*?>.*?</script>'si", // Удаление JavaScript
            "'<[\/\!]*?[^<>]*?>'si",          // Удаление HTML тегов
            "'([\r\n])[\s]+'",                // Удаление пробельных символов
            "'&(quot|#34);'i",                // Замена HTML сущностей
            "'&(amp|#38);'i",
            "'&(lt|#60);'i",
            "'&(gt|#62);'i",
            "'&(nbsp|#160);'i",
            "'&(iexcl|#161);'i",
            "'&(cent|#162);'i",
            "'&(pound|#163);'i",
            "'&(copy|#169);'i",
            "'&#(\d+);'e"
        );
        $replace = array(
            "",
            "",
            "\\1",
            "\"",
            "&",
            "<",
            ">",
            " ",
            chr(161),
            chr(162),
            chr(163),
            chr(169),
            "chr(\\1)"
        );
        $text_desc = preg_replace($search, $replace, $text_desc);
        $text_desc = str_replace(array("\r\n", "\r", "\n"), " ", $text_desc);
        $text_desc = preg_replace("/[ \t]+/", " ", $text_desc);
        return trim($text_desc);
    }
}
