<?php
use Bitrix\Main\Localization\Loc;
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
    "NAME" => Loc::getMessage("SU_SIMPLE_NAME"),
    "DESCRIPTION" => Loc::getMessage("SU_SIMPLE_DESCR"),
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
    "AREA_BUTTONS" => array(
        array(
            'URL' => "javascript:alert('Это кнопка!!!');",
            'SRC' => '/images/button.jpg',
            'TITLE' => Loc::getMessage("SU_SIMPLE_BUTTON")
        ),
    ),
    "CACHE_PATH" => "Y",
    //"COMPLEX" => "Y"
);