<?php

namespace Nick\Course\Helper;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;

Loader::includeModule('highloadblock');
class HighLoadBlock
{
    public static function getHlblock(
        $filter = [],
        $select = [],
        $order = [],
        $limit = null,
        $offset = null
    ): array
    {
        $res = HighloadBlockTable::getRow([
            'filter'  => $filter,
            'select'  => $select,
            'order'   => $order,
            'limit'   => $limit,
            'offset'  => $offset
        ]);
        if (isset($res) && !empty($res)) {
            return $res;
        } else {
            return [];
        }
    }

    public static function getHlblocks(
        $filter = [],
        $select = [],
        $order = [],
        $limit = null,
        $offset = null
    ): array
    {
        $highLoadBlocks = HighloadBlockTable::getList([
            'filter'  => $filter,
            'select'  => $select,
            'order'   => $order,
            'limit'   => $limit,
            'offset'  => $offset,
        ])->fetchAll();

        if (isset($highLoadBlocks) && !empty($highLoadBlocks)) {
            $highLoadBlockList = [];
            foreach ($highLoadBlocks as $highLoadBlock) {
                $highLoadBlockList[$highLoadBlock['ID']] = $highLoadBlock['NAME'];
            }

            return $highLoadBlockList;
        } else {
            return [];
        }
    }


    /**
     * Получение экземпляра класса
     * @param $HlBlockId
     * @return \Bitrix\Main\ORM\Data\DataManager|false|string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getEntityDataClass($HlBlockId)
    {
        if (empty($HlBlockId) || $HlBlockId < 1) {
            return false;
        }
        $hlblock = HighloadBlockTable::getById($HlBlockId)->fetch();
        $entity = HighloadBlockTable::compileEntity($hlblock);
        return $entity->getDataClass();
    }

    /**
     * Получение списка полей HL-блока
     * @param int $hlBlockId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getFieldsMap(int $hlBlockId): array
    {
        $fieldsMap = [];

        $fieldsMap[] = [
            'NAME' => 'ID',
            'DATA_TYPE' => 'integer',
            'IS_PRIMARY' => true,
            'TITLE' => 'ID'
        ];

        $query = \Bitrix\Main\UserFieldTable::query()
            ->where('ENTITY_ID', 'HLBLOCK_' . $hlBlockId)
            ->setSelect(['ID', 'FIELD_NAME', 'USER_TYPE_ID'])
            ->exec();

        while ($field = $query->fetch()) {
            $fieldsMap[$field['ID']] = [
                'ID' => $field['ID'],
                'NAME' => $field['FIELD_NAME'],
                'DATA_TYPE' => $field['USER_TYPE_ID']
            ];
        }

        $query = \Bitrix\Main\UserFieldLangTable::query()
            ->whereIn('USER_FIELD_ID', array_keys($fieldsMap))
            ->where('LANGUAGE_ID', LANGUAGE_ID)
            ->setSelect(['USER_FIELD_ID', 'EDIT_FORM_LABEL'])
            ->exec();

        while ($lang = $query->fetch()) {
            $fieldsMap[$lang['USER_FIELD_ID']]['TITLE'] = $lang['EDIT_FORM_LABEL'];
        }

        $fieldsList = [];

        foreach (array_values($fieldsMap) as $field) {
            if ($field['NAME'] === 'ID') continue;
            $fieldsList[$field['NAME']] = $field['TITLE'];
        }

        return $fieldsList;
    }
}
