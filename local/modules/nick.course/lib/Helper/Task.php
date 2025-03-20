<?php

namespace Nick\Course\Helper;

class Task
{
    public static function getEnumUserFieldsValues()
    {
        global $USER_FIELD_MANAGER;

        $userFields = $USER_FIELD_MANAGER->GetUserFields('TASKS_TASK');

        $result = [];

        foreach ($userFields as $fieldName => $userField) {
            if ($userField['USER_TYPE_ID'] === 'enumeration') {
                $fieldData = \Bitrix\Main\UserFieldTable::getFieldData($userField['ID']);

                $enumValues = [];
                if (isset($fieldData['ENUM']) && is_array($fieldData['ENUM'])) {
                    foreach ($fieldData['ENUM'] as $enum) {
                        $enumValues[$enum['ID']] = $enum['VALUE'];
                    }
                }

                $result[$fieldName] = [
                    'items' => $enumValues
                ];
            }
        }

        return $result;
    }
}
