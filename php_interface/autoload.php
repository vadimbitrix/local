<?php
use Bitrix\Main\Loader;
Loader::registerAutoLoadClasses(null, [

]);

// Если есть composer
if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php")) {
    require_once($_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php");
}
