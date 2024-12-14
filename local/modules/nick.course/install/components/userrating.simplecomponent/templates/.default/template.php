<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main\Page\Asset;
//\CJSCore::Init("lib_name")
// \Bitrix\Main\UI\Extension::load("folder_name.lib_name");
//Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/test.js");
//Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/test.css");
//Asset::getInstance()->addString("<link href='http://fonts.googleapis.com/css?family=PT+Sans:400&subset=cyrillic' rel='stylesheet' type='text/css'>");
//$this->addExternalCss("/local/styles.css");
$this->addExternalJS("/local/liba.js");
?>
<div class="my-block">
<?php \Bitrix\Main\Diag\Debug::dump($arResult,'$arResult-template.php');?>
</div>