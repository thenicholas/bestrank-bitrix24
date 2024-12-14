<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Diag\Debug;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if(!Loader::includeModule('study.userrating'))
    return false;

//Debug::dump($arCurrentValues);
$arComponentParameters = [
    "GROUPS" => [
        "SETTINGS" => [
            "NAME" => Loc::getMessage("SU_SIMPLE_SETTINGS")
        ],
        "PARAMS" => [
            "NAME" => Loc::getMessage("SU_SIMPLE_ADD_SETTINGS")
        ],
    ],
    "PARAMETERS" => [
        "SETTINGS_LIST_WITHOUT_ADD_VALUES" => [
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("SU_SIMPLE_SETTINGS_LIST_WITHOUT_ADD_VALUES"),
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "N",
            "VALUES" => [
                "1"=>Loc::getMessage("SU_SIMPLE_SETTINGS_LIST_VALUE1"),
                "2"=>Loc::getMessage("SU_SIMPLE_SETTINGS_LIST_VALUE2"),
            ],
            "REFRESH" => "Y"
        ],
        "SU_SIMPLE_SETTINGS_LIST_WITH_ADD_VALUES" => [
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("SU_SIMPLE_SETTINGS_LIST_WITH_ADD_VALUES"),
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => [
                "1"=>Loc::getMessage("SU_SIMPLE_SETTINGS_LIST_VALUE1"),
                "2"=>Loc::getMessage("SU_SIMPLE_SETTINGS_LIST_VALUE2"),
            ],
        ],
        "SU_SIMPLE_STRING_ALONE" => [
            "PARENT" => "PARAMS",
            "NAME" => Loc::getMessage("SU_SIMPLE_STRING_ALONE"),
            "TYPE" => "STRING",
            "MULTIPLE" => "N",
            "DEFAULT" => Loc::getMessage("SU_SIMPLE_STRING_DEFAULT"),
            "COLS" => 25
        ],
        "SU_SIMPLE_STRING_MULTIPLE" => [
            "PARENT" => "PARAMS",
            "NAME" => Loc::getMessage("SU_SIMPLE_STRING_ALONE"),
            "TYPE" => "STRING",
            "MULTIPLE" => "Y",
            "COLS" => 10
        ],
        "SU_SIMPLE_CHECKBOX" => [
            "PARENT" => "PARAMS",
            "NAME" => Loc::getMessage("SU_SIMPLE_CHECKBOX"),
            "TYPE" => "CHECKBOX",
        ],
        //https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=4880
        /*SU_SIMPLE_CUSTOM" => [
            "PARENT" => "PARAMS",
            "NAME" => Loc::getMessage("SU_SIMPLE_CUSTOM"),
            "TYPE" => "CUSTOM",
        ],*/

        "SU_SIMPLE_FILE" => [
            "PARENT" => "PARAMS",
            "NAME" => Loc::getMessage("SU_SIMPLE_FILE"),
            "TYPE" => "FILE",
        ],
        "SU_SIMPLE_COLORPICKER" => [
            "PARENT" => "PARAMS",
            "NAME" => Loc::getMessage("SU_SIMPLE_COLORPICKER"),
            "TYPE" => "COLORPICKER",
        ],
        "SET_TITLE" => [],
        //"CACHE_TIME" => [],

        //Для комплексных компонентов
        /*"VARIABLE_ALIASES" => [
            "DETAIL" => [
                "NAME" => Loc::getMessage("SU_SIMPLE_DETAIL"),
            ],
            "LIST" => [
                "NAME" => Loc::getMessage("SU_SIMPLE_LIST"),
            ],
        ]  ,
        "SEF_MODE" => [
            "list" => [
                "NAME" => Loc::getMessage("SU_SIMPLE__LIST_PATH_TEMPLATE"),
                "DEFAULT" => "index.php",
                "VARIABLES" => []
            ],
            "section1" => [
                "NAME" => Loc::getMessage("SU_SIMPLE_SECTION_1_PATH_TEMPLATE"),
                "DEFAULT" => "#DETAIL#",
                "VARIABLES" => ["DETAIL"]
            ],

        ],*/
    ]
];

if ($arCurrentValues['SETTINGS_LIST_WITHOUT_ADD_VALUES']==2)
{
    $arComponentParameters['PARAMETERS']['SU_SIMPLE_REFRESH']=[
        "PARENT" => "SETTINGS",
        "NAME" => Loc::getMessage("SU_SIMPLE_REFRESH"),
        "TYPE" => "STRING",
    ];
}
