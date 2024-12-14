<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/task_detail_more_menu.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
];
