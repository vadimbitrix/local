<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @global CMain $APPLICATION
 */
use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);
global $APPLICATION;

//delayed function must return a string
if(empty($arResult))
	return "";
$strReturn = '<div class="breadcrumbs">';

$strReturn .= '<nav class="breadcrumbs__nav"><ul class="breadcrumbs__list" itemscope itemtype="http://schema.org/BreadcrumbList">';

// link to back page
$strReturn .= '<li class="breadcrumbs__item">
                <a class="breadcrumbs__back" href="javascript:history.back()" id="go-back">
                    <svg class="icon icon-arrow_left">
                        <use xlink:href="'.SITE_TEMPLATE_PATH.'/build_frontend/dist/images/sprite.svg#arrow_left"></use>
                    </svg>
                    <span>'.Loc::getMessage('BACK').'</span>
                </a>
              </li>';

$itemSize = count($arResult);
for($index = 0; $index < $itemSize; $index++)
{
	$title = $arResult[$index]["TITLE"];
    $arrow = '<svg class="icon icon-arrow_right"><use xlink:href="'.SITE_TEMPLATE_PATH.'/build_frontend/dist/images/sprite.svg#arrow_right"></use></svg>';
	if($arResult[$index]["LINK"] <> "" && $index != $itemSize-1)
	{
		$strReturn .= '
			<li class="breadcrumbs__item" id="bx_breadcrumb_'.$index.'" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
				<a href="'.$arResult[$index]["LINK"].'" title="'.$title.'" class="breadcrumbs__link" itemprop="item">
					<span itemprop="name">'.$title.'</span>
				</a>
				'.$arrow.'
				<meta itemprop="position" content="'.($index + 1).'" />
			</li>';
	}
	else
	{
		$strReturn .= '
			<li class="breadcrumbs__item">
				<span>'.$title.'</span>
			</li>';
	}
}

$strReturn .= '</ul></nav></div>';

return $strReturn;
