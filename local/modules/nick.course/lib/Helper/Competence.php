<?php

namespace Nick\Course\Helper;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Nick\Course\Model\Competence\CompetenceTable;

class Competence
{
    /**
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     */
    public static function getFieldsList(): array
    {
        $fieldsList = [];
        $fields = CompetenceTable::getMap();
        foreach ($fields as $field) {
            $fieldsList[$field->getName()] = $field->getName();
        }

        return array_filter(
            $fieldsList,
            function($field) {
                return !in_array($field, ['PREV_COMPETENCE', 'NEXT_COMPETENCE']);
            }
        ) ?: [];
    }
}
