<?php

namespace Nick\Course\Helper;

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;

class Iblock
{
    /**
     * @return array
     */
    public static function getIblockList(): array
    {
        $iblocks = IblockTable::getList([
            'select' => ['ID', 'NAME', 'SORT'],
            'filter' => ['ACTIVE' => 'Y'],
            'order' => ['SORT', 'ID'],
        ])->fetchAll();

        $iblockList = [];
        foreach ($iblocks as $iblock) {
            $iblockList[$iblock['ID']] = $iblock['NAME'];
        }

        return $iblockList;
    }

    public static function getIblock(
        $filter = [],
        $select = [],
        $order = [],
        $limit = null,
        $offset = null
    ): array
    {
        $res = IblockTable::getRow([
            'filter' => $filter,
            'select' => $select,
            'order' => $order,
            'limit' => $limit,
            'offset' => $offset
        ]);
        if (!empty($res)) {
            return $res;
        }

        return [];
    }

    public static function getIblocks(
        $filter = [],
        $select = [],
        $order = [],
        $limit = null,
        $offset = null
    ): array
    {
        $res = IblockTable::getList([
            'filter' => $filter,
            'select' => $select,
            'order' => $order,
            'limit' => $limit,
            'offset' => $offset,
        ])->fetchAll();
        if (!empty($res)) {
            return $res;
        }

        return [];
    }

    /**
     * @param array $filter
     * @param array $select
     * @return array
     */
    public static function getIblocksNames(array $filter, array $select): array
    {
        $iblocks = [];
        $rows = self::getIblocks($filter, $select);
        foreach ($rows as $row) {
            $iblocks[$row['ID']] = $row['NAME'];
        }
        return $iblocks;
    }

    public static function getProperty(
        $filter = [],
        $select = [],
        $order = [],
        $runtime = null,
        $limit = null,
        $offset = null,
        $group = []
    ): array
    {
        $res = PropertyTable::getRow([
            'order' => $order,
            'filter' => $filter,
            'select' => $select,
            'group' => $group,
            'limit' => $limit,
            'offset' => $offset,
            'runtime' => $runtime
        ]);
        if (!empty($res)) {
            return $res;
        } else {
            return [];
        }
    }

    /**
     * @param array $filter
     * @param array $select
     * @param array $order
     * @param $limit
     * @param $offset
     * @return array
     */
    public static function getProperties(
        array $filter = [],
        array $select = [],
        array $order = [],
        $limit = null,
        $offset = null
    ): array
    {
        $res = PropertyTable::getList([
            'filter' => $filter,
            'select' => $select,
            'order' => $order,
            'limit' => $limit,
            'offset' => $offset
        ])->fetchAll();
        if (!empty($res)) {
            return $res;
        } else {
            return [];
        }
    }

    /**
     * @param $filter
     * @param $select
     * @return array
     */
    public static function getPropertiesByType($filter, $select): array
    {
        $properties = [];
        $rows = self::getProperties($filter, $select);
        foreach ($rows as $row) {
            $properties[$row['CODE']] = $row['NAME'];
        }
        return $properties;
    }
}
