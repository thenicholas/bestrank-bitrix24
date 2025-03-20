<?php


namespace Nick\Course\Handler;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Nick\Course\Helper;
use Nick\Course\Helper\LoadJsExtension;
use Nick\Course\Helper\Options;

class Epilog
{

    /**
     * Подключение JS библиотек
     * @return void
     * @throws LoaderException
     * @throws ArgumentException
     */
    public static function includeJsLibraries(): void
    {
        $urlParse = Helper\LoadJsExtension::urlParser();

        if ($urlParse === false) {
            return;
        }

        $userIdList = explode(',', Option::get(Options::moduleId, 'TASK_MENU_USERS'));

        if ($urlParse['module'] == 'tasks_detail' &&
            in_array(CurrentUser::get()->getId(), $userIdList)
        ) {
            $optionValue = Options::getParam('TASK_MENU_ITEMS');
            $menuItems = preg_split("/\r\n|\n|\r/", $optionValue);
            $menuItems = array_values(array_filter($menuItems));
            $data = $urlParse;
            $data['menuItems'] = $menuItems;
            Asset::getInstance()->addString(
                '<script>
                    BX.ready(function() {
                        BX.NickCourse.TaskDetailMoreMenu.data = ' . Json::encode($data) . ';
                    });
                </script>'
            );

            Extension::load('nick_course.task_detail_more_menu');
        }

        if ($urlParse['componentPath'] === 'paths_task_user') {
            $data = Helper\Task::getEnumUserFieldsValues();
            Asset::getInstance()->addString(
                '<script>
                    BX.ready(function() {
                        BX.NickCourse.GridCustomTasks.data = ' . Json::encode($data) . ';
                    });
                </script>'
            );
            Extension::load('nick_course.grid_custom_tasks');
        }
    }
}
