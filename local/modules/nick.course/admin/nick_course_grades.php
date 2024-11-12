<?php

defined("B_PROLOG_INCLUDED") || die;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Nick\Course\Helper;
use Nick\Course\Helper\Options;


global $APPLICATION, $USER;

if (!$USER->IsAdmin()) {
    return;
}

Loader::requireModule("nick.course");

$moduleId = Helper\Options::getModuleId();
$request = Application::getInstance()->getContext()->getRequest();

$highLoadBlockList = Helper\HighLoadBlock::getHlblocks(select: ['ID', 'NAME']);
$highLoadBlockId = Option::get(Options::moduleId, 'GRADE_LIST_ID');

if (!empty($highLoadBlockId)) {
    $highLoadBlockFieldsList = Helper\HighLoadBlock::getFieldsMap($highLoadBlockId);
}

$options = [
    'general' => [
        [
            'note' => Loc::getMessage('NI_CO_NOTE')
        ],
        [
            'GRADE_LIST_ID',
            Loc::getMessage('NI_CO_OPTION_HLB'),
            Option::get(Options::moduleId, 'GRADE_LIST_ID'),
            [
                'selectbox',
                $highLoadBlockList,
            ]
        ],
    ],
];

if (!empty($highLoadBlockId)) {
    $options['general'][] =
        [
            'gradesFieldName',
            Loc::getMessage('NI_CO_OPTION_HLB_FIELD'),
            Option::get(Options::moduleId, 'gradesFieldName'),
            [
                'selectbox',
                $highLoadBlockFieldsList,
            ]
        ];
}

$tabs = [
    [
        "DIV" => "general",
        "TAB" => Loc::getMessage('NI_CO_TAB_GENERAL_TEXT'),
        "TITLE" => Loc::getMessage('NI_CO_TAB_GENERAL_TITLE')
    ]
];


if (check_bitrix_sessid() && (strlen($request->getPost("save")) > 0 || strlen($request->getPost("apply")) > 0)) {
    if (!is_array($options)) {
        return false;
    }

    foreach ($options as $arOptions) {
        Helper\RenderOptions::__AdmSettingsSaveOptions($moduleId, $arOptions);
    }

    if (strlen($request->getPost("save")) > 0) {
        LocalRedirect($request->getRequestUri());
    }
}
//Подключаем заголовок модуля
$APPLICATION->SetTitle(Loc::getMessage("MAIN_SETTINGS_TITLE"));

require(Application::getDocumentRoot() . "/bitrix/modules/main/include/prolog_admin_after.php");

$tabControl = new CAdminTabControl('tabControl', $tabs);
$tabControl->Begin();
?>
    <form method="POST" action="<?= $request->getRequestUri() ?>">
        <?php
        foreach ($options as $option) {
            $tabControl->BeginNextTab();
            Helper\RenderOptions::__AdmSettingsDrawList($moduleId, $option);
        }
        $tabControl->Buttons(['btnApply' => true, 'btnCancel' => false, 'btnSaveAndAdd' => false]);
        echo bitrix_sessid_post();
        $tabControl->End();
        ?>
    </form>
<?php
require(Application::getDocumentRoot() . "/bitrix/modules/main/include/epilog_admin.php");
