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
use Bitrix\Main\UserFieldTable;
use Nick\Course\Helper\Options;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class GradesListGridComponent extends CBitrixComponent
{
    public const GRID_ID = 'NI_CO_LIST_GRADES';

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
        $this->arResult['COLUMNS'] = $this->prepareColumns();
        $this->arResult['ROWS'] = $this->prepareRows();
    }


    /**
     * @return string
     */
    protected function getGridId(): string
    {
        return self::GRID_ID;
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function prepareColumns(): array
    {
        $hlBlockId = Options::getParam('GRADES_LIST_ID');
        $entity = HighloadBlockTable::compileEntity($hlBlockId);

        $userFields = UserFieldTable::query()
            ->setFilter(['ENTITY_ID' => HighloadBlockTable::compileEntityId($hlBlockId)])
            ->setSelect([
                'FIELD_NAME',
                'LIST_COLUMN_LABEL' => 'LABELS.LIST_COLUMN_LABEL'
            ])
            ->registerRuntimeField(
                UserFieldTable::getLabelsReference(null, LANGUAGE_ID)
            )
            ->fetchAll();

        $fieldLabels = array_column($userFields, 'LIST_COLUMN_LABEL', 'FIELD_NAME');

        return array_map(
            function($field) use ($fieldLabels) {
                $fieldName = $field->getName();
                return [
                    'id' => $fieldName,
                    'name' => $fieldLabels[$fieldName] ?: $fieldName,
                    'default' => true,
                    'type' => match ($field->getDataType()) {
                        'boolean' => Type::CHECKBOX,
                        'integer' => Type::INT,
                        'double' => Type::NUMBER,
                        'datetime', 'date' => Type::DATE,
                        default => Type::TEXT,
                    }
                ];
            },
            $entity->getFields()
        );
    }

    /**
     * @return array|array[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function prepareRows(): array
    {
        $gridOptions = new \Bitrix\Main\Grid\Options(self::GRID_ID);
        $visibleColumns = $gridOptions->GetUsedColumns();

        $hlBlockId = Options::getParam('GRADES_LIST_ID');

        $grades = HighloadBlockTable::compileEntity($hlBlockId)->getDataClass()::query()
            ->setSelect($visibleColumns)
            ->fetchCollection();

        /** @var Collection $grades */

        return array_map(fn($element) => [
            'id' => ($element->getId()),
            'columns' => $element->collectValues(),
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
