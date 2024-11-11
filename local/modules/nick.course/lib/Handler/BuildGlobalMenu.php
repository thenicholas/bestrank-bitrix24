<?php
namespace Nick\Course\Handler;

use Bitrix\Main\Localization\Loc;

class BuildGlobalMenu
{
    /**
     * Добавление глобального раздела в меню админки
     * @param $aGlobalMenu
     * @param $aModuleMenu
     */
    public static function addMenuItem(&$aGlobalMenu, &$aModuleMenu)
    {
        global $USER;

        if (!$USER->IsAdmin() || is_array($aGlobalMenu['global_menu_nick_course']))
            return;

        $aGlobalMenu['global_menu_nick_course'] = [
            "menu_id" => "nick_course",
            "text" => Loc::getMessage('GLOBAL_MENU_NICK_COURSE_TEXT'),
            "title" => Loc::getMessage('GLOBAL_MENU_NICK_COURSE_TITLE'),
            "sort" => 1000,
            "items_id" => "global_menu_nick_course",
            "items" => []
        ];
    }
}
