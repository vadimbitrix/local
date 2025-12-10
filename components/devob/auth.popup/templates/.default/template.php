<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @var CBitrixComponentTemplate $this */

$this->setFrameMode(true);
?>

<?php if (!$arResult['IS_AUTHORIZED']): ?>
    <div class="devob-auth-triggers">
        <button onclick="window.devobAuthPopup?.open('login')" class="btn btn-primary">
            Войти
        </button>
        <button onclick="window.devobAuthPopup?.open('register')" class="btn btn-outline-primary">
            Регистрация
        </button>
    </div>
<?php else: ?>
    <div class="devob-auth-triggers">
        <button onclick="window.devobAuthPopup?.open('login')" class="btn btn-primary">
            Войти
        </button>
        <button onclick="window.devobAuthPopup?.open('register')" class="btn btn-outline-primary">
            Регистрация
        </button>
    </div>
    <div class="devob-auth-user">
        <span>Добро пожаловать, <?= htmlspecialchars($USER->GetFullName() ?: $USER->GetLogin()) ?>!</span>
        <a href="?logout=Y" class="btn btn-sm btn-outline-secondary">Выйти</a>
    </div>
<?php endif; ?>

<!-- Vue контейнер -->
<div id="<?= $arResult['COMPONENT_ID'] ?>"></div>

<script>
    // Глобальные настройки для компонента
    window.devobAuthConfig = {
        componentId: '<?= CUtil::JSEscape($arResult['COMPONENT_ID']) ?>',
        captchaKey: '<?= CUtil::JSEscape($arResult['CAPTCHA_KEY']) ?>',
        isAuthorized: <?= $arResult['IS_AUTHORIZED'] ? 'true' : 'false' ?>,
        ajaxUrl: '<?= $this->getFolder() ?>/ajax.php'
    };
</script>
