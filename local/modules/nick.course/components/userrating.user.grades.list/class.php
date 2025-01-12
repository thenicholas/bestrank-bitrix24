<?php

use Bitrix\Crm\Service\Container;
use Bitrix\Iblock\Iblock;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\SystemException;
use Nick\Course\Helper\Options;
use Bitrix\Main\Grid;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class UserGradesListComponent extends CBitrixComponent
{
    public const GRID_ID = 'NI_CO_USER_GRADES_LIST';
        private int $userCompetencelistId;

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
            $this->userCompetencelistId = Options::getParam('USER_COMPETENCE_LIST_ID');
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

    //Отдаем ID грида

    /**
     * @return string
     */
    protected function getGridId(): string
    {
        return self::GRID_ID;
    }


        protected function prepareColumns(): array
    {
        return array_map(
            fn($field) => [
                'id' => $field['CODE'] ?? $field['FIELD_ID'],
                'name' => $field['NAME'],
                'default' => true,
                'class' => 'competence-field competence-field-' . $field['NAME'],
                'editable' => true,
                'type' => match ($field['TYPE']) {
                    'N' => Type::INT,
                    'ACTIVE_TO', 'DATE_CREATE', 'TIMESTAMP_X', 'S:Date', 'S:DateTime' => Type::DATE,
                    'S:employee' => Type::CUSTOM,
                    default => Type::TEXT,
                }
            ],
            (new CList($this->userCompetencelistId))->GetFields()
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
        $defaultColumns = array_column($this->arResult['COLUMNS'], 'id');
        $visibleColumns = (new Grid\Options(self::GRID_ID))->GetUsedColumns($defaultColumns);

        $grades = Iblock::wakeUp($this->userCompetencelistId)->getEntityDataClass()::query()
            ->setSelect($visibleColumns)
            ->fetchCollection();

        /** @var Collection $grades */
        return array_map(fn($element) => [
            'id' => ($element->getId()),
            'columns' => $this->prepareRow($element),
            'actions' => [
                [
                    'text' => 'Редактировать',
                    'default' => true,
                    'onclick' => "console.log('Edit onclick')",
                ],
                [
                    'text' => 'Удалить',
                    'default' => true,
                    'onclick' => "console.log('Delete onclick')",
                ],
            ],
        ], $grades->getAll());
    }

    private function prepareRow($element): array
    {
        $values = $element->collectValues(recursive: true);

        array_walk($values, function(&$value, $key) {
            $value = is_array($value) && isset($value['VALUE']) ? $value['VALUE'] : $value;

            if ($key === 'USER_ID') {
                $profileUrl = Container::getInstance()
                    ->getRouter()->getUserPersonalUrl($value);
                $value = sprintf("<a href=\"%s\" bx-tooltip-user-id=\"%d\">%s</a>",
                    $profileUrl,
                    $value,
                    CCrmViewHelper::GetFormattedUserName($value));
            }
        });

        return $values;
    }
}
