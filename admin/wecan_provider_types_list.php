<?
use Wecan\Tools\ProviderTypeTable;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

if (!\Bitrix\Main\Loader::includeModule("wecan.tools")) {
    die('module not included!');
}
\Bitrix\Main\Loader::includeModule('iblock');
IncludeModuleLangFile(__FILE__);

$listTableId = "tbl_wecan_provider_types";
$adminList = new CAdminList($listTableId);

if (($arID = $adminList->GroupAction())) {
    if ($_REQUEST['action_target'] == 'selected') {
        $arID = [];
        $rsData = ProviderTypeTable::getList([
            "select" => [
                "ID"
            ],
        ]);

        while ($arRes = $rsData->fetch()) {
            $arID[] = $arRes['ID'];
        }
    }

    foreach ($arID as $ID) {
        $ID = intval($ID);
        if ($ID <= 0) {
            continue;
        }

        switch ($_REQUEST['action']) {
//            case "delete":
//                ProviderTypeTable::delete($ID);
//                break;
        }
    }
}

$obGroups = \Bitrix\Iblock\PropertyEnumerationTable::getList(
    [
        'order'   =>
            [
                'ID' => 'ASC'
            ],
        'select'  =>
            [
                'ID',
                'VALUE',
                'TITLE'           => 'PROVIDER.TITLE',
                'BROWSER_TITLE'   => 'PROVIDER.BROWSER_TITLE',
                'META_KEYWORDS'   => 'PROVIDER.META_KEYWORDS',
                'META_DESCRIPTION' => 'PROVIDER.META_DESCRIPTION',
            ],
        'filter'  =>
            [
                'PROPERTY_ID' => PROPERTY_POSTAVKA_VID_ID
            ],
        'runtime' =>
            [
                new \Bitrix\Main\Entity\ReferenceField('PROVIDER', '\Wecan\Tools\ProviderType', [
                    '=this.ID' => 'ref.ENUM_VALUE_ID'
                ])
            ]
    ]
);

$obGroups = new CAdminResult($obGroups, $listTableId);
$obGroups->NavStart();

$adminList->NavText($obGroups->GetNavPrint("Разделы"));

$colHeaders = [
    [
        "id"      => 'ID',
        "content" => 'ID',
        "sort"    => 1,
        "default" => true
    ],
    [
        "id"      => 'VALUE',
        "content" => 'VALUE',
        "sort"    => 2,
        "default" => true
    ],
    [
        "id"      => 'TITLE',
        "content" => 'TITLE',
        "sort"    => 3,
        "default" => true
    ],
];

$adminList->AddHeaders($colHeaders);

$visibleHeaderColumns = $adminList->GetVisibleHeaderColumns();
$arUsersCache = [];

while ($arRes = $obGroups->GetNext()) {
    $row =& $adminList->AddRow($arRes["ID"], $arRes);
    $arActions = [
//        [
//            "ICON"   => "delete",
//            "TEXT"   => "Удалить",
//            "ACTION" => $adminList->ActionDoGroup($arRes["ID"], "delete"),
//        ],
[
    "ICON"    => "edit",
    "TEXT"    => "Редактировать",
    "ACTION"  => $adminList->ActionRedirect("wecan_provider_types_edit.php?ID=" . $arRes["ID"] . "&lang=" . LANGUAGE_ID),
    "DEFAULT" => true,
]
    ];

    $row->AddActions($arActions);
}

$adminList->AddFooter(
    [
        [
            "title" => "Всего",
            "value" => $obGroups->SelectedRowsCount()
        ],
        [
            "counter" => true,
            "title"   => "Отмечено",
            "value"   => "0"
        ],
    ]
);
#$adminList->AddGroupActionTable(["delete" => "Удалить"]);
$aContext = [
//    [
//        "TEXT"  => GetMessage("MAIN_ADD"),
//        "LINK"  => "wecan_provider_types_edit.php.php",
//        "TITLE" => GetMessage("POST_ADD_TITLE"),
//        "ICON"  => "btn_new",
//    ],
];
$adminList->AddAdminContextMenu($aContext);
$adminList->CheckListMode();

$APPLICATION->SetTitle("Типы поставщиков");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$adminList->DisplayList();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>