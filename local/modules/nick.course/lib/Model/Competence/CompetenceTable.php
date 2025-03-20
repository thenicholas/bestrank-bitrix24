<?php
namespace Nick\Course\Model\Competence;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Validator\Length;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\EntityError;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

class CompetenceTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'nc_competence';
    }

    /**
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete()
                ->configureTitle(Loc::getMessage('NC_COMPETENCE_ENTITY_ID_FIELD')),

            (new StringField('NAME'))
                ->configureRequired()
                ->configureSize(150)
                ->addValidator(new Length(1))
                ->configureTitle(Loc::getMessage('NC_COMPETENCE_ENTITY_TITLE_FIELD')),

            (new TextField('DESCRIPTION'))
                ->configureTitle(Loc::getMessage('NC_COMPETENCE_ENTITY_DESCRIPTION_FIELD'))
                ->addValidator(new Length(1, 512)),

            (new DatetimeField('CREATE_DATE'))
                ->configureAutocomplete()
                ->configureTitle(Loc::getMessage('NC_COMPETENCE_ENTITY_CREATE_DATE_FIELD'))
                ->configureDefaultValue(new DateTime()),

            (new IntegerField('PREV_COMPETENCE_ID'))
                ->configureTitle(Loc::getMessage('NC_COMPETENCE_ENTITY_PREV_COMPETENCE_FIELD')),

            (new Reference(
                'PREV_COMPETENCE',
                self::class,
                Join::on('this.PREV_COMPETENCE_ID', 'ref.ID')
            )),

            (new IntegerField('NEXT_COMPETENCE_ID'))
                ->configureTitle(Loc::getMessage('NC_COMPETENCE_ENTITY_NEXT_COMPETENCE_FIELD')),

            (new Reference(
                'NEXT_COMPETENCE',
                self::class,
                Join::on('this.NEXT_COMPETENCE_ID', 'ref.ID')
            )),
        ];
    }

    public static function onBeforeUpdate(Event $event): EventResult
    {
            $result = new EventResult();

            $competenceId = $event->getParameter('primary')['ID'];
            $fields = $event->getParameter('fields');

            if ((int)$fields['PREV_COMPETENCE_ID'] === $competenceId ||
                (int)$fields['NEXT_COMPETENCE_ID'] === $competenceId) {
                $result->addError(new EntityError(
                    Loc::getMessage('NC_COMPETENCE_ENTITY_NEXT_COMPETENCE_FIELD_VALUE_ERROR')
                ));
            }

            return $result;
    }
}
