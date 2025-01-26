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
        'GRID_ID' => $arResult['GRID_ID'],
        'COLUMNS' => $arResult['COLUMNS'],
        'ROWS' => $arResult['ROWS'],
        'AJAX_MODE' => 'Y',
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_HISTORY' => 'N',

        'STUB' => $arResult['STUB'],
        //'ALLOW_STICKED_COLUMN'=>true,
        //'ALLOW_PIN_HEADER'=>true,
        //Нижняя панель
        'SHOW_NAVIGATION_PANEL' => $arResult['SHOW_NAVIGATION_PANEL'],
        'NAV_OBJECT' => $arResult['NAV_OBJECT'],
        'CURRENT_PAGE' => $arResult['CURRENT_PAGE'],
        'ENABLE_NEXT_PAGE' => $arResult['ENABLE_NEXT_PAGE'],
        'SHOW_MORE_BUTTON' => $arResult['SHOW_MORE_BUTTON'],
        'TOTAL_ROWS_COUNT' => $arResult['TOTAL_ROWS_COUNT'],
        'TOTAL_ROWS_COUNT_HTML' => 'Кол-во компетенций - ' . $arResult['TOTAL_ROWS_COUNT'],
        'PAGE_SIZES' => $arResult['PAGE_SIZES'],
        'SHOW_PAGESIZE' => $arResult['SHOW_PAGESIZE'],
        //Групповые действия
        'SHOW_ROW_CHECKBOXES' => $arResult['SHOW_ROW_CHECKBOXES'],
        'SHOW_SELECTED_COUNTER' => $arResult['SHOW_SELECTED_COUNTER'],
        'SHOW_ACTION_PANEL' => $arResult['SHOW_ACTION_PANEL'],
        'ACTION_PANEL' => $arResult['ACTION_PANEL'],
        'HANDLE_RESPONSE_ERRORS' => true,
    ],
    $component,
    ["HIDE_ICONS" => "Y"]
);
?>



