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
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldTable;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Nick\Course\Helper\Options;
use Bitrix\Main\Grid;
use Bitrix\Main\UI\Filter;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class GradesListGridComponent extends CBitrixComponent implements Controllerable, Errorable
{
    public const GRID_ID = 'NI_CO_LIST_GRADES';
    const EDIT_PATH = '/detail/grade.php?grade_id=';

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

        $this->makeFilter();
        $this->makeToolbar();

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
                $baseField = [
                    'id' => $fieldName,
                    'name' => $fieldLabels[$fieldName] ?: $fieldName,
                    'editable' => match ($field->getName()) {
                        'ID' => false,
                        default => true
                    },
                    'default' => true,
                ];

                $additionalFields = match ($field->getDataType()) {
                    'boolean' => ['type' => Type::CHECKBOX],
                    'integer' => ['type' => Type::INT],
                    'double' => ['type' => Type::NUMBER],
                    'datetime', 'date' => ['type' => Type::DATE],
                    default => ['type' => Type::TEXT],
                };

                return array_merge($baseField, $additionalFields);
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

        $filterOption = new Filter\Options(self::GRID_ID);
        $filter = $filterOption->getFilterLogic($this->arResult['FILTER']);

        $query = HighloadBlock::wakeUp($hlBlockId)->getEntityDataClass()::query()
            ->setSelect($visibleColumns)
            ->setLimit($nav->getLimit())
            ->setOffset($nav->getOffset())
            ->setFilter($filter);

        $totalCount = $query->queryCountTotal();
        $nav->setRecordCount($totalCount);

        $this->arResult['NAV_OBJECT'] = $nav;
        $this->arResult['CURRENT_PAGE'] = $nav->getCurrentPage();
        $this->arResult['TOTAL_ROWS_COUNT'] = $totalCount;

        /** @var Collection $grades */
        $grades = $query->fetchCollection();

        return array_map(function($element) {
            $values = $element->collectValues();

            foreach ($values as $key => $value) {
                if (isset($this->arResult['COLUMNS'][$key]) &&
                    $this->arResult['COLUMNS'][$key]['type'] === Type::CHECKBOX) {
                    $values[$key] = $value == 1 ? 'Y' : 'N';
                }
                if ($key === 'ID') {
                    $values[$key] = $this->formatLink(
                        self::EDIT_PATH  . $value,
                        $value
                    );
                }
            }

            return [
                'id' => ($element->getId()),
                'columns' => $values,
                'data' => $this->prepareRowData($values),
                'actions' => [
                    [
                        'text' => Loc::getMessage('NI_CO_SHOW_ACTION'),
                        'default' => true,
                        'href' => self::EDIT_PATH . $element->getId(),
                    ],
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
            ];
        }, $grades->getAll());
    }

    protected function prepareRowData($columnValues): array
    {
        $row = [];

        foreach ($columnValues as $fieldName => $fieldValue) {
            $row[$fieldName] = match ($this->arResult['COLUMNS'][$fieldName]['type']) {
                Type::DATE => $fieldValue->toString(),
                default => $fieldValue,
            };
        }

        return $row;
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
                        $snippet->getEditButton(),
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
            'delete' => $this->processDeleteAction(),
            'edit' => $this->processEditAction(),
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
    public function processDeleteAction(): void
    {
        if (!$this->request->getPost('ID')) {
            return;
        }
        foreach ($this->request->getPost('ID') as $id) {
            HighloadBlock::wakeUp(Options::getParam('GRADES_LIST_ID'))->getEntityDataClass()::delete($id);
        }
    }
    /**
     * @throws Exception
     */
    public function processEditAction(): void
    {
        $fields = $this->request->getPost('FIELDS');
        if (empty($fields)) {
            return;
        }

        $hlBlockId = Options::getParam('GRADES_LIST_ID');
        $entity = HighloadBlockTable::compileEntity($hlBlockId);

        // Получаем поля по типам
        $entityFields = $entity->getFields();
        $dateFields = array_filter($entityFields, fn($field) => $field instanceof DateField);
        $boolFields = array_filter($entityFields, fn($field) => $field instanceof BooleanField);

        $dateFieldNames = array_keys($dateFields);
        $boolFieldNames = array_keys($boolFields);

        $dataClass = HighloadBlock::wakeUp($hlBlockId)->getEntityDataClass();

        foreach ($fields as $elementId => $elementFields) {
            // Обработка дат
            foreach (array_intersect(array_keys($elementFields), $dateFieldNames) as $fieldName) {
                $elementFields[$fieldName] = new \Bitrix\Main\Type\DateTime($elementFields[$fieldName]);
            }

            // Обработка булевых значений
            foreach (array_intersect(array_keys($elementFields), $boolFieldNames) as $fieldName) {
                $elementFields[$fieldName] = $elementFields[$fieldName] === 'Y' ? 1 : 0;
            }

            $result = $dataClass::update($elementId, $elementFields);
            if (!$result->isSuccess()) {
                $this->errorCollection->add($result->getErrors());
                return;
            }
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

    public function makeFilter()
    {
        $this->arResult['FILTER_ID'] = self::GRID_ID;

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

        $this->arResult['FILTER'] = array_map(
            function($field) use ($fieldLabels) {
                $fieldName = $field->getName();
                $baseField = [
                    'id' => $fieldName,
                    'name' => $fieldLabels[$fieldName] ?: $fieldName,
                    'default' => true,
                    ];

                $type = match ($field->getDataType()) {
                    'integer' => Filter\FieldAdapter::NUMBER,
                    'date', 'datetime' => Filter\FieldAdapter::DATE,
                    'boolean' => Filter\FieldAdapter::CHECKBOX,
                    'list' => Filter\FieldAdapter::LIST,
                    default => Filter\FieldAdapter::STRING,
                };

                $additionalFields = match ($field->getDataType()) {
                    'boolean' => [
                        'valueType' => 'numeric'
                    ],
                    'list' => [
                        'items' => [
                            '' => 'Любой',
                            'P' => 'Поступление',
                            'M' => 'Списание'
                        ],
                        'params' => ['multiple' => 'Y']
                    ],
                    'string' => [
                        'filterable' => '?'
                    ],
                    default => []
                };

                return array_merge($baseField, ['type' => $type], $additionalFields);
            },
            $entity->getFields()
        );
    }

    public function makeToolbar()
    {
        $linkButton = new \Bitrix\UI\Buttons\CreateButton([
            'link' => self::EDIT_PATH,
        ]);
        Toolbar::addButton($linkButton);
        Toolbar::addFilter([
            'FILTER_ID' => $this->arResult['FILTER_ID'],
            'GRID_ID' => $this->arResult['GRID_ID'],
            'FILTER' => $this->arResult['FILTER'],
            'ENABLE_LABEL' => true,
            'ENABLE_LIVE_SEARCH' => true,
            'DISABLE_SEARCH' => false,
            //Группировка полей
            'HEADERS_SECTIONS' => $this->arResult['HEADERS_SECTIONS'],
            'ENABLE_FIELDS_SEARCH' => true,
            'COMPACT_STATE' => true,
            'FILTER_PRESETS' => $this->arResult['FILTER_PRESETS'],
            'THEME' => Filter\Theme::ROUNDED,
            /*'CONFIG' => [
                'AUTOFOCUS' => false,
            ],*/
            // LAZY_LOAD
            // VALUE_REQUIRED
            // ENABLE_ADDITIONAL_FILTERS
            // MESSAGES
            // RESET_TO_DEFAULT_MODE
            // VALUE_REQUIRED_MODE
            // COMPACT_STATE
            // FILTER_ROWS
            // COMMON_PRESETS_ID
            // RENDER_FILTER_INTO_VIEW
            // RENDER_FILTER_INTO_VIEW_SORT
        ]);
    }

    private function formatLink(?string $href, ?string $text): string
    {
        if (empty($href) || empty($text)) {
            return '';
        }

        return sprintf(
            '<a href="%s">%s</a>',
            $href,
            $text
        );
    }
}
