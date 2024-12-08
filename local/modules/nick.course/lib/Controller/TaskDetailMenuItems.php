<?php

namespace Nick\Course\Controller;

use Bitrix\Main\Engine\Controller;
use Nick\Course\Helper\Options;

class TaskDetailMenuItems extends Controller
{
    /**
     * @return array|null
     */
    public function getAction(): ?array
    {
        $text = Options::getParam('TASK_MENU_ITEMS');
        $menuItems = preg_split("/\r\n|\n|\r/", $text);

        $menuItems = array_values(array_filter($menuItems));

        return $menuItems ?: null;
    }
}
