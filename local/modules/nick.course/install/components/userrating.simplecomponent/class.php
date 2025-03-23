<?php

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ORM\Objectify\Collection;
use Nick\Course\Model\Competence\CompetenceTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 *
 */
class SimpleComponent extends CBitrixComponent
{
    /**
     * @return void
     */
    public function executeComponent(): void
    {
        try {
            $this->checkModules();

            $competencies = CompetenceTable::query()
                ->setSelect(array_values($this->arParams['SU_COMPETENCE_TABLE_FILEDS_LIST']))
                ->setLimit($this->arParams['SU_ELEMENTS_QUANTITY'])
                ->fetchCollection();

            /** @var Collection $competencies */
            $this->arResult['ITEMS'] = array_map(fn($element) => $element->collectValues(), $competencies->getAll());

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
        Loader::requireModule('nick.course');
    }
}
