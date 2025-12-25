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
Asset::getInstance()->addString('<link rel="shortcut icon" sizes="96x96" href="/favicons/favicon-96x96.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="shortcut icon" sizes="192x192" href="/favicons/web-app-manifest-192x192.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="shortcut icon" sizes="512x512" href="/favicons/web-app-manifest-512x512.png">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="shortcut icon" href="/favicons/favicon.svg">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="manifest" href="/favicons/webmanifest.json">', true, 'BEFORE_CSS');
Asset::getInstance()->addString('<link rel="apple-touch-icon" sizes="16x16" href="/favicons/apple-touch-icon.png">', true, 'BEFORE_CSS');
/*CSS*/
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/libs/swiper/swiper-bundle.min.css", true);
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/libs/fancybox/fancybox.min.css", true);
/*JS*/
CJSCore::Init(['fx','bx', 'jquery3']);
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/libs/vue/vue3.5.21.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/phone-mask.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/libs/fancybox/fancybox.min.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/libs/swiper/swiper-bundle.min.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/libs/pinia/pinia.prod.min.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/init.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/popup.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/cookie-notification.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/info-accordion.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/select-city.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/stores/cart.js");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/stores/favorites.js");
// Микроразметка
ob_start();
include $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/json.php';
$LdJSONs = ob_get_contents();
ob_end_clean();
?>
<?php if ($APPLICATION->GetCurPage() == "/"):?>
    <script type="application/ld+json">
        {
            "@context": "http://schema.org/",
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Сайт компании",
                    "item": "https://site.ru"
                }
            ]
        }
    </script>
<?php endif;?>
<script type="text/javascript" data-skip-moving="true">
    (function(H){H.className=H.className.replace(/\bno-js\b/,'js')})(document.documentElement)
</script>
<title><? $APPLICATION->ShowTitle() ?></title>
