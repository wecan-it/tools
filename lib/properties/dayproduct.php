<?php

namespace Wecan\Tools\Properties;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Page\AssetLocation;

class DayProduct extends \CUserTypeIBlockElement
{
    public function GetUserTypeDescription()
    {
        return [
            'USER_TYPE_ID' => "day_product",
            'CLASS_NAME'   => __CLASS__,
            'DESCRIPTION'  => 'Товар дня',
            'BASE_TYPE'    => 'int'
        ];
    }

    public function getList($arUserField)
    {
        $section = SectionTable::getRow([
            'filter' =>
                [
                    '=ACTIVE'      => 'Y',
                    '=DEPTH_LEVEL' => 1,
                    '=ID'          => $arUserField['VALUE_ID']
                ],
            'select' =>
                [
                    'ID'
                ]
        ]);

        if ($section) {
            $obElements = \CIBlockElement::GetList([
                'NAME' => 'ASC'
            ], [
                'IBLOCK_ID'          => CATALOG_IBLOCK_ID,
                'SECTION_ID'         => $section['ID'],
                'INCLUDE_SUBSECTIONS' => 'Y'
            ], false, false, ['ID', 'NAME']);
        }

        return $obElements ?? null;
    }

    public function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        $rsEnum = call_user_func_array(
            array($arUserField["USER_TYPE"]["CLASS_NAME"], "getlist"),
            array(
                $arUserField,
            )
        );
        if(!$rsEnum)
            return '';

        $bWasSelect = false;
        $options = '';

        while($arEnum = $rsEnum->GetNext(false, false))
        {
            $bSelected = (
                ($arHtmlControl["VALUE"]==$arEnum["ID"]) ||
                ($arUserField["ENTITY_VALUE_ID"]<=0 && $arEnum["DEF"]=="Y")
            );
            $bWasSelect = $bWasSelect || $bSelected;
            $options .= '<option value="'.$arEnum["ID"].'"'.($bSelected? ' selected': '').'>'.$arEnum["NAME"].'</option>';
        }

        global $APPLICATION;
        $APPLICATION->oAsset->addJs('/local/templates/.default/js/jquery.js');
        $APPLICATION->oAsset->addJs('/local/templates/.default/js/select2.min.js');
        $APPLICATION->SetAdditionalCSS('/local/templates/.default/css/select2.min.css');
        $APPLICATION->oAsset->addString('
        <script>$(function() {
            $(\'#day_product\').select2({ width: \'400px\' });
        })</script>', true, AssetLocation::AFTER_JS);

        return '
        <select name="'.$arHtmlControl["NAME"].'" id="day_product" class="select2">
            <option value="">Нет</option>'.
                $options
            .'
        </select>
        ';
    }
}