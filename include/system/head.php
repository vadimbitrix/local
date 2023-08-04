<?
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);
/*META*/
Asset::getInstance()->addString('<meta charset="UTF-8">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, maximum-scale=1, minimum-scale=1" charset="utf-8">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta http-equiv="X-UA-Compatible" content="ie=edge">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="imagetoolbar" content="no">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="application-name" content="">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="mobile-web-app-capable" content="yes">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="application-name" content="">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="apple-mobile-web-app-capable" content="yes">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="apple-mobile-web-app-title" content="">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="msthemecompatible" content="no">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="cleartype" content="no">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="HandheldFriendly" content="True">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="format-detection" content="telephone=no">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="format-detection" content="address=no">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="google" content="notranslate">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="msapplication-TileColor" content="#10cfe6">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="msapplication-TileImage" content="/favicons/mstile-144x144.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<meta name="msapplication-config" content="/favicons/browserconfig.xml">', true, 'BEFORE_CSS');
/*FAVICON*/
Asset::getInstance()->addString('<link rel="shortcut icon" href="/favicons/favicon.ico">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="manifest" href="/favicons/manifest.json">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="16x16" href="/favicons/apple-touch-icon-16x16.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="32x32" href="/favicons/apple-touch-icon-32x32.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="48x48" href="/favicons/apple-touch-icon-48x48.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="57x57" href="/favicons/apple-touch-icon-57x57.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="60x60" href="/favicons/apple-touch-icon-60x60.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="72x72" href="/favicons/apple-touch-icon-72x72.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="76x76" href="/favicons/apple-touch-icon-76x76.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="114x114" href="/favicons/apple-touch-icon-114x114.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="120x120" href="/favicons/apple-touch-icon-120x120.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="144x144" href="/favicons/apple-touch-icon-144x144.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="152x152" href="/favicons/apple-touch-icon-152x152.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="167x167" href="/favicons/apple-touch-icon-167x167.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon-180x180.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="1024x1024" href="/favicons/apple-touch-icon-1024x1024.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="yandex-tableau-widget" href="/favicons/yandex-browser-manifest.json">', true, 'BEFORE_CSS');
/*FONTS*/
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>', true, 'BEFORE_CSS');
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
