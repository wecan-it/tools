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
class LinkToUser extends \CUserTypeEnum
{
    function GetUserTypeDescription()
    {
        return [
            'USER_TYPE_ID' => "user_link",
            'CLASS_NAME' => get_class($this),
            'DESCRIPTION' => 'Привязка к пользователю',
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

        if(\CModule::IncludeModule('main'))
        {
            $result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_IBEL_DEFAULT_VALUE").':</td>
				<td>
					<select name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE]" size="5">
						<option value="">'.GetMessage("IBLOCK_VALUE_ANY").'</option>
			';

            $ob = \Bitrix\Main\UserTable::getList([
                'select' => [
                    'ID',
                    'LOGIN'
                ]
            ]);

            while($ar = $ob->fetch())
                $result .= '<option value="'.$ar["ID"].'"'.($ar["ID"]==$value? " selected": "").'>'.$ar["LOGIN"].'</option>';

            $result .= '</select>';
        }
        else
        {
            $result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_IBEL_DEFAULT_VALUE").':</td>
				<td>
					<input type="text" size="8" name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE]" value="'.htmlspecialcharsbx($value).'">
				</td>
			</tr>
			';
        }

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

        $rsEnum = UserTable::getList([
            'filter' => [
                'ACTIVE' => 'Y'
            ],
            'select' => [
                'ID',
                'LOGIN'
            ]
        ]);
        if(!$rsEnum)
            return '';

        if($arUserField["SETTINGS"]["DISPLAY"]=="CHECKBOX")
        {
            $bWasSelect = false;
            $result2 = '';
            while($arEnum = $rsEnum->GetNext())
            {
                $bSelected = (
                    ($arHtmlControl["VALUE"]==$arEnum["ID"]) ||
                    ($arUserField["ENTITY_VALUE_ID"]<=0 && $arEnum["DEF"]=="Y")
                );
                $bWasSelect = $bWasSelect || $bSelected;
                $result2 .= '<label><input type="radio" value="'.$arEnum["ID"].'" name="'.$arHtmlControl["NAME"].'"'.($bSelected? ' checked': '').($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>'.$arEnum["VALUE"].'</label><br>';
            }
            if($arUserField["MANDATORY"]!="Y")
                $result .= '<label><input type="radio" value="" name="'.$arHtmlControl["NAME"].'"'.(!$bWasSelect? ' checked': '').($arUserField["EDIT_IN_LIST"]!="Y"? ' disabled="disabled" ': '').'>'.htmlspecialcharsbx(strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? $arUserField["SETTINGS"]["CAPTION_NO_VALUE"] : GetMessage('MAIN_NO')).'</label><br>';
            $result .= $result2;
        }
        else
        {
            $bWasSelect = false;
            $result2 = '';
            while($arEnum = $rsEnum->fetch())
            {
                $bSelected = (
                    ($arHtmlControl["VALUE"]==$arEnum["ID"]) ||
                    ($arUserField["ENTITY_VALUE_ID"]<=0 && $arEnum["DEF"]=="Y")
                );
                $bWasSelect = $bWasSelect || $bSelected;
                $result2 .= '<option value="'.$arEnum["ID"].'"'.($bSelected? ' selected': '').'>'.$arEnum["LOGIN"].'</option>';
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

    function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        static $cache = array();
        $empty_caption = '&nbsp;';//strlen($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) > 0 ? htmlspecialcharsbx($arUserField["SETTINGS"]["CAPTION_NO_VALUE"]) : '&nbsp;';

        if(!array_key_exists($arHtmlControl["VALUE"], $cache))
        {
            $user = UserTable::getById($arHtmlControl['VALUE'])->fetchAll();

            if(!$user)
                return $empty_caption;

            if ($user)
                $cache[$user[0]['ID']] = $user[0]['LOGIN'];
        }
        if(!array_key_exists($arHtmlControl["VALUE"], $cache))
            $cache[$arHtmlControl["VALUE"]] = $empty_caption;

        return $cache[$arHtmlControl["VALUE"]];
    }
}