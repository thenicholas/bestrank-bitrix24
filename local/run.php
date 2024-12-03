<?php

use Bitrix\Main\Loader;
use Nick\Course\Handler\Epilog;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

Loader::requireModule('nick.course');

Epilog::includeJsLibraries();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
