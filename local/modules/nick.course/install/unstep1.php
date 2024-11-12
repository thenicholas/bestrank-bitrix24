<?php

use Bitrix\Main\Localization\Loc;

global $APPLICATION;
?>
<form action="<?= $APPLICATION->GetCurPage(); ?>">
    <?= bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="id" value="nick.course">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <?php CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN")); ?>
    <p><?= Loc::getMessage("MOD_UNINST_SAVE"); ?></p>
    <p>
        <input type="checkbox" name="save_tables" id="save_tables" value="Y" checked>
        <label for="save_tables"><?= Loc::getMessage("MOD_UNINST_SAVE_TABLES"); ?></label>
    </p>
    <input type="submit" name="inst" value="<?= Loc::getMessage("MOD_UNINST_DEL"); ?>">
</form>
