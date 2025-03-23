<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Objectify\Collection;
use Nick\Course\Helper\Options;
use Nick\Course\Model\Competence\CompetenceTable;


class GradesListComponent extends CBitrixComponent implements Controllerable, Errorable
{
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
     * Getting once error with the necessary code.
     * @param string $code Code of error.
     * @return Error
     */
    public function getErrorByCode($code): Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }
    /**
     * Getting array of errors.
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }
    public function checkModules()
    {
        if (!Loader::includeModule('nick.course')) {
            ShowError(Loc::getMessage('MODULE_IS_NOT_INSTALLED'));
            return false;
        }

        return true;
    }

    public function executeComponent()
    {
        if (!$this->checkModules()) 
            return;
        $this->arResult['LIST']['IBLOCK_TYPE_ID'] = 'lists';
        $this->arResult['LIST']['IBLOCK_ID'] = Options::getParam('USER_COMPETENCE_LIST_ID');
        $this->arResult['LIST']['IBLOCK_ID_USER_COMPETENCE'] = Options::getParam('USER_COMPETENCE_LIST_ID');
      
        $this->arResult['LIST']['LIST_ELEMENT_URL'] = "/detail/user.grade.php?user_grade_id=#element_id#";


        $this->includeComponentTemplate();
    }

    public function setCompetenceAction(array $data): ?array
    {

        $competenceValueName = match ($data['values'][0]) {
            'previousCompetence' => 'previousCompetence',
            'nextCompetence' => 'nextCompetence',
            default => null
        };

        if ($competenceValueName === null) {
            $this->errorCollection[] = (new Error('Неверный тип компетенции'));
            return null;
        }

        $updatedCounter = 0;

        foreach ($data['gridSelectedRows'] as $elementId) {
            CIBlockElement::SetPropertyValuesEx(
                $elementId,
                false,
                ['COMPETENCE_ID' => $data['values']['nextCompetence']]
            );
            $updatedCounter++;
        }
        return [
            'updated' => $updatedCounter,
            'errors' => count($this->getErrors())
        ];
    }
}
