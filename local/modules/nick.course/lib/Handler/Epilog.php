<?php


namespace Nick\Course\Handler;

use Bitrix\Main\Context;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\LoaderException;
use Bitrix\Main\UI\Extension;
use Nick\Course\Helper\Options;

class Epilog
{

    /**
     * Подключение JS библиотек
     * @return void
     * @throws LoaderException
     */
    public static function includeJsLibraries(): void
    {
        Debug::dumpToFile(Options::getParam('TASK_MENU_ITEMS'));

        if (Options::getParam('TASK_MENU_ITEMS')) {
            $currentPage = Context::getCurrent()->getRequest()->getRequestUri();
            if (preg_match('#/company/personal/user/\d+/tasks/task/view/\d+/#', $currentPage)) {
                Extension::load('nick_course.task_detail_more_menu');
            }
        }
    }
}
