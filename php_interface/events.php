<?php
use Bitrix\Main\EventManager;
// удяляем скрипты и стили ядра при отдаче сайта пользователям
//EventManager::getInstance()->addEventHandler("main", "OnEndBufferContent", ["\Dev\Settings", "deleteKernelJs"]);
//EventManager::getInstance()->addEventHandler("main", "OnEndBufferContent", ["\Dev\Settings", "deleteKernelCss"]);



