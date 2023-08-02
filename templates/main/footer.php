<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);
global $APPLICATION;

// Настройки++ (Удобная утилита для хранения одиночных свойств)
// !!!!! Не забудьте установить модуль Настройки++ !!!!!!
$test = Option::get("askaron.settings", "UF_TEST");

// Properties
//$section = $APPLICATION->GetDirProperty("section");

?>
</div> <!-- content -->
<? $APPLICATION->IncludeFile("/local/include/system/footer.php", [], ["SHOW_BORDER" => false]); ?>
<? $APPLICATION->IncludeFile("/local/include/system/popup.php", [], ["SHOW_BORDER" => false]); ?>
<? $APPLICATION->IncludeFile("/local/include/system/scripts_before_body.php", [], ["SHOW_BORDER" => false]); ?>
</div> <!-- wrapper -->
</body>
</html>
