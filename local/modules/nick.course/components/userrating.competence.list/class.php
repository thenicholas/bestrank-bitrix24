<?php

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\Grid\Options;
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
    public const GRID_ID = 'NI_CO_COMPETENCE_LIST';

    private int $userCompetenceListId;

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
     * @throws LoaderException
     */
    public function executeComponent(): void
    {
        try {
            $this->checkModules();

            $this->makeGridData();

            $this->IncludeComponentTemplate();
        } catch (SystemException $e) {
            ShowError($e->getMessage());
        }
    }

    /**
     * @throws LoaderException
     */
    protected function checkModules(): void
    {
        $arModules = [
            'nick.course',
            'ui'
        ];
        foreach ($arModules as $moduleName) {
            Loader::requireModule($moduleName);
        }
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
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
     * @throws SystemException
     * @throws ArgumentException
     */
    protected function prepareColumns(): array
    {
        return array_map(
            fn($field) => [
                'id' => $field->getName(),
                'name' => $field->getTitle(),
                'default' => $field->isRequired(),
                'class' => 'competence-field competence-field-' . $field->getName(),
                'editable' => true,
                'type' => match ($field->getDataType()) {
                    'integer' => Type::INT,
                    'datetime', 'date' => Type::DATE,
                    default => Type::TEXT,
                }
            ],
            CompetenceTable::getEntity()->getScalarFields()
        );
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function prepareRows(): array
    {
        $visibleColumns = (new Options(self::GRID_ID))->GetUsedColumns();

        $competencies = CompetenceTable::query()
            ->setSelect($visibleColumns)
            ->fetchCollection();

        /** @var Collection $competencies */

        return array_map(fn($element) => [
            'id' => ($values = $element->collectValues())['ID'],
            'columns' => $values,
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
