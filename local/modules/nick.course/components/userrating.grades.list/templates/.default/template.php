<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
Bitrix\Main\UI\Extension::load("ui.tooltip");
/** @var array $arResult */
/** @var array $arParams */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */

$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        "GRID_ID" => $arResult['GRID_ID'],
        "COLUMNS" => $arResult["COLUMNS"],
        "ROWS" => $arResult['ROWS'],
        "AJAX_MODE" => "Y",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_HISTORY" => "N",
        //'ALLOW_STICKED_COLUMN'=>true,
        //'ALLOW_PIN_HEADER'=>true,
        //"PAGE_SIZE" => $arResult['PAGE_SIZE'],
        //"SHOW_PAGESIZE"=>true
    ],
    $component,
    ["HIDE_ICONS" => "Y"]
);
?>



