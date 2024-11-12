<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Localization\Loc;

$arItems = [];

$arItems = [
    [
        'parent_menu' => 'global_menu_nick_course',
        'sort' => 100,
        'text' => Loc::getMessage("NI_CO_MENU_MAIN_SETTINGS_TEXT"),
        'url' => 'nick_course_options.php?lang=' . LANG,
        'items_id' => 'nick_course_listelementdetail',
    ],
    [
        'parent_menu' => 'global_menu_nick_course',
        'sort' => 200,
        'text' => Loc::getMessage('NI_CO_MENU_MAIN_GRADES_TEXT'),
        'url' => 'nick_course_grades.php?lang=' . LANG,
        'items_id' => 'nick_course_grades',
    ]
];

return $arItems;
