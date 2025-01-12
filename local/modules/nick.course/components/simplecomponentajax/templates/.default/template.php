<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Page\Asset;

\Bitrix\Main\UI\Extension::load('ui.icons.b24');
\Bitrix\Main\UI\Extension::load('ui.buttons.icons');
Bitrix\Main\UI\Extension::load('ui.tooltip');
\Bitrix\Main\UI\Extension::load('ui.forms');
\Bitrix\Main\UI\Extension::load('ui.countdown');
\Bitrix\Main\UI\Extension::load('ui.hint');
?>

<?php
if (!empty($arResult['BUTTONS']))
    foreach($arResult['BUTTONS'] as $buttonObj)
    {
        echo $buttonObj->render();
    }

?>

