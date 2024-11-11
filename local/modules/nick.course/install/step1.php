<?php

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid())
    return;

global $APPLICATION;
if ($exception = $APPLICATION->GetException()) {
    CAdminMessage::ShowMessage([
        'TYPE' => 'ERROR',
        'MESSAGE' => Loc::getMessage('MOD_INST_ERR'),
        'DETAILS' => $exception->GetString(),
        'HTML' => true,
    ]);
} else {
    CAdminMessage::ShowNote(Loc::getMessage('MOD_INST_OK'));
}
?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
    <p>
        <input type="hidden" name="lang" value="<?= LANG ?>">
        <input type="submit" name="" value="<?= Loc::getMessage("MOD_BACK") ?>">
    </p>
    <form>