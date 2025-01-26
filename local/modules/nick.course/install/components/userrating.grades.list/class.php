<?php

use Bitrix\Highloadblock\HighloadBlock;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Grid\Panel\Types;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldTable;
use Nick\Course\Helper\Options;
use Bitrix\Main\Grid;
use Nick\Course\Model\Competence\CompetenceTable;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class GradesListGridComponent extends CBitrixComponent implements Controllerable, Errorable
{
    public const GRID_ID = 'NI_CO_LIST_GRADES';

    protected ErrorCollection $errorCollection;

    private Grid\Options $gridOptions;

    public function configureActions(): array
    {
        return [];
    }

    /**
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        $this->errorCollection = new ErrorCollection();

        return $arParams;
    }

    /**
     * @return void
     * @throws LoaderException
     * @throws Exception
     */
    public function executeComponent(): void
    {
        try {
            $this->checkModules();

            $this->processGridActions();

            $this->makeGridData();

            $this->makeGridParams();

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
        $this->gridOptions = new Grid\Options(self::GRID_ID);

        $this->arResult['GRID_ID'] = $this->getGridId();

        $this->arResult['COLUMNS'] = $this->prepareColumns();

        if (!empty($this->arResult['COLUMNS'])) {
            $gridSort = $this->gridOptions->GetSorting(['sort' => ['ID' => 'ASC']]);
            $this->arResult['ROWS'] = $this->prepareRows();

            if (empty($this->arResult['ROWS'])) {
                $this->arResult['STUB'] = [
                    'title' => 'Нет компетенций',
                    'description' => 'Приходите завтра',
                ];
            }
            $this->makeGridParams();
        }
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
        $defaultColumns = array_column($this->arResult['COLUMNS'], 'id');
        $visibleColumns = $this->gridOptions->GetUsedColumns($defaultColumns);

        $navParams = $this->gridOptions->GetNavParams();
        $nav = new Bitrix\Main\UI\PageNavigation(self::GRID_ID);
        $nav->allowAllRecords(true)
            ->setPageSize($navParams['nPageSize'])
            ->initFromUri();

        $hlBlockId = Options::getParam('GRADES_LIST_ID');

        $query = HighloadBlock::wakeUp($hlBlockId)->getEntityDataClass()::query()
            ->setSelect($visibleColumns)
            ->setLimit($nav->getLimit())
            ->setOffset($nav->getOffset());

        $totalCount = $query->queryCountTotal();
        $nav->setRecordCount($totalCount);

        $this->arResult['NAV_OBJECT'] = $nav;
        $this->arResult['CURRENT_PAGE'] = $nav->getCurrentPage();
        $this->arResult['TOTAL_ROWS_COUNT'] = $totalCount;

        /** @var Collection $grades */
        $grades = $query->fetchCollection();

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
                    'onclick' => "UserRatingDeleteGrade({$element->getId()}, '" . self::GRID_ID . "');"
                ],
            ],
        ], $grades->getAll());
    }

    protected function makeGridParams(): void
    {
        $this->arResult['SHOW_NAVIGATION_PANEL']=true;
        $this->arResult['PAGE_SIZES']=$this->preparePageSize();
        $this->arResult['SHOW_PAGESIZE']=true;
        $this->arResult['ENABLE_NEXT_PAGE']=true;
        $this->arResult['SHOW_MORE_BUTTON']=true;

        $this->arResult['SHOW_ACTION_PANEL']=true;
        $this->arResult['ACTION_PANEL']=$this->prepareActionPanel();
        //Чекбоксы и экшены
        $this->arResult['SHOW_SELECTED_COUNTER']=true;
        $this->arResult['SHOW_ROW_CHECKBOXES']=true;
    }

    protected function preparePageSize(): array
    {
        return [
            ['NAME' => '1', 'VALUE' => '1'],
            ['NAME' => '5', 'VALUE' => '5'],
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => 'все', 'VALUE' => '9999'],
        ];
    }

    public function prepareActionPanel()
    {
        $snippet = new Snippet();
        $actions = [
            'GROUPS' => [
                [
                    'ITEMS' => [
                        $snippet->getRemoveButton(),
                        //$snippet->getEditButton(),
                        [
                            'ID' => 'set-type',
                            'TYPE' => Bitrix\Main\Grid\Panel\Types::DROPDOWN,
                            'ITEMS' => [
                                [
                                    'VALUE' => '',
                                    'NAME' => '- Выбрать -'
                                ],
                                [
                                    'VALUE' => 'setGrade',
                                    'NAME' => 'Указать оценку',
                                    'ONCHANGE' => [
                                        [
                                            'ACTION' => Actions::CREATE,
                                            'DATA' => [
                                                [
                                                    'TYPE' => Bitrix\Main\Grid\Panel\Types::TEXT,
                                                    'ID' => 'setGradeValueId',
                                                    'NAME' => 'setGradeValueName',
                                                    'VALUE' => '',
                                                    'SIZE' => 1,
                                                ],
                                                [
                                                    'TYPE' => Types::BUTTON,
                                                    'TEXT' => 'Применить',
                                                    'ID' => 'setGradeValueButtonId',
                                                    'NAME' => 'setGradeValueButtonName',
                                                    'ONCHANGE' => [
                                                        [
                                                            'ACTION' => Actions::CALLBACK,
                                                            'DATA' => [
                                                                ['JS' => "UserRatingSetGrade('" . self::GRID_ID . "');"]
                                                            ]
                                                        ]
                                                    ]
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ]
                        ],
                    ],
                ]
            ]
        ];
        return $actions;
    }

    /**
     * @throws Exception
     */
    public function processGridActions(): void
    {
        $postAction = 'action_button_' . self::GRID_ID;

        if (!$this->isValidGridAction($postAction)) {
            return;
        }

        match ($this->request->getPost($postAction)) {
            'delete' => $this->processDelete(),
            default => null,
        };
    }

    private function isValidGridAction(string $actionName): bool
    {
        return $this->request->isPost()
            && $this->request->getPost($actionName)
            && check_bitrix_sessid();
    }

    /**
     * @throws Exception
     */
    public function processDelete(): void
    {
        if (!$this->request->getPost('ID')) {
            return;
        }
        foreach ($this->request->getPost('ID') as $id) {
            HighloadBlock::wakeUp(Options::getParam('GRADES_LIST_ID'))->getEntityDataClass()::delete($id);
        }
    }

    /**
     * Getting array of errors.
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    /**
     * Getting once error with the necessary code.
     * @param string $code Code of error.
     * @return Error
     */
    public function getErrorByCode($code): Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    /**
     * @param array $gridSelectedRows
     * @param array $values
     * @return array|null
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function setGradeAction(array $gridSelectedRows, array $values): ?array
    {
        Loader::requireModule('nick.course');
        Loader::requireModule('highloadblock');

        $hlBlockId = Options::getParam('GRADES_LIST_ID');

        $grades = HighloadBlock::wakeUp($hlBlockId)->getEntityDataClass()::query()
            ->whereIn('ID', array_values($gridSelectedRows))
            ->fetchCollection();

        $updatedCounter = 0;
        /** @var Collection $grades*/
        foreach ($grades as $grade) {
            $grade->set('UF_GRADE', $values['setGradeValueName']);
            $updatedCounter++;
        }
        $updateResult = $grades->save();

        if (!$updateResult->isSuccess()) {
            foreach ($updateResult->getErrors() as $error)
                $this->errorCollection[] = $error;
        }

        return ['updated' => $updatedCounter];
    }

    /**
     * @param int $gradeId
     * @return void
     * @throws LoaderException
     * @throws Exception
     */
    public function deleteGradeAction(int $gradeId): void
    {
        Loader::requireModule('nick.course');
        Loader::requireModule('highloadblock');

        $hlBlockId = Options::getParam('GRADES_LIST_ID');

        $deleteResult = HighloadBlock::wakeUp($hlBlockId)->getEntityDataClass()::delete($gradeId);

        if (!$deleteResult->isSuccess()) {
            foreach ($deleteResult->getErrors() as $error)
                $this->errorCollection[] = $error;
        }
    }
}
