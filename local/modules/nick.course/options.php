<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\UserTable;
use Bitrix\Iblock\TypeTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Study\UserRating\Helpers\Options;

global $APPLICATION;
Loc::loadMessages(__FILE__);
$module_id = 'nick.course';

try {
    Loader::requireModule($module_id);
    $tabs = [
        [
            'DIV' => 'general',
            'TAB' => Loc::getMessage('NI_CO__TAB_GENERAL_NAME'),
            'TITLE' => Loc::getMessage('NI_CO__TAB_GENERAL_TITLE')
        ],
        [
            'DIV' => 'additional',
            'TAB' => Loc::getMessage('NI_CO__TAB_ADDITIONAL_NAME'),
            'TITLE' => Loc::getMessage('NI_CO__TAB_ADDITIONAL_TITLE')
        ]
    ];
    /**
     * CControllerClient::GetInstalledOptions($module_id);
     * Формат массива, элементы:
     * 1) ID опции (id инпута)(Берется с помощью COption::GetOptionString($module_id, $Option[0], $Option[2])
     * если есть)
     * 2) Отображаемое название опции
     * 3) Значение по умолчанию (так же берется если первый элемент равен пустой строке), зависит от типа:
     *      checkbox - Y если выбран
     *      text/password - htmlspecialcharsbx($val)
     *      selectbox - одно из значений, указанных в массиве опций
     *      multiselectbox - значения через запятую, указанные в массиве опций
     * 4) Тип поля (массив)
     *      1) Тип (multiselectbox, textarea, statictext, statichtml, checkbox, text, password, selectbox)
     *      2) Зависит от типа:
     *         text/password - атрибут size
     *         textarea - атрибут rows
     *         selectbox/multiselectbox - массив опций формата ["Значение"=>"Название"]
     *      3) Зависит от типа:
     *         checkbox - доп атрибут для input (просто вставляется строкой в атрибуты input)
     *         textarea - атрибут cols
     *
     *          noautocomplete) для text/password, если true то атрибут autocomplete="new-password"
     *
     * 5) Disabled = 'Y' || 'N';
     * 6) $sup_text - ??? текст маленького красного примечания над названием опции
     * 7) $isChoiceSites - Нужно ли выбрать сайт??? флаг Y или N
     */

    $iblocks = IblockTable::getList([
        'select' => ['ID', 'NAME', 'SORT'],
        'filter' => ['ACTIVE' => 'Y'],
        'order' => ['SORT', 'ID'],
    ])->fetchAll();

    $iblockList = [];
    foreach ($iblocks as $iblock) {
        $iblockList[$iblock['ID']] = $iblock['NAME'];
    }

    $users = UserTable::getList([
        'select' => ['ID', 'NAME'],
        'filter' => ['ACTIVE' => 'Y'],
        'order' => ['ID'],
    ])->fetchAll();

    $userList = [];
    foreach ($users as $user) {
        $userList[$user['ID']] = $user['NAME'];
    }

    $arOptions = [
        'general' => [
            [
                'isModuleActive',
                Loc::getMessage('NI_CO__OPTION_IS_MODULE_ACTIVE'),
                Option::get(Options::moduleId, 'isModuleActive'),
                [
                    "checkbox",
                ],
            ],
            [
                'mainIblock',
                Loc::getMessage('NI_CO__OPTION_MAIN_IBLOCK'),
                Option::get(Options::moduleId, 'mainIblock'),
                [
                    "selectbox",
                    $iblockList,
                ]
            ],
        ],
        'additional' => [
            [
                'user',
                Loc::getMessage('NI_CO__OPTION_USER'),
                Option::get(Options::moduleId, 'user'),
                [
                    'multiselectbox',
                    $userList,
                ]
            ]
        ]
    ];

//region Cохранение формы

    $request = HttpApplication::getInstance()->getContext()->getRequest();
    $isSave = $request->isPost() && !empty($request['save']);
    $isApply = $request->isPost() && !empty($request['apply']);

    if (check_bitrix_sessid()
        && $request->isPost()
        && ($isSave || $isApply)
    ) {
        foreach ($arOptions as $option) {
            __AdmSettingsSaveOptions($module_id, $option);
        }
        if ($isSave) {
            LocalRedirect($APPLICATION->GetCurPageParam());
        }
    }

//region Конструктор формы

    $tabControl = new CAdminTabControl('tabControl', $tabs);
    $tabControl->Begin();
    ?>

    <form method="POST"
          action="<?php
          echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($module_id) ?>&lang=<?= LANGUAGE_ID ?>"
          id="baseexchange_form">
        <?php

        foreach ($arOptions as $option) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $option);
        }
        $tabControl->Buttons(['btnApply' => false, 'btnCancel' => false, 'btnSaveAndAdd' => false]);
        echo bitrix_sessid_post();
        $tabControl->End();
        ?>
    </form>
    <?php
} catch (Exception $exception) {
    CAdminMessage::ShowMessage([
        'TYPE' => 'ERROR',
        'MESSAGE' => Loc::getMessage('MOD_INST_ERR'),
        'DETAILS' => $exception->getMessage(),
        'HTML' => true,
    ]);
}
