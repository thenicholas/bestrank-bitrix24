<?php defined("B_PROLOG_INCLUDED") || die;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Nick\Course\Helper;

global $APPLICATION, $USER;

if (!$USER->IsAdmin())
    return;

Loader::requireModule("nick.course");

$moduleId = Helper\Options::getModuleId();
$request = Application::getInstance()->getContext()->getRequest();

$iblockList = Helper\Iblock::getIblockList();

$userList = Helper\User::getUserList();

$options = [
    'general' => [
        [
            'isModuleActive',
            Loc::getMessage('NI_CO__OPTION_IS_MODULE_ACTIVE'),
            Option::get($moduleId, 'isModuleActive'),
            [
                'checkbox',
            ],
        ],
        [
            'mainIblock',
            Loc::getMessage('NI_CO__OPTION_MAIN_IBLOCK'),
            Option::get($moduleId, 'mainIblock'),
            [
                'selectbox',
                $iblockList,
            ]
        ],
    ],
    'additional' => [
        [
            'user',
            Loc::getMessage('NI_CO__OPTION_USER'),
            Option::get($moduleId, 'user'),
            [
                'multiselectbox',
                $userList,
            ]
        ],
    ]
];
$tabs = [];


$tabs[] = [
    "DIV" => "general",
    "TAB" => Loc::getMessage("NI_CO_TAB_GENERAL_NAME"),
    "TITLE" => Loc::getMessage("NI_CO_TAB_GENERAL_TITLE")
];

$tabs[] = [
    "DIV" => "ui_form_config",
    "TAB" => Loc::getMessage("UI_FORM_CONFIG_TAB"),
    "TITLE" => Loc::getMessage("UI_FORM_CONFIG_TITLE")
];


if (check_bitrix_sessid() && (strlen($request->getPost("save")) > 0 || strlen($request->getPost("apply")) > 0)) {

    if (!is_array($options))
        return false;

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
