<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;
use Nick\Course\Helper;

Loader::requireModule('nick.course');

require_once(Helper\Options::getModuleDir(true) . "/admin/nick_course_grades.php");
