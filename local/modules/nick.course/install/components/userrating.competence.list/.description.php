<?php
use Bitrix\Main\Localization\Loc;
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
    "NAME" => Loc::getMessage("SU_SIMPLE_GRID_NAME"),
    "DESCRIPTION" => Loc::getMessage("SU_SIMPLE_GRID_DESC"),
    "ICON" => "/images/icon.gif",
    'SORT'=>10,
    "PATH" => array(
        "ID" => "STUDY",
        'NAME'=> 'Оценка 360',
        "CHILD" => array(
            "ID" => "simple",
            "NAME" => Loc::getMessage("SU_SIMPLE_SIMPL")
        )
    ),
    "CACHE_PATH" => "Y",
    //"COMPLEX" => "Y"
);