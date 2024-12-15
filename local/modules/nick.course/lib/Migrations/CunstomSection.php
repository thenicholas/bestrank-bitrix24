<?php

namespace Nick\Course\Migrations;

use Bitrix\Intranet\CustomSection\Entity\CustomSectionPageTable;
use Bitrix\Intranet\CustomSection\Entity\CustomSectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use Nick\Course\Helper\Options;

class CunstomSection
{
    /**
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public static function up()
    {
        $arFields = [
            'CODE' => 'nick_course',
            'TITLE' => Loc::getMessage('LEFT_MENU_SECTION_TITLE'),
            'MODULE_ID' => Options::moduleId
        ];
        $fetchResult = CustomSectionTable::query()
            ->addSelect('ID')
            ->where('CODE', $arFields['CODE'])
            ->where('MODULE_ID', Options::moduleId)
            ->fetch();

        if (!$fetchResult) {
            $result = CustomSectionTable::add(['fields' => $arFields]);
            if (!$result->isSuccess()) {
                $errors[] = $result->getErrorMessages();
                throw new Exception(implode('', $errors));
            }
            $sectionId = $result->getId();
        } else {
            $sectionId = $fetchResult['ID'];
        }

        $arFields = [
            'CUSTOM_SECTION_ID' => $sectionId,
            'CODE' => 'competence_list',
            'TITLE' => Loc::getMessage('LEFT_MENU_COMPETENCE_LIST'),
            'MODULE_ID' => Options::moduleId,
            'SETTINGS' => 'userrating.competence.list',
            'SORT' => '100',
        ];

        $fetchResult = CustomSectionPageTable::query()
            ->addSelect('ID')
            ->where('CODE', $arFields['CODE'])
            ->where('MODULE_ID', Options::moduleId)
            ->fetch();
        if (!$fetchResult) {
            CustomSectionPageTable::add(['fields' => $arFields]);
        }

        $arFields['CODE'] = 'grades_list';
        $arFields['TITLE'] = Loc::getMessage('LEFT_MENU_GRADES_LIST');
        $arFields['SORT'] = '200';
        $arFields['SETTINGS'] = 'userrating.grades.list';

        $fetchResult = CustomSectionPageTable::query()
            ->addSelect('ID')
            ->where('CODE', $arFields['CODE'])
            ->where('MODULE_ID', Options::moduleId)
            ->fetch();
        if (!$fetchResult) {
            CustomSectionPageTable::add(['fields' => $arFields]);
        }

        $arFields['CODE'] = 'user_grades';
        $arFields['TITLE'] = Loc::getMessage('LEFT_MENU_USER_GRADES_LIST');
        $arFields['SORT'] = '300';
        $arFields['SETTINGS'] = 'userrating.user.grades.list';

        $fetchResult = CustomSectionPageTable::query()
            ->addSelect('ID')
            ->where('CODE', $arFields['CODE'])
            ->where('MODULE_ID', Options::moduleId)
            ->fetch();
        if (!$fetchResult) {
            CustomSectionPageTable::add(['fields' => $arFields]);
        }
    }

    /**
     * @throws ObjectPropertyException
     * @throws ArgumentException
     * @throws SystemException
     * @throws Exception
     */
    public static function down(): void
    {
        $section = CustomSectionTable::query()
            ->addSelect('ID')
            ->where('MODULE_ID', Options::moduleId)
            ->fetch();

        if ($section) {
            $result = CustomSectionTable::delete($section['ID']);
            if (!$result->isSuccess()) {
                $errors[] = $result->getErrorMessages();
                throw new Exception(implode('', $errors));
            }

            $subsections = CustomSectionPageTable::query()
                ->addSelect('ID')
                ->where('MODULE_ID', Options::moduleId)
                ->fetchAll();
            if ($subsections) {
                foreach ($subsections as $subsection) {
                    CustomSectionPageTable::delete($subsection);
                }
            }
        }
    }
}
