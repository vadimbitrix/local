<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);
global $APPLICATION;

// Настройки++ (Удобная утилита для хранения одиночных свойств)
// !!!!! Не забудьте установить модуль Настройки++ !!!!!!
$test = \Bitrix\Main\Config\Option::get("askaron.settings", "UF_TEST");

// Properties
//$section = $APPLICATION->GetDirProperty("section");

?>

</body>
</html>
