<?php

use Bitrix\Main\Context;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

global $APPLICATION;
$APPLICATION->SetTitle('Компетенция');

$entityId = Context::getCurrent()->getRequest()->get('user_grade_id');

$APPLICATION->IncludeComponent(
    'bitrix:ui.sidepanel.wrapper',
    '',
    [
        //Название компонента
        'POPUP_COMPONENT_NAME' => 'nick.course:user.grade.detail',
        //Шаблон компонента
        'POPUP_COMPONENT_TEMPLATE_NAME' => '',
        //параметры компонента
        'POPUP_COMPONENT_PARAMS' =>[
            'ENTITY_ID' => $entityId
        ],
    ]
);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
