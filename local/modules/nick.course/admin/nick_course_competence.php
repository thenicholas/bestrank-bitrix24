<?php

defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Nick\Course\Helper;


global $APPLICATION, $USER;

if (!$USER->IsAdmin()) {
    return;
}

Loader::requireModule('nick.course');

$moduleId = Helper\Options::getModuleId();
$request = Application::getInstance()->getContext()->getRequest();

$tabs = [
    [
        'DIV' => 'general',
        'TAB' => Loc::getMessage('NI_CO_TAB_GENERAL_TEXT'),
        'TITLE' => Loc::getMessage('NI_CO_TAB_GENERAL_TITLE')
    ]
];

$listLists[''] = '-';
foreach (Helper\Iblock::getIblocks(['IBLOCK_TYPE_ID' => 'lists'], ['ID', 'NAME']) as $list) {
    $listLists[$list['ID']] = '[' . $list['ID'] . '] ' . $list['NAME'];
}

$userCompetenceListId = Helper\Options::getParam('USER_COMPETENCE_LIST_ID');
$options = [
    'general' => [
        [
            'USER_COMPETENCE_LIST_ID',
            Loc::getMessage('NI_CO_USER_COMPETENCE_LIST_ID'),
            $userCompetenceListId,
            [
                'selectbox',
                $listLists,
            ]
        ],
    ],
];

if ($userCompetenceListId) {
    $employeeProps[''] = '-';
    $propFilter = ['IBLOCK_ID' => $userCompetenceListId, 'USER_TYPE' => 'employee'];
    foreach (Helper\Iblock::getProperties($propFilter, ['ID', 'NAME']) as $property) {
        $employeeProps[$property['ID']] = '[' . $property['ID'] . '] ' . $property['NAME'];
    }
    $options['general'][] = [
        'USER_COMPETENCE_LIST_USER_PROP_CODE',
        Loc::getMessage('NI_CO_USER_COMPETENCE_LIST_USER_PROP_CODE'),
        null,
        ['selectbox', $employeeProps]
    ];
}

$tabs = [
    [
        'DIV' => 'general',
        'TAB' => Loc::getMessage('NI_CO_TAB_GENERAL_TEXT'),
        'TITLE' => Loc::getMessage('NI_CO_TAB_GENERAL_TITLE')
    ]
];


if (check_bitrix_sessid() && (strlen($request->getPost('save')) > 0 || strlen($request->getPost('apply')) > 0)) {
    if (!is_array($options)) {
        return false;
    }

    foreach ($options as $arOptions) {
        Helper\RenderOptions::__AdmSettingsSaveOptions($moduleId, $arOptions);
    }

    if (strlen($request->getPost('save')) > 0) {
        LocalRedirect($request->getRequestUri());
    }
}
//Подключаем заголовок модуля
$APPLICATION->SetTitle(Loc::getMessage('MAIN_SETTINGS_TITLE'));

require(Application::getDocumentRoot() . '/bitrix/modules/main/include/prolog_admin_after.php');

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
require(Application::getDocumentRoot() . '/bitrix/modules/main/include/epilog_admin.php');
