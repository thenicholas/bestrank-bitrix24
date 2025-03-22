<?php

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Types;
use Bitrix\Main\Page\Asset;

/** @var array $arResult */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->__file = '/bitrix/components/bitrix/lists.list/templates/.default/template.php';
$this->__folder = '/bitrix/components/bitrix/lists.list/templates/.default';
$asset = Asset::getInstance();
$asset->addJs($this->__folder . '/script.js');
$asset->addCss($this->__folder . '/style.css');
include_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/bitrix/lists.list.default/templates/.default/result_modifier.php';

Bitrix\Main\UI\Extension::load(['ui.tooltip', 'nick_course.grid_custom']);

if (isset($arParams['IBLOCK_ID_USER_COMPETENCE'])
    && $arParams['IBLOCK_ID_USER_COMPETENCE'] != 0
    && $arResult['GRID_ID'] == 'lists_list_elements_' . $arParams['IBLOCK_ID_USER_COMPETENCE']
) {
    $arResult['GRID_ACTION_PANEL']['GROUPS'][0]['ITEMS'][] = [
        'ID' => 'set-type',
        'TYPE' => Types::DROPDOWN,
        'ITEMS' => [
            [
                'VALUE' => '',
                'NAME' => '- Выбрать -'
            ],
            [
                'VALUE' => 'nextCompetence',
                'NAME' => 'Указать след. компетенцию',
                'ONCHANGE' => [
                    [
                        'ACTION' => Actions::CREATE,
                        'DATA' => [
                            [
                                'TYPE' => Types::TEXT,
                                'ID' => 'nextCompetenceValueId',
                                'NAME' => 'nextCompetence',
                                'VALUE' => '',
                                'SIZE' => 1,
                            ],
                            [
                                'TYPE' => Types::BUTTON,
                                'TEXT' => 'Применить',
                                'ID' => 'nextCompetenceValueButtonId',
                                'NAME' => 'nextCompetenceValueButtonName',
                                'ONCHANGE' => [
                                    [
                                        'ACTION' => Actions::CALLBACK,
                                        'DATA' => [
                                            ['JS' => "UserRatingCompetenceChange('setCompetence', '" . $arResult['GRID_ID'] . "');"]
                                        ]
                                    ]
                                ]
                            ],
                        ],
                    ],
                ],
            ]
        ]
    ];
}

