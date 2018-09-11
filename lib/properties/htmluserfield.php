<?php namespace Wecan\Tools\Properties;

class HtmlUserField extends \CUserTypeString
{
    public function GetUserTypeDescription()
    {
        return [
            'USER_TYPE_ID' => "html_editor",
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'HTML Редактор',
            'BASE_TYPE' => 'string'
        ];
    }

    public function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        ob_start();
        ?><table width="100%">
        <tr>
            <td colspan="2" align="center">
                <input type="hidden" name="<?=$arHtmlControl["NAME"]?>" value="">
                <?
                \CFileMan::AddHTMLEditorFrame(
                    $arHtmlControl["NAME"],
                    $arHtmlControl['VALUE'],
                    'type',
                    'html'
                );
                ?>
            </td>
        </tr>
        </table>
        <?
        $return = ob_get_contents();
        ob_end_clean();
        return  $return;
    }
}