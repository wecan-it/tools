<?php
namespace Wecan\Tools\Properties;

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

/**
 * Created by PhpStorm.
 * User: fans
 * Date: 16.11.16
 * Time: 16:03
 */
class ProductsFields extends \CUserTypeEnum
{
    private static $fields = [
        0 => [
            'NAME' => 'Изображение',
            'CODE' => 'PREVIEW_PICTURE'
        ],
        1 => [
            'NAME' => 'Наименование',
            'CODE' => 'NAME'
        ],
        2 => [
            'NAME' => 'Цена',
            'CODE' => [
                'PROPERTY_MAXIMUM_PRICE',
                'PROPERTY_MINIMUM_PRICE'
            ]
        ],
        3 => [
            'NAME' => 'Наличие',
            'CODE' => 'CATALOG_QUANTITY'
        ]
    ];

    public static function getFieldsCode($arFields)
    {
        $res = [];
        if (!empty($arFields)) {
            foreach ($arFields as $field) {
                if (self::$fields[$field]) {
                    $code = self::$fields[$field]['CODE'];
                    if (is_array($code))
                        $res = array_merge($res, $code);
                    else
                        $res[] = $code;
                }
            }
        }
        return $res;
    }

    function PrepareSettings($arUserField)
    {
        $height = intval($arUserField["SETTINGS"]["LIST_HEIGHT"]);
        $disp = $arUserField["SETTINGS"]["DISPLAY"];
        $caption_no_value = trim($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]);

        if($disp!="CHECKBOX" && $disp!="LIST")
            $disp = "LIST";
        return array(
            "DISPLAY" => $disp,
            "LIST_HEIGHT" => ($height < 1? 1: $height),
            "DEFAULT_VALUE" => $arUserField['SETTINGS']['DEFAULT_VALUE'],
            "CAPTION_NO_VALUE" => $caption_no_value // no default value - only in output
        );
    }

    function GetUserTypeDescription()
    {
        return [
            'USER_TYPE_ID' => "product_fields",
            'CLASS_NAME' => get_called_class(),
            'DESCRIPTION' => 'Поля товара',
            'BASE_TYPE' => 'int'
        ];
    }

    function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
    {
        $result = '';

        if($bVarsFromForm)
            $ACTIVE_FILTER = $GLOBALS[$arHtmlControl["NAME"]]["ACTIVE_FILTER"] === "Y"? "Y": "N";
        elseif(is_array($arUserField))
            $ACTIVE_FILTER = $arUserField["SETTINGS"]["ACTIVE_FILTER"] === "Y"? "Y": "N";
        else
            $ACTIVE_FILTER = "N";

        if($bVarsFromForm)
            $value = $GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"];
        elseif(is_array($arUserField))
            $value = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
        else
            $value = "";

        $result .= '
        <tr>
            <td>'.GetMessage("USER_TYPE_IBEL_DEFAULT_VALUE").':</td>
            <td>
                <select name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE][]" size="5" multiple>
                    <option value="">'.GetMessage("IBLOCK_VALUE_ANY").'</option>
        ';

        foreach (self::$fields as $key=>$field) {
            $result .= '<option value="'.$key.'"'.(($key==$value || (is_array($value) && in_array($key, $value)))? " selected": "").'>'.$field['NAME'].'</option>';
        }

        $result .= '</select>';

        if($bVarsFromForm)
            $value = $GLOBALS[$arHtmlControl["NAME"]]["DISPLAY"];
        elseif(is_array($arUserField))
            $value = $arUserField["SETTINGS"]["DISPLAY"];
        else
            $value = "LIST";
        $result .= '
		<tr>
			<td class="adm-detail-valign-top">'.GetMessage("USER_TYPE_ENUM_DISPLAY").':</td>
			<td>
				<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DISPLAY]" value="LIST" '.("LIST"==$value? 'checked="checked"': '').'>'.GetMessage("USER_TYPE_IBEL_LIST").'</label><br>
				<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DISPLAY]" value="CHECKBOX" '.("CHECKBOX"==$value? 'checked="checked"': '').'>'.GetMessage("USER_TYPE_IBEL_CHECKBOX").'</label><br>
			</td>
		</tr>
		';

        if($bVarsFromForm)
            $value = intval($GLOBALS[$arHtmlControl["NAME"]]["LIST_HEIGHT"]);
        elseif(is_array($arUserField))
            $value = intval($arUserField["SETTINGS"]["LIST_HEIGHT"]);
        else
            $value = 5;
        $result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_IBEL_LIST_HEIGHT").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[LIST_HEIGHT]" size="10" value="'.$value.'">
			</td>
		</tr>
		';

        $result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_IBEL_ACTIVE_FILTER").':</td>
			<td>
				<input type="checkbox" name="'.$arHtmlControl["NAME"].'[ACTIVE_FILTER]" value="Y" '.($ACTIVE_FILTER=="Y"? 'checked="checked"': '').'>
			</td>
		</tr>
		';

        return $result;
    }

    function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        if(($arUserField["ENTITY_VALUE_ID"]<1) && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]);

        $result = '';

        if($arUserField["SETTINGS"]["DISPLAY"]=="CHECKBOX")
        {
            $selected = false;
            foreach (self::$fields as $key=>$field) {
                if ($key == $arHtmlControl["VALUE"] || (is_array($arHtmlControl['VALUE']) && in_array($key, $arHtmlControl['VALUE']))) $selected = true;
                $result .= '
                    <label>
                        <input type="radio" value="'.$key.'" name="'.$arHtmlControl["NAME"].'"'.($selected? ' checked': '').($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>'.
                            $field['NAME'].
                    '</label><br>';
            }
        }
        else
        {
            $bWasSelect = false;
            $result2 = '';
            foreach (self::$fields as $key=>$field)
            {
                $bSelected = ($arHtmlControl["VALUE"]==$key);
                $result2 .= '<option value="'.$key.'"'.($bSelected? ' selected': '').'>'.$field['NAME'].'</option>';
            }

            if($arUserField["SETTINGS"]["LIST_HEIGHT"] > 1)
            {
                $size = ' size="'.$arUserField["SETTINGS"]["LIST_HEIGHT"].'"';
            }
            else
            {
                $arHtmlControl["VALIGN"] = "middle";
                $size = '';
            }

            $result = '<select name="'.$arHtmlControl["NAME"].'"'.$size.($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>';
            if($arUserField["MANDATORY"]!="Y")
            {
                $result .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</option>';
            }
            $result .= $result2;
            $result .= '</select>';
        }
        return $result;
    }

    function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
    {
        if(($arUserField["ENTITY_VALUE_ID"]<1) && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
        elseif(!is_array($arHtmlControl["VALUE"]))
            $arHtmlControl["VALUE"] = array();

        $result = '';

        if($arUserField["SETTINGS"]["DISPLAY"]=="CHECKBOX")
        {
            $result .= '<input type="hidden" value="" name="'.$arHtmlControl["NAME"].'">';
            $bWasSelect = false;
            foreach (self::$fields as $key=>$field)
            {
                $bSelected = (in_array($key, $arHtmlControl["VALUE"]) || (is_array($arHtmlControl['VALUE']) && in_array($key, $arHtmlControl['VALUE'])));
                $bWasSelect = $bWasSelect || $bSelected;
                $result .= '<label><input type="checkbox" value="'.$key.'" name="UF_PRODUCT_FIELDS[]"'.
                    ($bSelected? ' checked': '').($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>'.
                    $field['NAME'].'</label><br>';
            }
        }
        else
        {
            $result = '<select multiple name="'.$arHtmlControl["NAME"].'" size="'.$arUserField["SETTINGS"]["LIST_HEIGHT"].'"'.($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': ''). '>';

            $result .= '<option value=""'.(!$arHtmlControl["VALUE"]? ' selected': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</option>';
            foreach(self::$fields as $key=>$field)
            {
                $bSelected = (in_array($key, $arHtmlControl["VALUE"]));
                $result .= '<option value="'.$key.'"'.($bSelected? ' selected': '').'>'.$field['NAME'].'</option>';
            }
            $result .= '</select>';
        }
        return $result;
    }


    function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        static $cache = array();
        $empty_caption = '&nbsp;';//strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? htmlspecialcharsbx($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) : '&nbsp;';

        if(!array_key_exists($arHtmlControl["VALUE"], $cache))
        {
            $value = self::$fields[$arHtmlControl["VALUE"]];

            if(!$value)
                return $empty_caption;

            if ($value)
                $cache[$arHtmlControl["VALUE"]] = $value;
        }
        if(!array_key_exists($arHtmlControl["VALUE"], $cache))
            $cache[$arHtmlControl["VALUE"]] = $empty_caption;

        return $cache[$arHtmlControl["VALUE"]];
    }
}