<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Компонент");
?>

<?php $APPLICATION->IncludeComponent('nick.course:userrating.simplecomponent',
'')?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
