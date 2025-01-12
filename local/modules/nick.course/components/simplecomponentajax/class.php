<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Nick\Course\Helper\Options;
use Bitrix\UI\Buttons;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class CompetenceListComponentAjax extends CBitrixComponent implements Controllerable
{
    /**
     * @return array
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function sendMessageAction(): array
    {
        return [
            'gradesCount' => $this->getElementsCountByUser()
        ];
    }

    /**
     * @return array
     */
    public function configureActions(): array
    {
        return [];
    }

    /**
     * @return int
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getElementsCountByUser(): int
    {
        Loader::requireModule('nick.course');

        $iblockId = Options::getParam('USER_COMPETENCE_LIST_ID');
        $propertyCode = Options::getParam('USER_COMPETENCE_LIST_USER_PROP_CODE');
        $userId = CurrentUser::get()->getId();

        $iblock = \Bitrix\Iblock\Iblock::wakeUp($iblockId);

        return $iblock->getEntityDataClass()::getCount([
            $propertyCode . '.VALUE' => $userId,
        ]);
    }

    public function executeComponent(): void
    {
        try {
            $this->checkModules();
            $this->arResult['BUTTONS'][]= new Buttons\Button([
                'color' => Buttons\Color::SUCCESS,
                'text' => 'Нажми меня',
                'className' => 'click-me-button'
            ]);
            $this->IncludeComponentTemplate();
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }

    /**
     * @throws LoaderException
     */
    protected function checkModules(): void
    {
        $modules = [
            'nick.course',
            'ui'
        ];

        foreach ($modules as $module) {
            Loader::requireModule($module);
        }
    }

}
