<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::requireModule('nick.course');

$fieldsList = Nick\Course\Helper\Competence::getFieldsList();

$arComponentParameters = [
    "GROUPS" => [
        "SETTINGS" => [
            "NAME" => Loc::getMessage("SU_SIMPLE_SETTINGS")
        ],
    ],
    "PARAMETERS" => [
        'SU_ELEMENTS_QUANTITY' => [
            'PARENT' => 'SETTINGS',
            'NAME' => Loc::getMessage('SU_ELEMENTS_QUANTITY'),
            'TYPE' => 'STRING',
            'MULTIPLE' => 'N',
            'COLS' => 1
        ],
        'SU_COMPETENCE_TABLE_FILEDS_LIST' => [
            'PARENT' => 'SETTINGS',
            'NAME' => Loc::getMessage('SU_COMPETENCE_TABLE_FILEDS_LIST'),
            'TYPE' => 'LIST',
            'MULTIPLE' => 'Y',
            'ADDITIONAL_VALUES' => 'N',
            'VALUES' => $fieldsList,
            'SIZE' => 10,
        ],
    ]
];
