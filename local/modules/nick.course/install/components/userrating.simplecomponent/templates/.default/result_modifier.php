<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\Diag\debug::dump('result_modifier.php');

#В файле доступны языковые фразы шаблона компонента и следующие переменные:

#$arParams - параметры, чтение, изменение. Не затрагивает одноименный член компонента, но изменения тут влияют на $arParams в файле template.php.
#$arResult — результат, чтение/изменение. Затрагивает одноименный член класса компонента.
#$APPLICATION, $USER, $DB - объявлять их как global избыточно, они уже доступны по-умолчанию.
#$this — ссылка на текущий шаблон (объект, описывающий шаблон, тип CBitrixComponentTemplate)


$arResult['NEW_ITEMS'] = [
    'new'=>'fgdsdfgdsfg'
];