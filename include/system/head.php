<?
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);
/*META*/
Asset::getInstance()->addString('<meta charset="UTF-8">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, maximum-scale=1, minimum-scale=1" charset="utf-8">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta http-equiv="X-UA-Compatible" content="ie=edge">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="imagetoolbar" content="no">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="msthemecompatible" content="no">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="cleartype" content="no">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="HandheldFriendly" content="True">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="format-detection" content="telephone=no">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="format-detection" content="address=no">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="google" content="notranslate">', true, 'BEFORE_CSS');
/*FAVICON*/
Asset::getInstance()->addString('<link rel="icon" type="image/x-icon" href="/local/favicon.ico">', true, 'BEFORE_CSS');
/*FONTS*/
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&amp;family=Open+Sans:wght@300;400;700&amp;display=swap" rel="stylesheet">', true, 'BEFORE_CSS');
/*CSS*/
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/css/name_file.min.css", true);
/*JS*/
CJSCore::Init(['fx','bx', 'jquery3']);
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/name_file.min.js");
// Микроразметка
ob_start();
include $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/json.php';
$LdJSONs = ob_get_contents();
ob_end_clean();
?>
<script type="text/javascript" data-skip-moving="true">
    (function(H){H.className=H.className.replace(/\bno-js\b/,'js')})(document.documentElement)
</script>
<title><? $APPLICATION->ShowTitle() ?></title>
