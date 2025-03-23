<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** @var array $arParams */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @var CBitrixComponentTemplate $this */

/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;


$APPLICATION->IncludeComponent(
    'bitrix:ui.form',
    '',
    $arResult['FORM'],
);
?>
