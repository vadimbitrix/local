<?php
use Bitrix\Main\Loader;
Loader::registerAutoLoadClasses(null, [
    'Vadim24\\Helpers\\Files' => '/local/php_interface/classes/helpers/Files.php',
    'Vadim24\\Helpers\\Utilities' => '/local/php_interface/classes/helpers/Utilities.php',
    'Vadim24\\Helpers\\Text' => '/local/php_interface/classes/helpers/Text.php',
    'Vadim24\\Lib\\Bitrix24Client' => '/local/php_interface/classes/lib/Bitrix24Client.php',
    'Vadim24\\Lib\\CURL' => '/local/php_interface/classes/lib/CURL.php',
    'Vadim24\\Cron\\Agents' => '/local/php_interface/classes/cron/Agents.php'
]);

// Если есть composer
if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php")) {
    require_once($_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php");
}
