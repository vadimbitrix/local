<?php

namespace Vadim24\Helpers;

/**
 * Класс ArrayHelper предоставляет методы для манипуляции и обработки массивов.
 */
class ArrayHelper
{
    /**
     * Рекурсивно переворачивает массив.
     *
     * @param array $arr Массив для обработки.
     * @return array Перевернутый массив.
     */
    public static function arrayReverseRecursive(array $arr): array
    {
        foreach ($arr as $key => &$val)
        {
            if (is_array($val))
            {
                $val = self::arrayReverseRecursive($val);
            }
        }
        return array_reverse($arr);
    }

    /**
     * Производит поиск в массиве по ключу и его значению.
     *
     * @param array $array Массив для поиска.
     * @param string $field Ключ для поиска.
     * @param mixed $value Значение для сравнения.
     * @return array|bool Найденный элемент или false, если элемент не найден.
     */
    public static function searchInArray(array $array, string $field, $value)
    {
        foreach ($array as $item)
        {
            if (isset($item[$field]) && $item[$field] == $value)
            {
                return $item;
            }
        }
        return false;
    }

    /**
     * Сливает два массива рекурсивно с заменой значений основного массива.
     *
     * @param array $array1 Основной массив.
     * @param array $array2 Массив для слияния.
     * @return array Результат слияния.
     */
    public static function mergeArrays(array $array1, array $array2): array
    {
        foreach ($array2 as $key => $value)
        {
            if (is_array($value) && isset($array1[$key]) && is_array($array1[$key]))
            {
                $array1[$key] = self::mergeArrays($array1[$key], $value);
            }
            else
            {
                $array1[$key] = $value;
            }
        }
        return $array1;
    }

    /**
     * Удаляет элементы массива с указанными ключами.
     *
     * @param array $array Массив для обработки.
     * @param array $keys Список ключей для удаления.
     * @return array Массив без удаленных ключей.
     */
    public static function unsetKeys(array $array, array $keys): array
    {
        foreach ($keys as $key)
        {
            unset($array[$key]);
        }
        return $array;
    }
}
