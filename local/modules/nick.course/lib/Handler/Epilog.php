<?php


namespace Nick\Course\Handler;

use Bitrix\Main\Application;
use Bitrix\Main\Diag\Debug;
use CJSCore;
use CUtil;

class Epilog
{

    /**
     * Подключение JS библиотек
     */
    public static function includeJsLibraries()
    {
        $arJsLibs = [
            'task_detail'=> [
                "js" => "/local/modules/nick.course/js/task_detail/taskDetail.js",
                "rel" => []
            ],
        ];

        foreach($arJsLibs as $jsLibName=>$options) {
            CJSCore::RegisterExt($jsLibName, $options);
        }

        $curPage = Application::getInstance()->getContext()->getRequest()->getRequestedPageDirectory();

        if (preg_match('#/company/personal/user/\d+/tasks/task/view/\d+/#', $curPage)) {
            CJSCore::Init(['task_detail']);
        }
    }
}
