<?php

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Json;
use Nick\Course\Helper\Options;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** @var array $arParams */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */

$data = [
    'width' => (int)Options::getParam('SLIDER_WIDTH', 1000),
    'cacheable' => Options::getParam('SLIDER_CACHEABLE', false) === 'Y',
    'allowChangeHistory' => Options::getParam('SLIDER_ALLOW_CHANGE_HISTORY', false) === 'Y'
];

Asset::getInstance()->addString(
    '<script>
        BX.ready(function() {
            BX.namespace("NickCourse.Slider");
            BX.NickCourse.Slider.data = ' . Json::encode($data) . ';
        });
    </script>'
);

$APPLICATION->IncludeComponent(
    "bitrix:lists.list",
    ".default",
    $arResult['LIST'],
    $component
);
