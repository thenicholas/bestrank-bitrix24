<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\SystemException;



if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CompetenceListComponent extends CBitrixComponent
{
    // Параметры компонента
    public function onPrepareComponentParams($arParams)
    {

        return $arParams;
    }

    //Конструктор компонента
    public function executeComponent()
    {
        try {
            // подключаем метод проверки подключения модуля «Информационные блоки»
            $this->checkModules();

            Debug::dump($this->arParams, '$this->arParams - class.php');
            Debug::dump($this->arResult, '$this->arResult - class.php');
            $this->arResult['ITEMS']=[
                0=>[
                    'id'=>1,
                    'name'=>'test'
                ]
            ];

            $this->IncludeComponentTemplate();
        } catch (SystemException $e) {
            ShowError($e->getMessage());
        }
    }

    protected function checkModules()
    {
        // если модуль не подключен
        if (!Loader::includeModule('study.userrating'))
            // выводим сообщение в catch
            throw new SystemException(Loc::getMessage('SU_NO_MODULES'));
    }
}