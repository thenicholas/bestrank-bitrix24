<?php


namespace Nick\Course\Handler;

use Bitrix\Main\Context;
use Bitrix\Main\LoaderException;
use Bitrix\Main\UI\Extension;

class Epilog
{

    /**
     * Подключение JS библиотек
     * @return void
     * @throws LoaderException
     */
    public static function includeJsLibraries(): void
    {
        $currentPage = Context::getCurrent()->getRequest()->getRequestUri();

        if (preg_match('#/company/personal/user/\d+/tasks/task/view/\d+/#', $currentPage)) {
            Extension::load('nick_course.task_detail_more_menu');
        }
    }
}
