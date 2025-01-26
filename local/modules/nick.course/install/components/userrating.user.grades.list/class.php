<?php

use Bitrix\Iblock\Iblock;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\SystemException;
use Nick\Course\Helper\Options;
use Bitrix\Main\Grid;
use Nick\Course\Model\Competence\CompetenceTable;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class UserGradesListComponent extends CBitrixComponent
{
    public const GRID_ID = 'NI_CO_USER_GRADES_LIST';
    private int $userCompetenceListId;
    private Grid\Options $gridOptions;

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
            $this->userCompetenceListId = Options::getParam('USER_COMPETENCE_LIST_ID');
            $this->gridOptions = new Bitrix\Main\Grid\Options(self::GRID_ID);

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
            (new CList($this->userCompetenceListId))->GetFields()
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

        $query = Iblock::wakeUp($this->userCompetenceListId)->getEntityDataClass()::query()
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
                $profileUrl = Option::get('intranet', 'path_user', '/company/personal/user/#USER_ID#/');
                $profileUrl = str_replace('#USER_ID#', $value, $profileUrl);
                $value = sprintf("<a href=\"%s\" bx-tooltip-user-id=\"%d\">%s</a>",
                    $profileUrl,
                    $value,
                    CCrmViewHelper::GetFormattedUserName($value));
            }
        });

        return $values;
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
                                    'VALUE' => 'nextComp',
                                    'NAME' => 'Указать след. компетенцию',
                                ],
                                [
                                    'VALUE' => 'prevComp',
                                    'NAME' => 'Указать пред. компетенцию',
                                    'ONCHANGE' => [
                                        [
                                            'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CREATE,
                                            'DATA' => [
                                                [
                                                    'TYPE' => Bitrix\Main\Grid\Panel\Types::TEXT,
                                                    'ID' => 'prevCompValueId',
                                                    'NAME' => 'prevCompValueName',
                                                    'VALUE' => '',
                                                    'SIZE' => 1,
                                                ],
                                                [
                                                    'TYPE' => Bitrix\Main\Grid\Panel\Types::BUTTON,
                                                    'TEXT' => 'Применить',
                                                    'ID' => 'prevCompValueButtonId',
                                                    'NAME' => 'prevCompValueButtonName',
                                                    'ONCHANGE' => [
                                                        [
                                                            'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
                                                            'DATA' => [
                                                                ['JS' => "UserRatingCompetenciesChange('prev', '" . self::GRID_ID . "');"]
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
            CIBlockElement::Delete($id);
        }
    }
}
