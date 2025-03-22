<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Компонент-обертка");
use Bitrix\UI\Buttons;
$linkButton = new Buttons\CreateButton([
    "link" => 'path',
]);
?>

<?php
$APPLICATION->IncludeComponent(
    'bitrix:ui.sidepanel.wrapper',
    '',
    [
        //Название компонента
        'POPUP_COMPONENT_NAME' => 'study:userrating.simplecomponent',
        //Шаблон компонента
        'POPUP_COMPONENT_TEMPLATE_NAME' => '',
        //параметры компонента
        'POPUP_COMPONENT_PARAMS' =>[],

        //Использовать темы
        'POPUP_COMPONENT_USE_BITRIX24_THEME'=>'Y',
        'DEFAULT_THEME_ID' => 'light:mail',
        'THEME_ID' => 'light:robots',

        //Отсупы в контентной области
        'USE_PADDING'=>false,

        //Отключение фона контентной области
        //'USE_BACKGROUND_CONTENT'=>false,

        //Вывод без заголовка
        //'PLAIN_VIEW'=>true,

        //Открытие детальной страницы всегда в слайдере
        /*'PAGE_MODE'=>true,
        'PAGE_MODE_OFF_BACK_URL'=>'/slayder-primery/slayder-metod-open.php'*/

        //Кнопки
        //https://dev.1c-bitrix.ru/api_d7/bitrix/ui/button_panel/parameters.php
        'BUTTONS'=>[
            'close',
            'apply',
            [
                'TYPE' => 'save', // тип - обязательный
                'CAPTION' => 'Сохранить', // название - не обязательный
                'NAME' => 'save', // атрибут `name` инпута - не обязательный
                'ID' => 'my-save-id', // атрибут `id` инпута - не обязательный
                'VALUE' => 'Y', // атрибут `value` инпута - не обязательный
                'ONCLICK' => 'alert();', // атрибут `onclick` инпута - не обязательный
            ]
        ],
        //'CLOSE_AFTER_SAVE'=>'Y'
        //'RELOAD_GRID_AFTER_SAVE' => 'Y',
        //'RELOAD_PAGE_AFTER_SAVE' => 'Y',
        //''NOTIFICATION' => 'Успешно сохранено!'

        //Наличие тулбара
        //'USE_UI_TOOLBAR' => 'Y',

    ]
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>