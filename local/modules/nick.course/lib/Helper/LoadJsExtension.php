<?php

namespace Nick\Course\Helper;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

class LoadJsExtension
{
    /**
     * @param string $extensionName
     * @param array $data
     * @return void
     * @throws LoaderException
     * @throws ArgumentException
     */
    public static function loadWithData(string $extensionName, array $data): void
    {
        Extension::load($extensionName);

        Asset::getInstance()->addString(
            '<script>
                    BX.ready(function() {
                        BX.NickCourse.TaskDetailMoreMenu.data = ' . Json::encode($data) . ';
                    });
                </script>'
        );
    }
    public static function write_to_console($string)
    {
        if (is_array($string)) {
            Asset::getInstance()->addString('<script>console.log(' . \CUtil::PhpToJSObject($string) . ');</script>');
        } else {
            Asset::getInstance()->addString("<script>console.log('" . $string . "');</script>");
        }
    }

    /**
     * @return false|array
     */
    public static function urlParser(): false|array
    {
        $arUrlTemplates = [
            'path_user' => ltrim(
                Option::get(
                    'intranet',
                    'path_user',
                    '/company/personal/user/#USER_ID#/',
                    SITE_ID
                ),
                '/'
            ),
            'paths_task_user' => ltrim(
                Option::get(
                    'tasks',
                    'paths_task_user',
                    '/company/personal/user/#user_id#/tasks/',
                    SITE_ID
                ),
                '/'
            ),
            'paths_task_user_entry' => ltrim(
                Option::get(
                    'tasks',
                    'paths_task_user_entry',
                    '/company/personal/user/#user_id#/tasks/task/view/#task_id#/',
                    SITE_ID
                ),
                '/'
            ),
            'paths_task_group' => ltrim(
                Option::get(
                    'tasks',
                    'paths_task_group',
                    '/workgroups/group/#group_id#/tasks/',
                    SITE_ID
                ),
                '/'
            ),
            'paths_task_group' => ltrim(
                Option::get(
                    'tasks',
                    'paths_task_group',
                    '/workgroups/group/#group_id#/tasks/',
                    SITE_ID
                ),
                '/'
            ),
        ];
        $componentPath = \CComponentEngine::parseComponentPath('/', $arUrlTemplates, $arVariables);

        switch ($componentPath) {
            case 'path_user':
                return [
                    'module' => 'intranet',
                    'componentPath' => $componentPath,
                    'arVariables' => $arVariables
                ];
            case 'paths_task_user_entry':
                return [
                    'module' => 'tasks_detail',
                    'componentPath' => $componentPath,
                    'arVariables' => $arVariables
                ];
            case 'paths_task_user':
            case 'paths_task_group':
                return [
                    'module' => 'tasks',
                    'componentPath' => $componentPath,
                    'arVariables' => $arVariables
                ];
            default:
                return false;
        }
    }
}
