<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$this->setFrameMode(true);

$componentPage = $arResult['COMPONENT_PAGE'] ?? 'index';

if ($componentPage === 'auth') {
    ?>
    <div class="personal personal--auth">
        <div class="personal__auth-message">
            <p><?= Loc::getMessage('DEVOB_PERSONAL_AUTH_REQUIRED'); ?></p>
            <?php if (!empty($arResult['AUTH_URL'])): ?>
                <a class="personal__auth-link" href="<?= htmlspecialcharsbx($arResult['AUTH_URL']); ?>">
                    <?= Loc::getMessage('DEVOB_PERSONAL_AUTH_LINK'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return;
}

$user = $arResult['USER'];
$navigation = $arResult['NAVIGATION'] ?? [];
$profile = $arResult['PROFILE'] ?? [];
$edit = $arResult['EDIT'] ?? [];
$orders = $arResult['ORDERS'] ?? ['ACTIVE' => [], 'ARCHIVE' => []];
$noPhotoImage = '/upload/no-photo-white.png';
$favoritesComponent = $arResult['FAVORITES_COMPONENT'] ?? null;

?>
<div class="personal personal--page-<?= htmlspecialcharsbx($componentPage); ?>">
    <aside class="personal__sidebar">
        <div class="personal__user-card">
            <div class="personal__avatar">
                <?php if (!empty($user['PHOTO_SRC'])): ?>
                    <img src="<?= htmlspecialcharsbx($user['PHOTO_SRC']); ?>" alt="<?= htmlspecialcharsbx($user['FULL_NAME']); ?>">
                <?php else: ?>
                    <span><?= htmlspecialcharsbx($user['INITIALS']); ?></span>
                <?php endif; ?>
            </div>
            <div class="personal__user-info">
                <div class="personal__user-header">
                    <div>
                        <div class="personal__user-name"><?= htmlspecialcharsbx($user['FULL_NAME']); ?></div>
                    </div>
                    <a class="personal__user-edit" href="<?= htmlspecialcharsbx($profile['EDIT_URL'] ?? '#'); ?>">
                        <?= Loc::getMessage('DEVOB_PERSONAL_EDIT_LINK'); ?>
                    </a>
                </div>
                <ul class="personal__user-contacts">
                    <?php if (!empty($user['EMAIL'])): ?>
                        <li><span><?= Loc::getMessage('DEVOB_PERSONAL_FIELD_EMAIL'); ?>:</span> <?= htmlspecialcharsbx($user['EMAIL']); ?></li>
                    <?php endif; ?>
                    <?php if (!empty($user['PHONE_NUMBER'])): ?>
                        <li><span><?= Loc::getMessage('DEVOB_PERSONAL_FIELD_PHONE'); ?>:</span> <?= htmlspecialcharsbx($user['PHONE_NUMBER']); ?></li>
                    <?php endif; ?>
                </ul>
                <div class="personal__user-actions">
                    <a class="personal__user-logout" href="<?= htmlspecialcharsbx($profile['LOGOUT_URL'] ?? '#'); ?>">
                        <?= Loc::getMessage('DEVOB_PERSONAL_LOGOUT'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php if ($navigation): ?>
            <nav class="personal__nav">
                <ul>
                    <?php foreach ($navigation as $item): ?>
                        <li class="<?= $item['is_active'] ? 'is-active' : ''; ?>">
                            <a href="<?= htmlspecialcharsbx($item['url']); ?>"><?= htmlspecialcharsbx($item['title']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>
        <div class="personal__promo">
            <div class="personal__promo-title">Telegram</div>
            <div class="personal__promo-text"><?= Loc::getMessage('DEVOB_PERSONAL_TELEGRAM_TEXT'); ?></div>
            <a class="personal__promo-button" href="https://t.me/" target="_blank" rel="noopener"><?= Loc::getMessage('DEVOB_PERSONAL_TELEGRAM_BUTTON'); ?></a>
        </div>
    </aside>
    <div class="personal__content">
        <?php if ($componentPage === 'index'): ?>
            <div class="personal-profile">
                <h1 class="personal__title"><?= Loc::getMessage('DEVOB_PERSONAL_TITLE_PROFILE'); ?></h1>
                <div class="personal-profile__grid">
                    <div class="personal-profile__card">
                        <div class="personal-profile__card-title"><?= Loc::getMessage('DEVOB_PERSONAL_PROFILE_NAME'); ?></div>
                        <div class="personal-profile__card-value"><?= htmlspecialcharsbx($user['FULL_NAME']); ?></div>
                    </div>
                    <div class="personal-profile__card">
                        <div class="personal-profile__card-title"><?= Loc::getMessage('DEVOB_PERSONAL_FIELD_EMAIL'); ?></div>
                        <div class="personal-profile__card-value"><?= htmlspecialcharsbx($user['EMAIL']); ?></div>
                    </div>
                    <div class="personal-profile__card">
                        <div class="personal-profile__card-title"><?= Loc::getMessage('DEVOB_PERSONAL_FIELD_PHONE'); ?></div>
                        <div class="personal-profile__card-value"><?= htmlspecialcharsbx($user['PHONE_NUMBER']); ?></div>
                    </div>
                </div>
                <div class="personal-profile__actions">
                    <a class="personal-button" href="<?= htmlspecialcharsbx($profile['ORDERS_URL'] ?? '#'); ?>">
                        <?= Loc::getMessage('DEVOB_PERSONAL_GO_ORDERS'); ?>
                    </a>
                    <a class="personal-button personal-button--secondary" href="<?= htmlspecialcharsbx($profile['FAVORITES_URL'] ?? '#'); ?>">
                        <?= Loc::getMessage('DEVOB_PERSONAL_GO_FAVORITES'); ?>
                    </a>
                </div>
            </div>
        <?php elseif ($componentPage === 'edit'): ?>
            <div class="personal-edit">
                <h1 class="personal__title"><?= Loc::getMessage('DEVOB_PERSONAL_TITLE_EDIT'); ?></h1>
                <?php if (!empty($edit['SUCCESS'])): ?>
                    <div class="personal-message personal-message--success"><?= Loc::getMessage('DEVOB_PERSONAL_SAVE_SUCCESS'); ?></div>
                <?php endif; ?>
                <?php if (!empty($edit['ERRORS'])): ?>
                    <div class="personal-message personal-message--error">
                        <?php foreach ($edit['ERRORS'] as $error): ?>
                            <div><?= htmlspecialcharsbx($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form class="personal-form" method="post">
                    <?= bitrix_sessid_post(); ?>
                    <div class="personal-form__row">
                        <label>
                            <span><?= Loc::getMessage('DEVOB_PERSONAL_FIELD_LAST_NAME'); ?></span>
                            <input type="text" name="LAST_NAME" value="<?= htmlspecialcharsbx($edit['VALUES']['LAST_NAME'] ?? ''); ?>">
                        </label>
                    </div>
                    <div class="personal-form__row">
                        <label>
                            <span><?= Loc::getMessage('DEVOB_PERSONAL_FIELD_NAME'); ?></span>
                            <input type="text" name="NAME" value="<?= htmlspecialcharsbx($edit['VALUES']['NAME'] ?? ''); ?>">
                        </label>
                    </div>
                    <div class="personal-form__row">
                        <label>
                            <span><?= Loc::getMessage('DEVOB_PERSONAL_FIELD_SECOND_NAME'); ?></span>
                            <input type="text" name="SECOND_NAME" value="<?= htmlspecialcharsbx($edit['VALUES']['SECOND_NAME'] ?? ''); ?>">
                        </label>
                    </div>
                    <div class="personal-form__row">
                        <label>
                            <span><?= Loc::getMessage('DEVOB_PERSONAL_FIELD_EMAIL'); ?></span>
                            <input type="email" name="EMAIL" value="<?= htmlspecialcharsbx($edit['VALUES']['EMAIL'] ?? ''); ?>">
                        </label>
                    </div>
                    <div class="personal-form__row">
                        <label>
                            <span><?= Loc::getMessage('DEVOB_PERSONAL_FIELD_PHONE'); ?></span>
                            <input type="text" value="<?= htmlspecialcharsbx($edit['VALUES']['PHONE_NUMBER'] ?? ''); ?>" disabled>
                        </label>
                    </div>
                    <div class="personal-form__actions">
                        <button class="personal-button" type="submit" name="save" value="Y">
                            <?= Loc::getMessage('DEVOB_PERSONAL_SAVE_BUTTON'); ?>
                        </button>
                        <a class="personal-button personal-button--secondary" href="<?= htmlspecialcharsbx($edit['BACK_URL'] ?? '#'); ?>">
                            <?= Loc::getMessage('DEVOB_PERSONAL_CANCEL_BUTTON'); ?>
                        </a>
                    </div>
                </form>
            </div>
        <?php elseif ($componentPage === 'orders'): ?>
            <div class="personal-orders">
                <h1 class="personal__title"><?= Loc::getMessage('DEVOB_PERSONAL_TITLE_ORDERS'); ?></h1>
                <?php
                $renderOrderItem = static function (array $order, bool $asAccordion = false, int $index = 0) use ($noPhotoImage): void {
                    $itemClasses = ['personal-orders__item'];

                    if ($asAccordion) {
                        $itemClasses[] = 'personal-orders__item--accordion';
                    }

                    $itemClassAttribute = implode(' ', $itemClasses);

                    $domIdSource = (string)($order['ID'] ?? $order['ACCOUNT_NUMBER'] ?? $index);
                    $domIdPart = preg_replace('/[^a-zA-Z0-9_-]/', '', $domIdSource) ?: (string)$index;

                    $accordionTriggerId = $asAccordion ? 'personal-orders-accordion-trigger-' . $domIdPart . '-' . $index : '';
                    $accordionContentId = $asAccordion ? 'personal-orders-accordion-content-' . $domIdPart . '-' . $index : '';
                    ?>
                    <li class="<?= $itemClassAttribute; ?>"<?= $asAccordion ? ' data-personal-order-item' : ''; ?>>
                        <?php if ($asAccordion): ?>
                            <button class="personal-orders__accordion-trigger" type="button" aria-expanded="<?= $index === 0 ? 'true' : 'false'; ?>" aria-controls="<?= htmlspecialcharsbx($accordionContentId); ?>" id="<?= htmlspecialcharsbx($accordionTriggerId); ?>">
                        <?php endif; ?>
                            <div class="personal-orders__item-head">
                                <div class="personal-orders__item-title">
                                    <div class="personal-orders__number"><?= Loc::getMessage('DEVOB_PERSONAL_ORDER_NUMBER_TITLE'); ?> <?= htmlspecialcharsbx($order['ACCOUNT_NUMBER']); ?></div>
                                    <div class="personal-orders__date"><?= Loc::getMessage('DEVOB_PERSONAL_ORDER_DATE'); ?>: <?= htmlspecialcharsbx($order['DATE_FORMATTED']); ?></div>
                                </div>
                                <div class="personal-orders__item-actions">
                                    <div class="personal-orders__status personal-orders__status--<?= htmlspecialcharsbx($order['STATUS_CLASS']); ?>">
                                        <?= htmlspecialcharsbx($order['STATUS_NAME']); ?>
                                    </div>
                                    <?php if ($asAccordion): ?>
                                        <span class="personal-orders__accordion-icon" aria-hidden="true"></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php if ($asAccordion): ?>
                            </button>
                        <?php endif; ?>
                        <div class="personal-orders__item-content"<?php if ($asAccordion): ?> id="<?= htmlspecialcharsbx($accordionContentId); ?>" role="region" aria-labelledby="<?= htmlspecialcharsbx($accordionTriggerId); ?>"<?php endif; ?>>
                            <div class="personal-orders__item-body">
                                <div class="personal-orders__meta">
                                    <div class="personal-orders__meta-item">
                                        <span class="personal-orders__meta-title"><?= Loc::getMessage('DEVOB_PERSONAL_ORDER_TOTAL'); ?></span>
                                        <span class="personal-orders__meta-value"><?= $order['PRICE_FORMATTED']; ?></span>
                                    </div>
                                    <div class="personal-orders__meta-item">
                                        <span class="personal-orders__meta-title"><?= Loc::getMessage('DEVOB_PERSONAL_ORDER_PAYMENT_STATUS'); ?></span>
                                        <span class="personal-orders__badge personal-orders__badge--<?= htmlspecialcharsbx($order['PAYMENT_STATUS_CLASS']); ?>">
                                            <?= htmlspecialcharsbx($order['PAYMENT_STATUS_TEXT']); ?>
                                        </span>
                                    </div>
                                    <div class="personal-orders__meta-item">
                                        <span class="personal-orders__meta-title"><?= Loc::getMessage('DEVOB_PERSONAL_ORDER_PAYMENT_METHOD'); ?></span>
                                        <span class="personal-orders__meta-value">
                                            <?= htmlspecialcharsbx($order['PAYMENT_SUMMARY'] ?: Loc::getMessage('DEVOB_PERSONAL_ORDER_EMPTY_PAYMENT')); ?>
                                        </span>
                                    </div>
                                    <div class="personal-orders__meta-item">
                                        <span class="personal-orders__meta-title"><?= Loc::getMessage('DEVOB_PERSONAL_ORDER_DELIVERY_METHOD'); ?></span>
                                        <span class="personal-orders__meta-value">
                                            <?= htmlspecialcharsbx($order['DELIVERY_SUMMARY'] ?: Loc::getMessage('DEVOB_PERSONAL_ORDER_EMPTY_DELIVERY')); ?>
                                        </span>
                                    </div>
                                    <div class="personal-orders__meta-item">
                                        <span class="personal-orders__meta-title"><?= Loc::getMessage('DEVOB_PERSONAL_ORDER_DELIVERY_PRICE'); ?></span>
                                        <span class="personal-orders__meta-value"><?= $order['DELIVERY_PRICE_FORMATTED']; ?></span>
                                    </div>
                                </div>
                                <?php if (!empty($order['PAYMENTS'])): ?>
                                    <div class="personal-orders__payments">
                                        <?php foreach ($order['PAYMENTS'] as $payment): ?>
                                            <div class="personal-orders__payment">
                                                <div class="personal-orders__payment-name"><?= htmlspecialcharsbx($payment['NAME']); ?></div>
                                                <div class="personal-orders__payment-sum"><?= $payment['SUM_FORMATTED']; ?></div>
                                                <div class="personal-orders__payment-status personal-orders__badge personal-orders__badge--<?= $payment['IS_PAID'] ? 'success' : 'process'; ?>">
                                                    <?= htmlspecialcharsbx($payment['IS_PAID'] ? Loc::getMessage('DEVOB_PERSONAL_ORDER_PAYMENT_STATUS_PAID') : Loc::getMessage('DEVOB_PERSONAL_ORDER_PAYMENT_STATUS_WAIT')); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($order['ITEMS'])): ?>
                                    <div class="personal-orders__products-wrap">
                                        <div class="personal-orders__block-title"><?= Loc::getMessage('DEVOB_PERSONAL_ORDER_PRODUCTS'); ?></div>
                                        <ul class="personal-orders__products">
                                            <?php foreach ($order['ITEMS'] as $item): ?>
                                                <li class="personal-orders__product">
                                                    <a class="personal-orders__product-image" href="<?= htmlspecialcharsbx($item['URL'] ?: '#'); ?>">
                                                        <?php if (!empty($item['IMAGE'])): ?>
                                                            <img src="<?= htmlspecialcharsbx($item['IMAGE']); ?>" alt="<?= htmlspecialcharsbx($item['NAME']); ?>">
                                                        <?php else: ?>
                                                            <img src="<?= htmlspecialcharsbx($noPhotoImage); ?>" alt="<?= Loc::getMessage('DEVOB_PERSONAL_ORDER_NO_IMAGE'); ?>">
                                                        <?php endif; ?>
                                                    </a>
                                                    <div class="personal-orders__product-main">
                                                        <a class="personal-orders__product-name" href="<?= htmlspecialcharsbx($item['URL'] ?: '#'); ?>">
                                                            <?= htmlspecialcharsbx($item['NAME']); ?>
                                                        </a>
                                                        <div class="personal-orders__product-meta">
                                                            <span><?= htmlspecialcharsbx($item['QUANTITY_FORMATTED']); ?> <?= htmlspecialcharsbx($item['MEASURE']); ?></span>
                                                            <span><?= $item['PRICE_FORMATTED']; ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="personal-orders__product-total"><?= $item['SUM_FORMATTED']; ?></div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($order['COMMENT'])): ?>
                                    <div class="personal-orders__comment">
                                        <div class="personal-orders__block-title"><?= Loc::getMessage('DEVOB_PERSONAL_ORDER_COMMENT'); ?></div>
                                        <div class="personal-orders__comment-text"><?= nl2br(htmlspecialcharsbx($order['COMMENT'])); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($order['PROPERTIES'])): ?>
                                    <div class="personal-orders__properties">
                                        <div class="personal-orders__block-title"><?= Loc::getMessage('DEVOB_PERSONAL_ORDER_PROPERTIES'); ?></div>
                                        <div class="personal-orders__properties-list">
                                            <?php foreach ($order['PROPERTIES'] as $property): ?>
                                                <div class="personal-orders__property">
                                                    <span class="personal-orders__property-name"><?= htmlspecialcharsbx($property['NAME']); ?></span>
                                                    <span class="personal-orders__property-value"><?= htmlspecialcharsbx($property['VALUE']); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                    <?php
                };

                $sections = [
                    'ACTIVE' => [
                        'TITLE' => Loc::getMessage('DEVOB_PERSONAL_ORDERS_ACTIVE'),
                        'EMPTY_MESSAGE' => Loc::getMessage('DEVOB_PERSONAL_ORDERS_EMPTY'),
                        'EMPTY_LINK' => [
                            'TEXT' => Loc::getMessage('DEVOB_PERSONAL_GO_SHOP'),
                            'URL' => Loc::getMessage('DEVOB_PERSONAL_CATALOG_URL'),
                        ],
                    ],
                    'ARCHIVE' => [
                        'TITLE' => Loc::getMessage('DEVOB_PERSONAL_ORDERS_ARCHIVE'),
                        'EMPTY_MESSAGE' => Loc::getMessage('DEVOB_PERSONAL_ORDERS_ARCHIVE_EMPTY'),
                        'EMPTY_LINK' => null,
                    ],
                ];

                foreach ($sections as $type => $section):
                    ?>
                    <div class="personal-orders__section">
                        <h2><?= htmlspecialcharsbx($section['TITLE']); ?></h2>
                        <?php if (!empty($orders[$type])): ?>
                            <?php $orderList = array_values($orders[$type]); ?>
                            <ul class="personal-orders__list<?= $type === 'ARCHIVE' ? ' personal-orders__list--archive' : ''; ?>"<?= $type === 'ARCHIVE' ? ' data-personal-orders-archive' : ''; ?>>
                                <?php foreach ($orderList as $orderIndex => $order): ?>
                                    <?php $renderOrderItem($order, $type === 'ARCHIVE', (int)$orderIndex); ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="personal-orders__empty">
                                <p><?= htmlspecialcharsbx($section['EMPTY_MESSAGE']); ?></p>
                                <?php if (!empty($section['EMPTY_LINK'])): ?>
                                    <a class="personal-button" href="<?= htmlspecialcharsbx($section['EMPTY_LINK']['URL']); ?>">
                                        <?= htmlspecialcharsbx($section['EMPTY_LINK']['TEXT']); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                endforeach;
                ?>
            </div>
        <?php elseif ($componentPage === 'favorites'): ?>
            <div class="personal-favorites">
                <h1 class="personal__title"><?= Loc::getMessage('DEVOB_PERSONAL_TITLE_FAVORITES'); ?></h1>
                <?php if ($favoritesComponent): ?>
                    <?php
                    global $APPLICATION;
                    $APPLICATION->IncludeComponent(
                        $favoritesComponent['NAME'],
                        $favoritesComponent['TEMPLATE'],
                        $favoritesComponent['PARAMS'],
                        false,
                        ['HIDE_ICONS' => 'Y']
                    );
                    ?>
                <?php else: ?>
                    <div class="personal-favorites__empty">
                        <p><?= Loc::getMessage('DEVOB_PERSONAL_FAVORITES_EMPTY'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
