<?php

use Bitrix\Iblock\EO_ElementProperty_Query;
use Bitrix\Iblock\Iblock;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\UI\EntityForm\Control\Type;
use Nick\Course\Helper\Options;
use Nick\Course\Model\Competence\CompetenceTable;

class UserGradeDetailComponent extends CBitrixComponent
{
    private int $entityID;
    /**
     * @var \Bitrix\Main\ORM\Fields\ScalarField[]
     */
    private array $fieldsMap;
    private array $entityData;
    private int $userCompetenceListId;
    private string $iblockId;

    public function onPrepareComponentParams($arParams)
    {
        if (isset($arParams['ENTITY_ID'])
            && $arParams['ENTITY_ID'] != ''
            && (int)$arParams['ENTITY_ID'] > 0) {
            $this->setEntityId($arParams['ENTITY_ID']);
        } else {
            ShowError(Loc::getMessage('SU_ENTITY_ID_ERROR'));
            Application::getInstance()->terminate();
        }

        // подключаем метод проверки подключения модуля «Информационные блоки»
        $this->checkModules();

        $this->userCompetenceListId = Options::getParam('USER_COMPETENCE_LIST_ID');
        $this->iblockId = Options::getParam('USER_COMPETENCE_LIST_ID');


        //return parent::onPrepareComponentParams($arParams);


        return $arParams;
    }

    public function setEntityID($entityID)
    {
        $this->entityID = (int)$entityID;
    }

    //Конструктор компонента
    public function executeComponent()
    {
        try {
            $this->arResult['FORM'] = [
                //Редактирование разделов карточки
                'ENABLE_SECTION_EDIT' => true,
                //Создание разделов карточки
                'ENABLE_SECTION_CREATION' => true,
                //Перетаскивание разделов карточки
                'ENABLE_SECTION_DRAG_DROP' => true,
                //Дополнительное меню полей
                'ENABLE_FIELDS_CONTEXT_MENU' => true,
                //Изменение личной настройки карточки
                'ENABLE_PERSONAL_CONFIGURATION_UPDATE' => true,
                //Изменение общей настройки карточки
                'ENABLE_COMMON_CONFIGURATION_UPDATE' => true,
                //Сохранять для всех
                'ENABLE_SETTINGS_FOR_ALL' => true,
                //Ajax форма
                'ENABLE_AJAX_FORM' => true,
                //Разрешить перетаскивание полей
                'ENABLE_FIELD_DRAG_DROP' => true,
                //Режим чтения
                'READ_ONLY' => false,
                //Переключение режимов отображения
                'ENABLE_MODE_TOGGLE' => true,
                //Нижняя панель
                'ENABLE_BOTTOM_PANEL' => true,
                //Создание полей
                'ENABLE_USER_FIELD_CREATION' => true,
                //Панель инструментов
                'ENABLE_TOOL_PANEL' => true,
                //Селектор выбора типа отображения карточки
                'ENABLE_CONFIG_CONTROL' => true,
            ];

            $this->arResult['FORM']['ENTITY_ID'] = $this->entityID;
            $this->prepareFieldsMap();


            $this->initializeData();

            $initMode = $this->request->get('init_mode');
            if (!is_string($initMode)) {
                $initMode = '';
            } else {
                $initMode = mb_strtolower($initMode);
                if ($initMode !== 'edit' && $initMode !== 'view') {
                    $initMode = '';
                }
            }

            $this->arResult['FORM']['INITIAL_MODE'] = $initMode !== '' ? $initMode : ($this->entityID > 0 ? 'view' : 'edit');

            //region GUID
            $this->arResult['FORM']['GUID'] = $this->arParams['GUID'] ?? "grade_{$this->entityID}_details";
            $this->guid = $this->arResult['FORM']['GUID'];

            $this->arResult['FORM']['CONFIG_ID'] = $this->arParams['CONFIG_ID'] ?? $this->getDefaultConfigID();
            //endregion

            //region Fields
            $this->arResult['FORM']['ENTITY_FIELDS'] = $this->entityFieldInfos;
            //endregion
            //region Data
            $this->arResult['FORM']['ENTITY_DATA'] = $this->entityData;
            //endregion


            $this->arResult['FORM']['COMPONENT_AJAX_DATA'] = [
                'COMPONENT_NAME' => $this->getName(),
                //'ACTION_NAME' => 'save',
                //'SIGNED_PARAMETERS' => $this->getSignedParameters(),
            ];
            $this->arResult['FORM']['ENTITY_ID'] = $this->arResult['FORM']['INITIAL_MODE'] == 'view'
                ? $this->arResult['FORM']['ENTITY_ID']
                : 0;
            //region Config
            $this->prepareConfiguration();

            //endregion

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
    protected function checkModules()
    {
        Loader::requireModule('lists');
        // если модуль не подключен
        if (!Loader::includeModule('nick.course')) // выводим сообщение в catch
        {
            throw new SystemException(Loc::getMessage('SU_NO_MODULES'));
        }
    }

    protected function prepareFieldsMap()
    {
        if (!isset($this->fieldsMap)) {
            $this->fieldsMap = (new CList($this->userCompetenceListId))->GetFields();

        }
    }

    protected function initializeData()
    {
        $this->prepareEntityData();
        $this->prepareFieldInfos();
    }

    protected function prepareEntityData()
    {
        if (isset($this->entityData)) {
            return $this->entityData;
        }

        $elementValues = CIBlockElement::GetList(
            [],
            ['ID' => $this->entityID],
            false,
            false,
            array_keys($this->fieldsMap)
        )->Fetch();

        $entityData = [];

        foreach ($this->fieldsMap as $fieldName => $field) {
            $entityData[$fieldName] = match ($field['PROPERTY_TYPE']) {
                'S', 'N' => $elementValues[$fieldName . '_VALUE'],
                default => $elementValues[$fieldName]
            };
        }
        $this->entityData = $entityData;
    }

    protected function prepareFieldInfos(): array
    {
        if (isset($this->entityFieldInfos)) {
            return $this->entityFieldInfos;
        }

        $this->entityFieldInfos = [];

        $this->entityFieldInfos = array_map(
            fn($field) => [
                'name' => $field['FIELD_ID'],
                'title' => $field['NAME'],
                'required' => $field['IS_REQUIRED'],
                'defaultValue' => $field['DEFAULT_VALUE'],
                'editable' => true,
                'type' => match ($field['TYPE']) {
                    'N' => Type::NUMBER,
                    'ACTIVE_TO', 'DATE_CREATE', 'TIMESTAMP_X', 'S:Date', 'S:DateTime' => Type::DATETIME,
                    'S:employee' => Type::NUMBER, // todo: реализовать корреткное отображение пользователя
                    default => Type::TEXT,
                },
                'settings' => [],
                'data' => [],
            ],
            array_filter((new CList($this->userCompetenceListId))->GetFields(), fn ($field) => !$field['IS_PRIMARY'])
        );

        return $this->entityFieldInfos;
    }

    public function getDefaultConfigID(): string
    {
        return 'user_grade_details';
    }

    public function prepareConfiguration()
    {
        if (isset($this->arResult['FORM']['ENTITY_CONFIG'])) {
            return $this->arResult['FORM']['ENTITY_CONFIG'];
        }

        //Одна колонка в форме
        //$this->prepareOneColumn();
        //Несколько колонок в форме
        $this->prepareNColumn();
    }

    protected function prepareOneColumn()
    {
        $section = [
            'name' => 'main',
            'type' => Type::SECTION,
            'title' => 'Поля',
            'elements' => []
        ];
        foreach ($this->fieldsMap as $field) {
            if ($field['IS_PRIMARY']) {
                continue;
            }

            $configItem = [
                'name' => $field['NAME']
            ];
            $section['elements'][] = $configItem;
        }
        $this->arResult['FORM']['ENTITY_CONFIG'][] = $section;
        return $this->arResult['FORM']['ENTITY_CONFIG'];
    }

    protected function prepareNColumn()
    {
        $arColumns = [
            [
                'name' => 'left',
                'type' => Type::COLUMN,
                'data' => [
                    'width' => 30
                ],
                'elements' => [
                    [
                        'name' => 'main',
                        'type' => Type::SECTION,
                        'title' => 'Поля',
                        'elements' => [
                            [
                                'NAME' => 'NAME'
                            ],
                            [
                                'NAME' => 'DESCRIPTION'
                            ]
                        ],
                        'column' => 0

                    ]
                ]
            ],
            [
                'name' => 'right',
                'type' => 'column',
                'data' => [
                    'width' => 70
                ],
                'elements' => [
                    [
                        'name' => 'main',
                        'type' => 'section',
                        'title' => 'Поля',
                        'elements' => [
                            [
                                'NAME' => 'CREATE_DATE'
                            ],
                            [
                                'NAME' => 'PREV_COMPETENCE_ID'
                            ],
                            [
                                'NAME' => 'PREV_COMPETENCE_ID'
                            ]
                        ],
                        'column' => 1
                    ]
                ]
            ],
        ];

        $this->arResult['FORM']['ENTITY_CONFIG'] = $arColumns;

        return $this->arResult['FORM']['ENTITY_CONFIG'];
    }

}
