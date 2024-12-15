<?php

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\SystemException;
use Nick\Course\Helper\Options;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class GradesListGridComponent extends CBitrixComponent
{
    public const GRID_ID = 'SU_SIMPLE_GRID_ID';

    // Параметры компонента

    /**
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        return $arParams;
    }

    //Конструктор компонента

    /**
     * @return void
     * @throws LoaderException
     */
    public function executeComponent(): void
    {
        try {
            // подключаем метод проверки подключения модуля «Информационные блоки»
            $this->checkModules();

            $this->makeGridData();

            //$this->makeGridParams();

            //Debug::dump($this->arResult);
            $this->IncludeComponentTemplate();
        } catch (SystemException $e) {
            ShowError($e->getMessage());
        }
    }

    /**
     * @return void
     * @throws SystemException
     * @throws LoaderException
     */
    protected function checkModules(): void
    {
        $arModules = [
            'nick.course',
            'ui'
        ];
        foreach ($arModules as $moduleName) {
            if (!Loader::includeModule($moduleName)) // выводим сообщение в catch
            {
                throw new SystemException(Loc::getMessage('SU_NO_MODULES', ['#MODULE_NAME#' => $moduleName]));
            }
        }
    }

    //Собираем сам грид

    /**
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function makeGridData(): void
    {
        $this->arResult['GRID_ID'] = $this->getGridId();
        //$gridOptions = new Bitrix\Main\Grid\Options(self::GRID_ID);
        //Debug::dump($gridOptions->getUsedColumns());
        $this->arResult['COLUMNS'] = $this->prepareColumns();
        $this->arResult['ROWS'] = $this->prepareRows();
    }

    //Отдаем ID грида

    /**
     * @return string
     */
    protected function getGridId(): string
    {
        return self::GRID_ID;
    }

    //Собираем колонки таблицы

    /**
     * @return array|array[]
     */
    protected function prepareColumns(): array
    {
        return [
            [
                'id' => 'ID',
                'name' => 'ID',
                'width' => 50,
                'default' => true,
                'type' => Type::INT
            ],
            [
                'id' => 'UF_ACTIVE',
                'name' => Loc::getMessage('NI_CO_UF_ACTIVE'),
                'width' => 150,
                'default' => true,
                'type' => Type::CHECKBOX
            ],
            [
                'id' => 'UF_GRADE',
                'name' => Loc::getMessage('NI_CO_UF_GRADE'),
                'default' => true,
                'type' => Type::INT
            ],
            [
                'id' => 'UF_CODE',
                'name' => Loc::getMessage('NI_CO_UF_CODE'),
                'default' => true,
                'type' => Type::TEXT
            ],
            [
                'id' => 'UF_TEXT',
                'name' => Loc::getMessage('NI_CO_UF_TEXT'),
                'default' => true,
                'type' => Type::TEXT
            ],
        ];
    }

    /**
     * @return string[]
     */
    protected function getFields(): array
    {
        return [
            'ID',
            'UF_ACTIVE',
            'UF_GRADE',
            'UF_CODE',
            'UF_TEXT',
        ];
    }

    /**
     * @param array $values
     * @return array
     */
    protected function makeColumns(array $values): array
    {
        $columns = [];
        foreach ($this->getFields() as $field) {
            $columns[$field] = $values[$field];
        }
        return $columns;
    }

    /**
     * @return array|array[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function prepareRows(): array
    {
        $hlblockId = Options::getParam('GRADES_LIST_ID');

        $grades = HighloadBlockTable::compileEntity($hlblockId)->getDataClass()::query()
            ->setSelect($this->getFields())
            ->fetchCollection();

        /** @var Collection $grades */
        return array_map(fn($element) => [
            'id' => ($values = $element->collectValues())['ID'],
            'columns' => $this->makeColumns($values),
            'actions' => [
                [
                    'text' => Loc::getMessage('NI_CO_EDIT_ACTION'),
                    'default' => true,
                    'onclick' => "console.log('Edit onclick')",
                ],
                [
                    'text' => Loc::getMessage('NI_CO_DELETE_ACTION'),
                    'default' => true,
                    'onclick' => "console.log('Delete onclick')",
                ],
            ],
        ], $grades->getAll());
    }
}
