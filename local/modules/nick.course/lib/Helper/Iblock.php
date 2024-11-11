<?php

namespace Nick\Course\Helper;

use Bitrix\Iblock\IblockTable;

class Iblock
{
    public static function getIblockList()
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
}
