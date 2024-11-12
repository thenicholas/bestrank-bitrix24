<?php

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid())
    return;

global $APPLICATION;
?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
    <?= bitrix_sessid_post(); ?>
    <input type='hidden' name='id' value='nick.course'>
    <input type='hidden' name='install' value='Y'>
    <input type='hidden' name='step' value='2'>
    <p>
        <input type='checkbox' name='create_hlblock' id='create_hlblock' value='Y' checked>
        <label for='create_hlblock'><?= Loc::getMessage('NI_CO_INSTALL_CREATE_HLBLOCK'); ?></label>
    </p>
    <p>
        <input type="hidden" name="lang" value="<?= LANG ?>">
        <input type="submit" name="" value="<?= Loc::getMessage("NI_CO_INSTALL_SUBMIT_BTN") ?>">
    </p>
    <form>
