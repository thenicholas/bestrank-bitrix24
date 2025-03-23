<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\UI\EntityForm\Control\Type;
use Nick\Course\Model\Competence\CompetenceTable;

class CompetenceDetailComponent extends CBitrixComponent
{
    private int $entityID;
    /**
     * @var \Bitrix\Main\ORM\Fields\ScalarField[]
     */
    private array $fieldsMap;
    private array $entityData;

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
            // подключаем метод проверки подключения модуля «Информационные блоки»
            $this->checkModules();

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
        // если модуль не подключен
        if (!Loader::includeModule('nick.course')) // выводим сообщение в catch
        {
            throw new SystemException(Loc::getMessage('SU_NO_MODULES'));
        }
    }

    protected function prepareFieldsMap()
    {
        if (!isset($this->fieldsMap)) {
            $this->fieldsMap = CompetenceTable::getEntity()->getScalarFields();
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

        $this->entityData = CompetenceTable::query()
            ->addSelect('*')
            ->where('ID', $this->entityID)
            ->fetch();

        $entityData = [];

        $fields = array_filter(CompetenceTable::getEntity()->getScalarFields(), fn($field) => !$field->isPrimary());

        foreach ($this->fieldsMap  as $field) {
            if ($field->isPrimary())
                continue;

            $fieldName = $field->getName();

            if ($fieldName == 'PREV_COMPETENCE_ID' || $fieldName == 'NEXT_COMPETENCE_ID') {
                $entityData[$fieldName. '[VIEW_HTML]'] = '<div style="background:red; width: 200px; height:15px; padding:35px;">' . $fieldName . '</div>';
                $entityData[$fieldName . '[EDIT_HTML]'] = 'Нельзя редактировать';
            }
            else {
                $entityData[$fieldName] = $this->entityData[$fieldName] ?? null;
            }
        }

        $this->entityData = $entityData;
    }

    protected function prepareFieldInfos(): array
    {
        if (isset($this->entityFieldInfos)) {
            return $this->entityFieldInfos;
        }

        $this->entityFieldInfos = [];

        $fieldItem = array_map(
            fn($field) => [
                'name' => $field->getName(),
                'title' => $field->getTitle(),
                'required' => $field->isRequired(),
                'defaultValue' => $field->getDefaultValue(),
                'editable' => true,
                'type' => match ($field->getName()) {
                    'PREV_COMPETENCE_ID', 'NEXT_COMPETENCE_ID' => 'custom',
                    default => match ($field->getDataType()) {
                        'integer' => Type::NUMBER,
                        'datetime', 'date' => Type::DATETIME,
                        'text' => Type::TEXTAREA,
                        default => Type::TEXT,
                    }
                },
                'data' => match ($field->getName()) {
                    'PREV_COMPETENCE_ID', 'NEXT_COMPETENCE_ID' => [
                        'view' => $field->getName() . '[VIEW_HTML]',
                        'edit' => $field->getName() . '[EDIT_HTML]',
                    ],
                    default => []
                }
            ],
            array_filter(CompetenceTable::getEntity()->getScalarFields(), fn ($field) => !$field->isPrimary())
        );

        $this->entityFieldInfos = $fieldItem;
        
        return $this->entityFieldInfos;
    }

    public function getDefaultConfigID(): string
    {
        return 'competence_details';
    }

    public function prepareConfiguration()
    {
        if (isset($this->arResult['FORM']['ENTITY_CONFIG'])) {
            return $this->arResult['FORM']['ENTITY_CONFIG'];
        }

        //Одна колонка в форме
        $this->prepareOneColumn();
        //Несколько колонок в форме
        //$this->prepareNColumn();
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
            if ($field->isPrimary()) {
                continue;
            }

            $configItem = [
                'name' => $field->getName()
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
