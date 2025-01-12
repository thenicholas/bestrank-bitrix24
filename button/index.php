<?php

global $APPLICATION;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Кнопка");

$APPLICATION->IncludeComponent(
    'nick.course:simplecomponentajax',
    ''
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
