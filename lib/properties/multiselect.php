<?php namespace Wecan\Tools\Properties;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ReferenceField;

class MultiSelect
{
    public static function GetPropertyFieldHtmlMulty()
    {
        return [
            'PROPERTY_TYPE'             => 'E',
            'USER_TYPE'                 => 'MultiSelect',
            'DESCRIPTION'               => 'Два селекта',
            'GetPropertyFieldHtmlMulty' => function ($arProperty, $value, $strHTMLControlName) {
                \CJSCore::Init('jquery');
                $valuesIds = \ArrayHelper::getColumn($value, 'VALUE');
                $arElements = ElementTable::getList([
                    'filter'  =>
                        [
                            '=ACTIVE'    => 'Y',
                            '=IBLOCK_ID' => $arProperty['LINK_IBLOCK_ID']
                        ],
                    'select'  =>
                        [
                            'ID',
                            'NAME',
                            'IBLOCK_ID',
                            'IBLOCK_SECTION_ID',
                            'IBLOCK_SECTION.NAME',
                            'ROOT_SECTION.ID',
                            'ROOT_SECTION.NAME'
                        ],
                    'order'   =>
                        [
                            'ROOT_SECTION.NAME'   => 'ASC',
                            'IBLOCK_SECTION.NAME' => 'ASC',
                            'NAME'                => 'ASC'
                        ],
                    'runtime' =>
                        [
                            new ReferenceField('ROOT_SECTION', '\Bitrix\Iblock\Section', [
                                '>this.IBLOCK_SECTION.LEFT_MARGIN'  => 'ref.LEFT_MARGIN',
                                '<this.IBLOCK_SECTION.RIGHT_MARGIN' => 'ref.RIGHT_MARGIN',
                                '=ref.IBLOCK_ID'                    => new SqlExpression($arProperty['LINK_IBLOCK_ID']),
                                '=ref.DEPTH_LEVEL'                  => new SqlExpression(1)
                            ], [
                                'join_type' => 'LEFT'
                            ])
                        ]
                ])->fetchAll();
                foreach ($arElements as $arElement) {
                    $arSections[$arElement['IBLOCK_ELEMENT_ROOT_SECTION_NAME'] ?: $arElement['IBLOCK_ELEMENT_IBLOCK_SECTION_NAME']][] = $arElement;
                }
                ob_start() ?>
                <section class="container">
                    <div style="float:left;" class="select_not_selected">
                        <select style="width:430px" id="<?= $strHTMLControlName['FORM_NAME'] ?>_leftValues" size="15"
                                multiple>
                            <? foreach ($arSections as $name => $elements): ?>
                                <optgroup label="<?= $name ?>">
                                    <? foreach ($elements as $arElement): ?>
                                        <? if (!in_array($arElement['ID'], $valuesIds)): ?>
                                            <option value="<?= $arElement['ID'] ?>">
                                                <?= $arElement['IBLOCK_ELEMENT_ROOT_SECTION_NAME'] ? $arElement['IBLOCK_ELEMENT_IBLOCK_SECTION_NAME'] . '::' . $arElement['NAME'] : $arElement['NAME'] ?>
                                            </option>
                                        <? endif ?>
                                    <? endforeach ?>
                                </optgroup>
                            <? endforeach ?>
                        </select>
                    </div>
                    <div style="float:left;width:40px;margin:0 10px;">
                        <input type="button" id="<?= $strHTMLControlName['FORM_NAME'] ?>_btnLeft" value="&lt;&lt;"/>
                        <input type="button" id="<?= $strHTMLControlName['FORM_NAME'] ?>_btnRight" value="&gt;&gt;"/>
                    </div>
                    <div style="float:left;" class="select_selected">
                        <select style="width:430px" name="<?= $strHTMLControlName['VALUE'] ?>[]"
                                id="<?= $strHTMLControlName['FORM_NAME'] ?>_rightValues" size="15" multiple>
                            <? foreach ($arElements as $arElement): ?>
                                <? if (in_array($arElement['ID'], $valuesIds)): ?>
                                    <option selected value="<?= $arElement['ID'] ?>"><?= $arElement['NAME'] ?></option>
                                <? endif ?>
                            <? endforeach ?>
                        </select>
                    </div>
                    <div style="clear:both;"></div>
                </section>
                <script>
                    $(function () {
                        $(document).on('submit', '#<?= $strHTMLControlName['FORM_NAME'] ?>', function () {
                            $("#<?= $strHTMLControlName['FORM_NAME'] ?>_rightValues").find('option').attr('selected', 'selected');
                        });
                    });
                    $(document).on('click', "#<?=$strHTMLControlName['FORM_NAME']?>_btnLeft", function () {
                        var selectedItem = $("#<?=$strHTMLControlName['FORM_NAME']?>_rightValues option:selected");
                        $("#<?=$strHTMLControlName['FORM_NAME']?>_leftValues").append(selectedItem);
                    });

                    $(document).on('click', "#<?=$strHTMLControlName['FORM_NAME']?>_btnRight", function () {
                        var selectedItem = $("#<?=$strHTMLControlName['FORM_NAME']?>_leftValues option:selected");
                        $("#<?=$strHTMLControlName['FORM_NAME']?>_rightValues").append(selectedItem);
                    });
                </script>

                <?
                return ob_get_clean();
            }
        ];
    }
}