<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;
use Nick\Course\Helper;

Loader::includeModule('nick.course');

require_once(Helper\Options::getModuleDir(true) . "/admin/nick_course_options.php");
