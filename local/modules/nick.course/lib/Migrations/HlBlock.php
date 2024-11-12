<?php

namespace Nick\Course\Migrations;

use Bitrix\Highloadblock\HighloadBlockLangTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Nick\Course\Helper;

class HlBlock
{
    /**
     * Создание HL блока
     * @throws SystemException
     */
    public static function up()
    {
        Loader::requireModule('highloadblock');

        $result = Helper\HighLoadBlock::getHlblock(
            filter: [
                'NAME' => Loc::getMessage(
                    'NI_CO_MIGRATIONS_HLBLOCK_GRADES_NAME'
                )
            ],
            select: ['ID']
        );

        if (!empty($result['ID'])) {
            return $result['ID'];
        } else {
            $result = HighloadBlockTable::add([
                'NAME' => 'GradesList',
                'TABLE_NAME' => 'b_hlst_grades_list',
            ]);

            if (!$result->isSuccess()) {
                throw new SystemException(implode(';', $result->getErrorMessages()));
            }

            $id = $result->getId();
            HighloadBlockLangTable::add([
                'ID' => $id,
                'LID' => SITE_ID,
                'NAME' => Loc::getMessage("NI_CO_MIGRATIONS_HLBLOCK_GRADES_NAME")
            ]);
            static::addUserTypeEntity(
                self::getUfArFields(
                    $id,
                    [
                        'FIELD_NAME' => 'UF_ACTIVE',
                        'USER_TYPE_ID' => 'boolean',
                        'SORT' => 100,
                        'LABEL' => Loc::getMessage("NI_CO_FIELD_LABEL_UF_ACTIVE")
                    ]
                ),
            );
            static::addUserTypeEntity(
                self::getUfArFields(
                    $id,
                    [
                        'FIELD_NAME' => 'UF_GRADE',
                        'USER_TYPE_ID' => 'integer',
                        'SORT' => 100,
                        'LABEL' => Loc::getMessage('NI_CO_FIELD_LABEL_UF_GRADE')
                    ]
                ),
            );
            static::addUserTypeEntity(
                self::getUfArFields(
                    $id,
                    [
                        'FIELD_NAME' => 'UF_CODE',
                        'USER_TYPE_ID' => 'string',
                        'SORT' => 100,
                        'LABEL' => Loc::getMessage('NI_CO_FIELD_LABEL_UF_CODE')
                    ]
                ),
            );
            static::addUserTypeEntity(
                self::getUfArFields(
                    $id,
                    [
                        'FIELD_NAME' => 'UF_TEXT',
                        'USER_TYPE_ID' => 'string',
                        'SORT' => 100,
                        'LABEL' => Loc::getMessage('NI_CO_FIELD_LABEL_UF_TEXT')
                    ]
                ),
            );
        }
        return $id;
    }

    protected static function getUfArFields($hlId, $data)
    {
        $arFields = [
            'ENTITY_ID' => 'HLBLOCK_' . $hlId,
            'FIELD_NAME' => $data['FIELD_NAME'],
            'USER_TYPE_ID' => $data['USER_TYPE_ID'],
            'XML_ID' => $data['FIELD_NAME'],
            'SORT' => $data['SORT'] ?: 100,
            'MULTIPLE' => $data['MULTIPLE'] ?: 'N',
            'MANDATORY' => $data['MANDATORY'] ?: 'N',
            'SHOW_FILTER' => $data['SHOW_FILTER'] ?: 'N',
            'SHOW_IN_LIST' => $data['SHOW_IN_LIST'] ?: 'Y',
            'EDIT_IN_LIST' => $data['EDIT_IN_LIST'] ?: 'Y',
            'IS_SEARCHABLE' => $data['IS_SEARCHABLE'] ?: 'N',
            'EDIT_FORM_LABEL' => ['ru' => $data['LABEL']],
            'LIST_COLUMN_LABEL' => ['ru' => $data['LABEL']],
            'LIST_FILTER_LABEL' => ['ru' => $data['LABEL']],
            'ERROR_MESSAGE' => ['ru' => '',],
            'HELP_MESSAGE' => ['ru' => '',],
        ];

        switch ($data['USER_TYPE_ID']) {
            case 'boolean':
                $arFields['SETTINGS'] = [
                    'DEFAULT_VALUE' => 1,
                    'DISPLAY' => 'CHECKBOX',
                    'LABEL' => ['', ''],
                    'LABEL_CHECKBOX' => '',
                ];
            case 'integer':
                $arFields['SETTINGS'] = [
                    'SIZE' => 20,
                    'MIN_VALUE' => 0,
                    'MAX_VALUE' => 0,
                    'DEFAULT_VALUE' => ''
                ];
            case 'string':
                $arFields['SETTINGS'] = [
                    'SIZE' => 20,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ];

                break;
            default:
                break;
        }


        return $arFields;
    }

    /**
     * Удаление HL блока
     * @throws SystemException
     * @throws LoaderException
     */
    public static function down()
    {
        Loader::requireModule('highloadblock');

        $hlBlock = Helper\HighLoadBlock::getHlblock(
            filter: [
                'NAME' => Loc::getMessage(
                    'NI_CO_MIGRATIONS_HLBLOCK_GRADES_NAME'
                )
            ],
            select: ['ID']
        );

        if (!empty($hlBlock)) {
            $result = HighloadBlockTable::delete($hlBlock['ID']);
            if (!$result->isSuccess()) {
                throw new SystemException(implode(';', $result->getErrorMessages()));
            }
        }
    }

    protected static function addUserTypeEntity($field)
    {
        $obUserField = new \CUserTypeEntity;
        $obUserField->Add($field);
    }
}
