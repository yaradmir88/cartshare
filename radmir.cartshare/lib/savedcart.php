<?php 
namespace Radmir\Cartshare;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\TextField;
use \Bitrix\Highloadblock\HighloadBlockTable as HLTable;
use \Bitrix\Highloadblock\HighloadBlockLangTable as HLLangTable;

Loc::loadMessages(__FILE__);
Loader::IncludeModule('highloadblock');

class SavedCartTable extends DataManager
{
    CONST TABLE_NAME = 'r_saved_cart';
    CONST ENTITY_NAME = 'RadmirSavedCart';

    public static function getTableName() {
        return self::TABLE_NAME;
    }

    public static function getMap()
	{
        $fields = [
            new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('RADMIR_CARTSHARE_HL_FIELD_ID')
				]
			)
        ];
        $fieldsRaws = self::getFieldsRaw();
        foreach ($fieldsRaws as $fieldId => $field) {
            $mappedField = self::getMappedField($fieldId, $field);

            if (!empty($mappedField)) {
                $fields[] = $mappedField;
            }
        }

        return $fields;
	}

    public static function deleteHL() {
        $hlId = self::getHLID();
        if (!empty($hlId)) {
            HLTable::delete($hlId);
        }
    }

    public static function addHL() {
        $id = false;
        
        $hlResult = HLTable::add([
            'NAME' => self::ENTITY_NAME,
            'TABLE_NAME' => self::TABLE_NAME,
        ]);

        if ($hlResult->isSuccess()) {
            $id = $hlResult->getId();
            self::addHLLangTable($id);
            self::addHLFields($id);
        } 

        return $id;
    }

    public static function increaseViewCounter($id) {
        $item = self::getById($id)->fetch();
        if (!empty($item)) {
            $count = (!empty($item['UF_CNT_VIEW'])) ? $item['UF_CNT_VIEW'] : 0;
            self::update($id, ['UF_CNT_VIEW' => ++$count]);
        }
    }

    private static function getLangName() {
        $lang = \Bitrix\Main\Application::getInstance()->getContext()->getLanguage();
        return [
            $lang => Loc::getMessage('RADMIR_CARTSHARE_HL_NAME'),
        ];
    }

    private static function getFieldsRaw() {
        return [
            'UF_CART' => [
                'NAME' => Loc::getMessage('RADMIR_CARTSHARE_HL_FIELD_CART'),
                'USER_TYPE_ID' => 'string',
                'MANDATORY' => 'Y',
            ],
            'UF_DATE' => [
                'NAME' => Loc::getMessage('RADMIR_CARTSHARE_HL_FIELD_DATE'),
                'USER_TYPE_ID' => 'datetime',
                'MANDATORY' => 'Y',
            ],
            'UF_USER_ID' => [
                'NAME' => Loc::getMessage('RADMIR_CARTSHARE_HL_FIELD_USER_ID'),
                'USER_TYPE_ID' => 'integer',
                'MANDATORY' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => 0,
                    'SIZE' => 10
                ],
            ],
            'UF_CNT_VIEW' => [
                'NAME' => Loc::getMessage('RADMIR_CARTSHARE_HL_FIELD_CNT_VIEW'),
                'USER_TYPE_ID' => 'integer',
                'MANDATORY' => 'N',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => 0,
                    'SIZE' => 10
                ],
            ],
            'UF_LINK' => [
                'NAME' => Loc::getMessage('RADMIR_CARTSHARE_HL_FIELD_LINK'),
                'MANDATORY' => 'N',
                'USER_TYPE_ID' => 'string',
                'SETTINGS' => [
                    'DEFAULT_VALUE' => '',
                    'SIZE' => 20
                ],
            ]
        ];
    }

    private static function getMappedField($fieldId, $field) {
        $params = [
            'title' => $field['NAME'],
        ];

        if (isset($field['DEFAULT_VALUE'])) {
            $params['default'] = $field['DEFAULT_VALUE'];
        }

        if (!empty($field['MANDATORY']) && $field['MANDATORY'] == 'Y') {
            $params['required'] = true;
        }

        switch ($field['USER_TYPE_ID']) {
            case 'string': 
                $mappedField = new TextField($fieldId, $params);
                break;
            case 'integer': 
                $mappedField = new IntegerField($fieldId, $params);
                break;
            case 'datetime': 
                $mappedField = new DatetimeField($fieldId, $params);
                break;
            default: 
                $mappedField = false;
        }

        return $mappedField;
    }

    private static function getFields($objectCode)
    {
        $lang = \Bitrix\Main\Application::getInstance()->getContext()->getLanguage();
        $fields = [];
        $fieldsRaws = self::getFieldsRaw();
        foreach ($fieldsRaws as $fieldId => $field) {
            $tmpField = [
                'ENTITY_ID' => $objectCode,
                'FIELD_NAME' => $fieldId,
                'USER_TYPE_ID' => $field['USER_TYPE_ID'],
                "EDIT_FORM_LABEL" => [$lang => $field['NAME']],
                "LIST_COLUMN_LABEL" => [$lang => $field['NAME']],
                "LIST_FILTER_LABEL" => [$lang => $field['NAME']],
                "ERROR_MESSAGE" => [$lang => ''],
                "HELP_MESSAGE" => [$lang => ''],
            ];
            if (!empty($field['MANDATORY'])) {
                $tmpField['MANDATORY'] = $field['MANDATORY'];
            }
            if (!empty($field['SETTINGS'])) {
                $tmpField['SETTINGS'] = $field['SETTINGS'];
            }

            $fields[$fieldId] = $tmpField;
        }

        return $fields;
    }

    private static function getHLID()
    {
        $hlId = false;

        $hlList = HLTable::getList(array(
            'select' => ["ID"], 
            'limit' => '1',
            'filter' => ['NAME' => self::ENTITY_NAME]
        ));

        if ($hlItem = $hlList->fetch()) {
            $hlId = $hlItem['ID'];
        }
    
        return $hlId;
    }

    private static function addHLLangTable($id) {
        foreach(self::getLangName() as $langKey => $langVal){
            HLLangTable::add([
                'ID' => $id,
                'LID' => $langKey,
                'NAME' => $langVal
            ]);	
        }
    }

    private static function addHLFields($id) {
        $objectCode = 'HLBLOCK_' . $id;
        $fieldsList = self::getFields($objectCode);
        foreach($fieldsList as $field){
            $obUserField  = new \CUserTypeEntity;
            $obUserField->Add($field);
        }
    }

}
