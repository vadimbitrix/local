<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php if (!empty($arResult)):?>
<!--    <div class="main-menu">-->
<!--        <nav class="main-menu__nav">-->
<!--            <ul class="main-menu__list">-->
<!--                <li class="main-menu__item"><a class="main-menu__link" href="/detail-services.html">Триплирование-->
<!--                        <svg class="icon icon-arrow_down main-menu__arrow">-->
<!--                            <use xlink:href="./images/sprite.svg#arrow_down"></use>-->
<!--                        </svg></a>-->
<!--                    <ul class="main-menu__sub-list">-->
<!--                        <li class="main-menu__sub-item"><a class="main-menu__sub-link" href="/detail-services.html">Триплирование</a></li>-->
<!--                        <li class="main-menu__sub-item"><a class="main-menu__sub-link" href="/detail-services.html">Дублирование</a></li>-->
<!--                    </ul>-->
<!--                </li>-->
<!--                <li class="main-menu__item"><a class="main-menu__link" href="/detail-services.html">Дублирование</a>-->
<!--                </li>-->
<!--                <li class="main-menu__item"><a class="main-menu__link" href="/detail-services.html">Стегание</a>-->
<!--                </li>-->
<!--                <li class="main-menu__item"><a class="main-menu__link" href="/detail-services.html">Склеивание</a>-->
<!--                </li>-->
<!--                <li class="main-menu__item"><a class="main-menu__link" href="/detail-services.html">Доставка и оплата</a>-->
<!--                </li>-->
<!--                <li class="main-menu__item"><a class="main-menu__link" href="/contacts.html">Контакты</a>-->
<!--                </li>-->
<!--            </ul>-->
<!--        </nav>-->
<!--    </div>-->

    <div class="main-menu">
        <nav class="main-menu__nav">
            <ul class="main-menu__list">
                <?php $previousLevel = 0;
                foreach($arResult as $arItem):
                    if ($previousLevel && $arItem["DEPTH_LEVEL"] < $previousLevel){
                        echo str_repeat("</ul></li>", ($previousLevel - $arItem["DEPTH_LEVEL"]));
                    }
                ?>
                <?php if ($arItem["IS_PARENT"]):?>
                    <?php if ($arItem["DEPTH_LEVEL"] == 1):?>
                        <li class="main-menu__item">
                            <a href="<?=$arItem["LINK"]?>" class="main-menu__link"><?=$arItem["TEXT"]?></a>
                            <ul class="main-menu__sub-list">
                    <?php else:?>
                        <li class="main-menu__item">
                            <a href="<?=$arItem["LINK"]?>" class="main-menu__link"><?=$arItem["TEXT"]?></a>
                            <ul class="main-menu__sub-list">
                    <?php endif?>
                <?php else:?>
                    <?php if ($arItem["PERMISSION"] > "D"):?>
                        <?php if ($arItem["DEPTH_LEVEL"] == 1):?>
                            <li class="main-menu__item">
                                <a href="<?=$arItem["LINK"]?>" class="main-menu__link"><?=$arItem["TEXT"]?></a>
                            </li>
                        <?php else:?>
                            <li class="main-menu__sub-item">
                                <a href="<?=$arItem["LINK"]?>" class="main-menu__sub-link"><?=$arItem["TEXT"]?></a>
                            </li>
                        <?php endif?>
                    <?php else:?>
                        <?php if ($arItem["DEPTH_LEVEL"] == 1):?>
                            <li class="main-menu__item">
                                <a href="" class="main-menu__link"><?=$arItem["TEXT"]?></a>
                            </li>
                        <?php else:?>
                            <li class="main-menu__item">
                                <a href="" class="main-menu__link"><?=$arItem["TEXT"]?></a>
                            </li>
                        <?php endif?>
                    <?php endif?>
                <?php endif?>

                <?php $previousLevel = $arItem["DEPTH_LEVEL"];?>
                <?php endforeach?>
                <?php if ($previousLevel > 1)://close last item tags?>
                    <?=str_repeat("</ul></li>", ($previousLevel-1) );?>
                <?php endif?>
            </ul>
        </nav>
    </div>
<?php endif?>
