<?php namespace Wecan\Tools\Properties;

class SpecProperty
{
    public static function GetPropertyFieldHtmlMulty()
    {
        return [
            'PROPERTY_TYPE'             => 'E',
            'USER_TYPE'                 => 'Spec',
            'DESCRIPTION'               => 'Специализация',
            'GetPropertyFieldHtmlMulty' => function ($arProperty, $value, $strHTMLControlName) {
                $max_n = 0;
                $values = array();
                if (is_array($value)) {
                    foreach ($value as $property_value_id => $arValue) {
                        if (is_array($arValue)) {
                            $values[$property_value_id] = $arValue["VALUE"];
                        } else {
                            $values[$property_value_id] = $arValue;
                        }

                        if (preg_match("/^n(\\d+)$/", $property_value_id, $match)) {
                            if ($match[1] > $max_n) {
                                $max_n = intval($match[1]);
                            }
                        }
                    }
                }

                $html = '';

                $obRootSections = \CIBlockSection::GetList([], [
                    'IBLOCK_ID'   => $arProperty['LINK_IBLOCK_ID'],
                    'DEPTH_LEVEL' => 1,
                    'ACTIVE'      => 'Y'
                ], false, [
                    'ID',
                    'NAME'
                ]);
                $bWasSelect = false;
                while ($arRootSection = $obRootSections->Fetch()) {
                    $options = self::GetOptionsHtml($arProperty, $values, $bWasSelect, $arRootSection['ID']);
                    $html .= '<div style="display: inline-block;">';
                    $html .= '<input id="spec_hidden_input" type="hidden" name="' . $strHTMLControlName["VALUE"] . '[]" value="">';
                    $html .= '<b style="display:block; margin-bottom: 5px;">'.$arRootSection['NAME'] . '</b>';
                    $html .= '<select style="width:250px;" size="10" multiple name="' . $strHTMLControlName["VALUE"] . '[]">';
                    if ($arProperty["IS_REQUIRED"] != "Y") {
                        $html .= '<option value=""' . (!$bWasSelect ? ' selected' : '') . '>' . GetMessage("IBLOCK_PROP_ELEMENT_LIST_NO_VALUE") . '</option>';
                    }
                    $html .= $options;
                    $html .= '</select></div>';
                }

                return $html;
            }
        ];
    }

    public static function getOptionsHtml($arProperty, $values, &$bWasSelect, $parentSection = null)
    {
        $options = "";
        $bWasSelect = false;

        $arElements = self::GetElements($arProperty["LINK_IBLOCK_ID"], $parentSection);
        $arTree = self::GetSections($arProperty["LINK_IBLOCK_ID"], $parentSection);
        foreach ($arElements as $i => $arElement) {
            if (
                $arElement["IN_SECTIONS"] == "Y"
                && array_key_exists($arElement["IBLOCK_SECTION_ID"], $arTree)
            ) {
                $arTree[$arElement["IBLOCK_SECTION_ID"]]["E"][] = $arElement;
                unset($arElements[$i]);
            }
        }

        foreach ($arTree as $arSection) {
            $options .= '<optgroup label="' . str_repeat(" . ",
                    $arSection["DEPTH_LEVEL"] - 1) . $arSection["NAME"] . '">';
            if (isset($arSection["E"])) {
                foreach ($arSection["E"] as $arItem) {
                    $options .= '<option value="' . $arItem["ID"] . '"';
                    if (in_array($arItem["~ID"], $values)) {
                        $options .= ' selected';
                        $bWasSelect = true;
                    }
                    $options .= '>' . $arItem["NAME"] . '</option>';
                }
            }
            $options .= '</optgroup>';
        }
        foreach ($arElements as $arItem) {
            $options .= '<option value="' . $arItem["ID"] . '"';
            if (in_array($arItem["~ID"], $values)) {
                $options .= ' selected';
                $bWasSelect = true;
            }
            $options .= '>' . $arItem["NAME"] . '</option>';
        }


        return $options;
    }

    public static function GetSections($IBLOCK_ID, $parentSection = null)
    {
        $IBLOCK_ID = intval($IBLOCK_ID);

        $res = [];

        if ($IBLOCK_ID > 0) {
            $arSelect = array(
                "ID",
                "NAME",
                "DEPTH_LEVEL",
            );
            $arFilter = array(
                "IBLOCK_ID" => $IBLOCK_ID,
            );
            if (!is_null($parentSection)) {
                $arFilter['SECTION_ID'] = $parentSection;
            }
            $arOrder = array(
                "LEFT_MARGIN" => "ASC",
            );
            $rsItems = \CIBlockSection::GetList($arOrder, $arFilter, false, $arSelect);
            while ($arItem = $rsItems->GetNext()) {
                $res[$arItem["ID"]] = $arItem;
            }
        }

        return $res;
    }

    public static function GetElements($IBLOCK_ID, $parentSection = null)
    {
        $IBLOCK_ID = intval($IBLOCK_ID);
        $res = [];
        if ($IBLOCK_ID > 0) {
            $arSelect = array(
                "ID",
                "NAME",
                "IN_SECTIONS",
                "IBLOCK_SECTION_ID",
            );
            $arFilter = array(
                "=IBLOCK_ID"          => $IBLOCK_ID,
                "=ACTIVE"             => "Y",
                "INCLUDE_SUBSECTIONS" => "Y"
            );
            if (!is_null($parentSection)) {
                $arFilter['SECTION_ID'] = $parentSection;
            }
            $arOrder = array(
                "NAME" => "ASC",
                "ID"   => "ASC",
            );
            $rsItems = \CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
            while ($arItem = $rsItems->GetNext()) {
                $res[$arItem['ID']] = $arItem;
            }
        }

        return $res;
    }
}