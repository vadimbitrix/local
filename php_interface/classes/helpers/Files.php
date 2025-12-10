<?php

namespace Vadim24\Helpers;

/**
 * Класс Files предоставляет методы для работы с файлами.
 */
class Files
{
    /**
     * Преобразует количество байт в читаемый формат.
     *
     * @param int $bytes Количество байт.
     * @return string Читаемое представление размера файла.
     */
    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * Сохраняет данные в файл.
     *
     * @param string $filename Путь к файлу, в который необходимо сохранить данные.
     * @param mixed $data Данные для сохранения.
     * @return bool Возвращает true, если данные успешно сохранены.
     */
    public static function saveToFile(string $filename, $data): bool
    {
        $result = file_put_contents($filename, $data);
        return $result !== false;
    }

    /**
     * Читает содержимое файла в строку.
     *
     * @param string $filename Путь к файлу.
     * @return string|false Содержимое файла или false, если файл не удалось прочитать.
     */
    public static function readFromFile(string $filename)
    {
        return file_get_contents($filename);
    }

    /**
     * Удаляет файл по заданному пути.
     *
     * @param string $filename Путь к файлу, который необходимо удалить.
     * @return bool Возвращает true, если файл успешно удален.
     */
    public static function deleteFile(string $filename): bool
    {
        return unlink($filename);
    }

    /**
     * Рекурсивно удаляет директорию вместе с файлами и вложенными папками.
     *
     * @param string $folder Путь к директории для удаления.
     * @return void
     */
    public static function removeFolder(string $folder): void
    {
        if ($files = glob($folder . '/*'))
        {
            foreach ($files as $file)
            {
                if (is_dir($file))
                {
                    self::removeFolder($file);
                }
                else
                {
                    unlink($file);
                }
            }
        }
        rmdir($folder);
    }
}
