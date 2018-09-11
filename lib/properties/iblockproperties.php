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
class IblockProperties extends \CUserTypeIBlockElement
{
    function GetUserTypeDescription()
    {
        return [
            'USER_TYPE_ID' => "iblock_properties",
            'CLASS_NAME' => get_called_class(),
            'DESCRIPTION' => 'Cвойства инфоблока',
            'BASE_TYPE' => 'int'
        ];
    }

    public static function getPropertiesByIds($arIds, $iblockId)
    {
        $res = [];

        $propOb = \CIBlockProperty::GetList(
            [],
            [
                'IBLOCK_ID' => $iblockId
            ]
        );

        while ($resProp = $propOb->Fetch()) {
            if (in_array($resProp['ID'], $arIds))
                $res[$resProp['ID']] = [
                    'TYPE' => $resProp['PROPERTY_TYPE'],
                    'NAME' => $resProp['NAME'],
                    'MULTIPLE' => $resProp['MULTIPLE'],
                    'CODE' => $resProp['CODE']
                ];
        }

        return $res;
    }

    function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
    {
        $result = '';

        if($bVarsFromForm)
            $iblock_id = $GLOBALS[$arHtmlControl["NAME"]]["IBLOCK_ID"];
        elseif(is_array($arUserField))
            $iblock_id = $arUserField["SETTINGS"]["IBLOCK_ID"];
        else
            $iblock_id = "";
        if(\CModule::IncludeModule('iblock'))
        {
            $result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_IBEL_DISPLAY").':</td>
				<td>
					'.GetIBlockDropDownList($iblock_id, $arHtmlControl["NAME"].'[IBLOCK_TYPE_ID]', $arHtmlControl["NAME"].'[IBLOCK_ID]', false, 'class="adm-detail-iblock-types"', 'class="adm-detail-iblock-list"').'
				</td>
			</tr>
			';
        }
        else
        {
            $result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_IBEL_DISPLAY").':</td>
				<td>
					<input type="text" size="6" name="'.$arHtmlControl["NAME"].'[IBLOCK_ID]" value="'.htmlspecialcharsbx($value).'">
				</td>
			</tr>
			';
        }

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
        if(($iblock_id > 0) && \CModule::IncludeModule('iblock'))
        {
            $result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_IBEL_DEFAULT_VALUE").':</td>
				<td>
					<select name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE]" size="5">
						<option value="">'.GetMessage("IBLOCK_VALUE_ANY").'</option>
			';

            $arFilter = Array("IBLOCK_ID"=>$iblock_id);
            if($ACTIVE_FILTER === "Y")
                $arFilter["ACTIVE"] = "Y";

            $rs = \CIBlockProperty::GetList(
                array("SORT" => "DESC", "NAME"=>"ASC"),
                $arFilter
            );
            while($ar = $rs->GetNext())
                $result .= '<option value="'.$ar["ID"].'"'.($ar["ID"]==$value? " selected": "").'>'.$ar["NAME"].'</option>';

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

    function CheckFields($arUserField, $value)
    {
        $aMsg = array();
        return $aMsg;
    }

    function GetList($arUserField)
    {
        $rsElement = false;
        if(\CModule::IncludeModule('iblock'))
        {
            $obElement = new CIBlockPropertyEnum();
            $rsElement = $obElement->GetTreeList($arUserField["SETTINGS"]["IBLOCK_ID"], $arUserField["SETTINGS"]["ACTIVE_FILTER"]);
        }
        return $rsElement;
    }

    function OnSearchIndex($arUserField)
    {
        $res = '';

        if(is_array($arUserField["VALUE"]))
            $val = $arUserField["VALUE"];
        else
            $val = array($arUserField["VALUE"]);

        $val = array_filter($val, "strlen");
        if(count($val) && CModule::IncludeModule('iblock'))
        {
            $ob = new \CIBlockProperty();
            $rs = $ob->GetList(array("sort" => "asc", "id" => "asc"), array(
                "=ID" => $val
            ));

            while($ar = $rs->Fetch())
                $res .= $ar["NAME"]."\r\n";
        }

        return $res;
    }
}

class CIBlockPropertyEnum extends \CDBResult
{
    function GetTreeList($IBLOCK_ID, $ACTIVE_FILTER="N")
    {
        $rs = false;
        if(\CModule::IncludeModule('iblock'))
        {
            $arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID);
            if($ACTIVE_FILTER === "Y")
                $arFilter["ACTIVE"] = "Y";

            $rs = \CIBlockProperty::GetList(
                array("SORT" => "DESC", "NAME"=>"ASC"),
                $arFilter
            );
            if($rs)
            {
                $rs = new CIBlockPropertyEnum($rs);
            }
        }
        return $rs;
    }

    function GetNext($bTextHtmlAuto=true, $use_tilda=true)
    {
        $r = parent::GetNext($bTextHtmlAuto, $use_tilda);
        if($r)
            $r["VALUE"] = $r["NAME"];

        return $r;
    }
}