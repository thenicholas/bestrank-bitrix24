<?php

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\SystemException;
use Nick\Course\Model\Competence\CompetenceTable;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class CompetenceListComponent extends CBitrixComponent
{
    public const GRID_ID = 'SU_SIMPLE_GRID_ID';

    // Параметры компонента
    public function onPrepareComponentParams($arParams): array
    {
        return $arParams;
    }

    //Конструктор компонента

    /**
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
     * @throws LoaderException
     * @throws SystemException
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
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
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
    protected function getGridId(): string
    {
        return self::GRID_ID;
    }

    //Собираем колонки таблицы
    protected function prepareColumns(): array
    {
        return [
            [
                'id' => 'ID',
                'name' => 'ID',
                "default" => true,
                'type' => Type::INT
            ],
            [
                'id' => 'NAME',
                'name' => Loc::getMessage('NI_CO_NAME_FIELD'),
                "default" => true,
                'type' => Type::TEXT
            ],
            [
                'id' => 'DESCRIPTION',
                'name' => Loc::getMessage('NI_CO_DESCRIPTION_FIELD'),
                'default' => true,
                'type' => Type::TEXT
            ],
            [
                'id' => 'CREATE_DATE',
                'name' => Loc::getMessage('NI_CO_CREATE_DATE_FIELD'),
                'default' => true,
                'type' => Type::DATE
            ],
            [
                'id' => 'PREV_COMPETENCE_ID',
                'name' => Loc::getMessage('NI_CO_PREV_COMPETENCE_ID_FIELD'),
                'default' => true,
                'type' => Type::INT
            ],
            [
                'id' => 'NEXT_COMPETENCE_ID',
                'name' => Loc::getMessage('NI_CO_NEXT_COMPETENCE_ID_FIELD'),
                'default' => true,
                'type' => Type::INT
            ],
        ];
    }

    protected function getFields(): array
    {
        return [
            'ID',
            'NAME',
            'DESCRIPTION',
            'CREATE_DATE',
            'PREV_COMPETENCE_ID',
            'NEXT_COMPETENCE_ID',
        ];
    }

    protected function makeColumns(array $values): array
    {
        $columns = [];
        foreach ($this->getFields() as $field) {
            $columns[$field] = $values[$field];
        }
        return $columns;
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function prepareRows(): array
    {
        $competencies = CompetenceTable::query()
            ->setSelect($this->getFields())
            ->fetchCollection();

        /** @var Collection $competencies */
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
        ], $competencies->getAll());
    }
}
