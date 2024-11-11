<?php

namespace Nick\Course\Helper;

use Bitrix\Main\UserTable;

class User
{
    public static function getUserList()
    {
        $users = UserTable::getList([
            'select' => ['ID', 'NAME'],
            'filter' => ['ACTIVE' => 'Y'],
            'order' => ['ID'],
        ])->fetchAll();

        $userList = [];
        foreach ($users as $user) {
            $userList[$user['ID']] = $user['NAME'];
        }
        return $userList;
    }
}
