<?php
namespace Wecan\Tools\Properties;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;

class SectionsTags extends \CUserTypeIBlockElement
{
    public function GetUserTypeDescription()
    {
        return [
            'USER_TYPE_ID' => "sections_tags",
            'CLASS_NAME'   => __CLASS__,
            'DESCRIPTION'  => 'Разделы - тэги',
            'BASE_TYPE'    => 'int'
        ];
    }

    public function getList($arUserField)
    {
        $rsElement = false;
        if(\CModule::IncludeModule('iblock'))
        {
            $tagsProp = PropertyTable::getRow([
                'filter' =>
                    [
                        '=IBLOCK_ID' => CATALOG_IBLOCK_ID,
                        '=CODE' => 'TAGS'
                    ],
                'select' =>
                    [
                        'ID'
                    ]
            ]);
            $obElementsTags = \CIBlockElement::GetPropertyValues(CATALOG_IBLOCK_ID, [
                'IBLOCK_ID'           => CATALOG_IBLOCK_ID,
                '=ACTIVE'             => 'Y',
                '=SECTION_ID'         => $arUserField['ENTITY_VALUE_ID'],
                'INCLUDE_SUBSECTIONS' => 'Y',
                '!PROPERTY_TAGS'      => false
            ], false, [
                'ID' => $tagsProp['ID']
            ]);
            $tagsIds = [];
            while($arElementTag = $obElementsTags->Fetch()) {
                $tagsIds = array_merge($tagsIds, $arElementTag[$tagsProp['ID']]);
            }

            $rsElement = [];
            if(!empty($tagsIds)) {
                $rsElement = ElementTable::getList([
                    'filter' =>
                        [
                            '=IBLOCK_ID' => $arUserField["SETTINGS"]["IBLOCK_ID"],
                            '=ACTIVE'    => 'Y',
                            '=ID' => array_unique($tagsIds)
                        ],
                    'select' =>
                        [
                            'ID',
                            'VALUE' => 'NAME'
                        ]
                ]);
            }
        }

        $rs = new \CDBResult($rsElement);

        return $rs;
    }

}