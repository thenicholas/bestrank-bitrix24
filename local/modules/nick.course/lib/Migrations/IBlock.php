<?php

namespace Nick\Course\Migrations;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SiteTable;
use Bitrix\Main\SystemException;
use Bitrix\Tasks\Exception;
use CIBlock;
use Nick\Course\Helper;
use Bitrix\Main\Localization\Loc;

class IBlock
{
    /**
     * @return int|null
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentOutOfRangeException
     */
    public static function up()
    {
        Loader::includeModule('lists');

        $exist = Helper\Iblock::getIblock(['CODE' => 'st_grades'], ['ID']);

        if (!$exist) {
            $siteList = SiteTable::getList(['select' => ['LID']])->fetchAll();

            $ob = new CIBlock;
            $arFields = [
                'NAME' => Loc::getMessage("MIGRATIONS_IBLOCK_GRADES_NAME"),
                'CODE' => 'st_grades',
                'API_CODE' => 'stGrades',
                'LIST_PAGE_URL' => '',
                'DETAIL_PAGE_URL' => '',
                'IBLOCK_TYPE_ID' => 'lists',
                'SITE_ID' => array_column($siteList, 'LID'),
                'DESCRIPTION' => '',
                'WORKFLOW' => 'N',
                'BIZPROC' => 'Y'
            ];
            $iblockId = $ob->Add($arFields);

            if ($iblockId) {
                CIBlock::SetPermission($iblockId, ["2"=>"R"]);
                $obList = new \CList($iblockId);

                $defaultFieldSettings = [
                    'IS_REQUIRED' => 'N',
                    'MULTIPLE' => 'N',
                    'DEFAULT_VALUE' => '',
                    'USER_TYPE_SETTINGS' => NULL,
                    'SETTINGS' =>
                        [
                            'SHOW_ADD_FORM' => 'Y',
                            'SHOW_EDIT_FORM' => 'Y',
                            'ADD_READ_ONLY_FIELD' => 'N',
                            'EDIT_READ_ONLY_FIELD' => 'N',
                            'SHOW_FIELD_PREVIEW' => 'N',
                        ],
                    'LIST' => []
                ];

                $fieldId = $obList->AddField(array_merge($defaultFieldSettings, [
                    'SORT' => 20,
                    'NAME' => Loc::getMessage("IBLOCK_FIELD_USER_ID"),
                    'CODE' => 'USER_ID',
                    'TYPE' => 'S:employee'
                ]));

                $fieldId = str_replace('PROPERTY_', '', $fieldId);

                Helper\Options::setParam('USER_COMPETENCE_LIST_USER_PROP_CODE', $fieldId);

                $obList->AddField(array_merge($defaultFieldSettings, [
                    'SORT' => 30,
                    'NAME' => Loc::getMessage("IBLOCK_FIELD_COMPETENCE_ID"),
                    'CODE' => 'COMPETENCE_ID',
                    'TYPE' => 'N'
                ]));

                $obList->AddField(array_merge($defaultFieldSettings, [
                    'SORT' => 40,
                    'NAME' => Loc::getMessage("IBLOCK_FIELD_GRADE_ID"),
                    'CODE' => 'GRADE_ID',
                    'TYPE' => 'N',
                    'USER_TYPE_SETTINGS' => ['HL_BLOCK_ID' => Helper\Options::getParam('GRADES_LIST_ID')]
                ]));

                global $CACHE_MANAGER;
                $CACHE_MANAGER->ClearByTag("lists_list_" . $iblockId);
                $CACHE_MANAGER->ClearByTag("lists_list_any");
                $CACHE_MANAGER->CleanDir("menu");


                Helper\Options::setParam('USER_COMPETENCE_LIST_ID', $iblockId);
                return $iblockId;
            }
        }
    }

    /**
     * @return void
     * @throws SystemException
     * @throws LoaderException
     */
    public static function down(): void
    {
        global $DB, $APPLICATION;
        Loader::includeModule('lists');
        $exist = Helper\Iblock::getIblock(['CODE' => 'st_grades'], ['ID']);

        if ($exist) {
            $DB->StartTransaction();
            if (!CIBlock::Delete($exist['ID'])) {
                $DB->Rollback();
                if($ex = $APPLICATION->GetException()) {
                    $strError = $ex->GetString();
                    throw new SystemException($strError);
                }
                throw new SystemException(Loc::getMessage('IBLOCK_DELETE_ERROR'));
            } else {
                $DB->Commit();
            }
        }
    }
}
