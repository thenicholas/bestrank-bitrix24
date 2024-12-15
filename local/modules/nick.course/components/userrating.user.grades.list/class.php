<?php

use Bitrix\Iblock\Iblock;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\SystemException;
use Nick\Course\Helper\Options;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class UserGradesListComponent extends CBitrixComponent
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
        return
            [
                [
                    'id' => 'ID',
                    'name' => 'ID',
                    'width' => 50,
                    'default' => true,
                    'type' => Type::INT
                ],
                [
                    'id' => 'NAME',
                    'name' => 'Название',
                    'default' => true,
                    'type' => Type::TEXT
                ],
                [
                    'id' => 'USER_ID.VALUE',
                    'name' => 'Сотрудник',
                    'default' => true,
                    'type' => Type::INT
                ],
                [
                    'id' => 'COMPETENCE_ID.VALUE',
                    'name' => 'Компетенция',
                    'default' => true,
                    'type' => Type::INT
                ],
                [
                    'id' => 'GRADE_ID.VALUE',
                    'name' => 'Оценка',
                    'default' => true,
                    'type' => Type::INT
                ],
            ];
    }

    /**
     * @return array|array[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function prepareRows(): array
    {
        $hlblockId = Options::getParam('USER_COMPETENCE_LIST_ID');

        $grades = Iblock::wakeUp($hlblockId)->getEntityDataClass()::query()
            ->setSelect([
                    'ID',
                    'NAME',
                    'USER_ID.VALUE',
                    'COMPETENCE_ID.VALUE',
                    'GRADE_ID.VALUE',
                ])
            ->fetchCollection();

        /** @var Collection $grades */
        return array_map(fn($element) => [
            'id' => ($values = $element->collectValues(recursive: true))['ID'],
            'columns' => [
                'ID' => $values['ID'],
                'NAME' => $values['NAME'],
                'USER_ID.VALUE' => CCrmViewHelper::PrepareUserBaloonHtml([
                    'USER_ID' => $values['USER_ID']['VALUE'],
                    'USER_NAME' => $this->getUserNameById($values['USER_ID']['VALUE']),
                ]),
                'COMPETENCE_ID.VALUE' => $values['COMPETENCE_ID']['VALUE'],
                'GRADE_ID.VALUE' => $values['GRADE_ID']['VALUE']
            ],
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

    private static function getUserNameById(int $userId): string
    {
        if ($userId <= 0) {
            return '';
        }

        $user = CUser::GetByID($userId)->Fetch();
        if (!$user) {
            return '';
        }

        return trim(sprintf('%s %s',
            $user['NAME'] ?? '',
            $user['LAST_NAME'] ?? ''
        )) ?: $user['LOGIN'];
    }
}
