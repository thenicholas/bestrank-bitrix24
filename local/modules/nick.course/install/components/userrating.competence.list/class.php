<?php

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\Grid\Options;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Grid\Panel\Types;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\SystemException;
use Nick\Course\Model\Competence\CompetenceTable;
use Bitrix\Main\Error;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class CompetenceListComponent extends CBitrixComponent implements Controllerable, Errorable
{
    public const GRID_ID = 'NI_CO_COMPETENCE_LIST';

    private $gridOptions;

    const EDIT_PATH = '#';

    protected ErrorCollection $errorCollection;

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
        $this->gridOptions = new Bitrix\Main\Grid\Options(self::GRID_ID);

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
        $defaultColumns = array_column($this->arResult['COLUMNS'], 'id');
        $visibleColumns = (new Options(self::GRID_ID))->GetUsedColumns($defaultColumns);

        $navParams = $this->gridOptions->GetNavParams();
        $nav = new Bitrix\Main\UI\PageNavigation(self::GRID_ID);
        $nav->allowAllRecords(true)
            ->setPageSize($navParams['nPageSize'])
            ->initFromUri();

        $query = CompetenceTable::query()
            ->setSelect($visibleColumns)
            ->addSelect('PREV_COMPETENCE.NAME')
            ->addSelect('NEXT_COMPETENCE.NAME')
            ->setLimit($nav->getLimit())
            ->setOffset($nav->getOffset());

        $totalCount = $query->queryCountTotal();
        $nav->setRecordCount($totalCount);

        $this->arResult['NAV_OBJECT'] = $nav;
        $this->arResult['CURRENT_PAGE'] = $nav->getCurrentPage();
        $this->arResult['TOTAL_ROWS_COUNT'] = $totalCount;

        /** @var Collection $competencies */
        $competencies = $query->fetchCollection();

        return array_map(fn($element) => [
            'id' => ($columnValues = $element->collectValues())['ID'],
            'columns' => $this->prepareRow($columnValues),
            'actions' => [
                [
                    'text' => Loc::getMessage('NI_CO_SHOW_ACTION'),
                    'default' => true,
                    'onclick' => "console.log('Show onclick')",
                ],
                [
                    'text' => Loc::getMessage('NI_CO_EDIT_ACTION'),
                    'default' => true,
                    'onclick' => "console.log('Edit onclick')",
                ],
                [
                    'text' => Loc::getMessage('NI_CO_DELETE_ACTION'),
                    'default' => true,
                    'onclick' => "UserRatingDeleteCompetence({$element->getId()}, '" . self::GRID_ID . "');"
                ],
            ],
        ], $competencies->getAll());
    }

    protected function prepareRow($columnValues): array
    {
        $row = [];

        foreach ($columnValues as $columnName => $columnValue) {
            $row[$columnName] = match ($columnName) {
                'NAME' => $this->formatLink(
                    self::EDIT_PATH . $columnValues['ID'],
                    $columnValue
                ),
                'NEXT_COMPETENCE_ID' => $this->formatLink(
                    self::EDIT_PATH . $columnValue,
                    $columnValues['NEXT_COMPETENCE']?->getName()
                ),
                'PREV_COMPETENCE_ID' => $this->formatLink(
                    self::EDIT_PATH . $columnValue,
                    $columnValues['PREV_COMPETENCE']?->getName()
                ),
                default => $columnValue ?? '',
            };
        }

        return $row;
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

    protected function makeGridParams(): void
    {
        $this->arResult['SHOW_NAVIGATION_PANEL'] = true;
        $this->arResult['PAGE_SIZES'] = $this->preparePageSize();
        $this->arResult['SHOW_PAGESIZE'] = true;
        $this->arResult['ENABLE_NEXT_PAGE'] = true;
        $this->arResult['SHOW_MORE_BUTTON'] = true;

        $this->arResult['SHOW_ACTION_PANEL'] = true;
        $this->arResult['ACTION_PANEL'] = $this->prepareActionPanel();
        //Чекбоксы и экшены
        $this->arResult['SHOW_SELECTED_COUNTER'] = true;
        $this->arResult['SHOW_ROW_CHECKBOXES'] = true;
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
                                    'VALUE' => 'nextCompetence',
                                    'NAME' => 'Указать след. компетенцию',
                                    'ONCHANGE' => [
                                        [
                                            'ACTION' => Actions::CREATE,
                                            'DATA' => [
                                                [
                                                    'TYPE' => Types::TEXT,
                                                    'ID' => 'nextCompetenceValueId',
                                                    'NAME' => 'nextCompetence',
                                                    'VALUE' => '',
                                                    'SIZE' => 1,
                                                ],
                                                [
                                                    'TYPE' => Types::BUTTON,
                                                    'TEXT' => 'Применить',
                                                    'ID' => 'nextCompetenceValueButtonId',
                                                    'NAME' => 'nextCompetenceValueButtonName',
                                                    'ONCHANGE' => [
                                                        [
                                                            'ACTION' => Actions::CALLBACK,
                                                            'DATA' => [
                                                                ['JS' => "UserRatingCompetenceChange('setCompetence', '" . self::GRID_ID . "');"]
                                                            ]
                                                        ]
                                                    ]
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'VALUE' => 'previousCompetence',
                                    'NAME' => 'Указать пред. компетенцию',
                                    'ONCHANGE' => [
                                        [
                                            'ACTION' => Actions::CREATE,
                                            'DATA' => [
                                                [
                                                    'TYPE' => Types::TEXT,
                                                    'ID' => 'previousCompetenceValueId',
                                                    'NAME' => 'previousCompetence',
                                                    'VALUE' => '',
                                                    'SIZE' => 1,
                                                ],
                                                [
                                                    'TYPE' => Types::BUTTON,
                                                    'TEXT' => 'Применить',
                                                    'ID' => 'previousCompetenceValueButtonId',
                                                    'NAME' => 'previousCompetenceValueButtonName',
                                                    'ONCHANGE' => [
                                                        [
                                                            'ACTION' => Actions::CALLBACK,
                                                            'DATA' => [
                                                                ['JS' => "UserRatingCompetenceChange('setCompetence', '" . self::GRID_ID . "');"]
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
            CompetenceTable::delete($id);
        }
    }

    /**
     * @param array $data
     * @return array|null
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function setCompetenceAction(array $data): ?array
    {
        Loader::requireModule('nick.course');

        $competencies = CompetenceTable::query()
            ->whereIn('ID',$data['gridSelectedRows'])
            ->fetchCollection();

        $matchResult = match ($data['values'][0]) {
            'previousCompetence' => ['PREV_COMPETENCE_ID', 'previousCompetence'],
            'nextCompetence' => ['NEXT_COMPETENCE_ID', 'nextCompetence'],
            default => null
        };

        if ($matchResult === null) {
            $this->errorCollection[] = (new Error('Неверный тип компетенции'));
            return null;
        }

        [$competenceFieldName, $competenceValueName] = $matchResult;

        $updatedCounter = 0;
        /** @var $competencies Collection*/
        foreach ($competencies as $competence ) {
            $competence->set($competenceFieldName, $data['values'][$competenceValueName]);

            $result = $competencies->save();

            if (!$result->isSuccess()) {
                foreach ($result->getErrors() as $error)
                    $this->errorCollection[] = $error;
            } else {
                $updatedCounter++;
            }
        }

        return [
            'updated' => $updatedCounter,
            'errors' => count($this->getErrors())
        ];
    }

    /**
     * @param int $competenceId
     * @return void
     * @throws LoaderException
     */
    public function deleteCompetenceAction(int $competenceId): void
    {
        Loader::requireModule('nick.course');

        $deleteResult = CompetenceTable::delete($competenceId);

        if (!$deleteResult->isSuccess()) {
            foreach ($deleteResult->getErrors() as $error)
                $this->errorCollection[] = $error;
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
}
