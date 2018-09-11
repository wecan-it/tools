<?
// подключим все необходимые файлы:
use Wecan\Tools\ProviderTypeTable;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог

\Bitrix\Main\Loader::includeModule('wecan.tools');
\Bitrix\Main\Loader::includeModule('iblock');
CJSCore::init('jquery');

// подключим языковой файл
IncludeModuleLangFile(__FILE__);

$aTabs = [
    ["DIV" => "edit1", "TAB" => "Основное", "ICON" => "main_user_edit", "TITLE" => "Настройки"],
];
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);        // идентификатор редактируемой записи
$message = null;        // сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

// ******************************************************************** //
//                ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ                             //
// ******************************************************************** //

if (
    $REQUEST_METHOD == "POST" // проверка метода вызова страницы
    &&
    ($save != "" || $apply != "") // проверка нажатия кнопок "Сохранить" и "Применить"
    &&
    check_bitrix_sessid()     // проверка идентификатора сессии
) {
    // обработка данных формы
    $arFields = compact('TITLE', 'BROWSER_TITLE', 'META_KEYWORDS', 'META_DESCRIPTION');
    $arFields['ENUM_VALUE_ID'] = $ID;

    // сохранение данных

    $type = ProviderTypeTable::getRow([
        'filter' =>
            [
                'ENUM_VALUE_ID' => $ID
            ]
    ]);

    if ($type) {
        $res = ProviderTypeTable::update($type['ID'], $arFields);
    } else {
        $res = ProviderTypeTable::add($arFields);
    }

    if ($res->isSuccess()) {
        if (!$ID) {
            $ID = $res->getId();
        }
        if ($apply != "") {
            LocalRedirect("/bitrix/admin/wecan_provider_types_edit.php?ID=" . $ID . "&mess=ok");
        } else {
            LocalRedirect("/bitrix/admin/wecan_provider_types_list.php");
        }
    } else {
        foreach ($res->getErrorMessages() as $error_message) {
            $message = new CAdminMessage($error_message);
            break;
        }

        $bVarsFromForm = true;
    }
}

// ******************************************************************** //
//                ВЫБОРКА И ПОДГОТОВКА ДАННЫХ ФОРМЫ                     //
// ******************************************************************** //

// выборка данных
if ($ID > 0) {
    $enum = \Bitrix\Iblock\PropertyEnumerationTable::getRow([
        'filter'  =>
            [
                '=ID' => $ID
            ],
        'select'  =>
            [
                'ID',
                'VALUE',
                'TITLE'            => 'PROVIDER.TITLE',
                'BROWSER_TITLE'    => 'PROVIDER.BROWSER_TITLE',
                'META_KEYWORDS'    => 'PROVIDER.META_KEYWORDS',
                'META_DESCRIPTION' => 'PROVIDER.META_DESCRIPTION',
            ],
        'runtime' =>
            [
                new \Bitrix\Main\Entity\ReferenceField('PROVIDER', '\Wecan\Tools\ProviderType', [
                    '=this.ID' => 'ref.ENUM_VALUE_ID'
                ])
            ]
    ]);
    $APPLICATION->SetTitle("Редактирование {$enum['VALUE']}");
}

// ******************************************************************** //
//                ВЫВОД ФОРМЫ                                           //
// ******************************************************************** //

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

// конфигурация административного меню
$aMenu = [
    [
        "TEXT"  => "Список",
        "TITLE" => "Список",
        "LINK"  => "wecan_provider_types_list.php",
        "ICON"  => "btn_list",
    ],
    //    [
    //        "TEXT"  => "Удалить",
    //        "TITLE" => "Удалить",
    //        "LINK"  => "javascript:if(confirm('" . "Да, прошу вас" . "')) window.location='/bitrix/admin/wecan_provider_types_list.php?ID=" . $ID . "&action=delete&lang=" . LANGUAGE_ID . "&" . bitrix_sessid_get() . "';",
    //        "ICON"  => "btn_delete"
    //    ]
];

// создание экземпляра класса административного меню
$context = new CAdminContextMenu($aMenu);

// вывод административного меню
$context->Show();
?>

<?
// если есть сообщения об ошибках или об успешном сохранении - выведем их.
if ($_REQUEST["mess"] == "ok" && $ID > 0) {
    CAdminMessage::ShowMessage(["MESSAGE" => 'Success', "TYPE" => "OK"]);
}

if ($message) {
    echo $message->Show();
}
?>
<form method="POST" action="<?= $APPLICATION->GetCurPage() ?>" name="wecan_edit_form">
<? // проверка идентификатора сессии ?>
<? echo bitrix_sessid_post(); ?>
<?
// отобразим заголовки закладок
$tabControl->Begin();
?>
<?
//********************
// первая закладка - форма редактирования параметров рассылки
//********************
$tabControl->BeginNextTab();
?>
    <tr>
        <td width="40%">Заголовок страницы</td>
        <td width="60%">
            <textarea cols="70" rows="3"
                      name="TITLE"><?= ($bVarsFromForm) ? $_POST['TITLE'] : $enum['TITLE'] ?></textarea>
        </td>
    </tr>
    <tr>
        <td width="40%">Заголовок окна браузера</td>
        <td width="60%">
            <textarea cols="70" rows="3"
                      name="BROWSER_TITLE"><?= ($bVarsFromForm) ? $_POST['BROWSER_TITLE'] : $enum['BROWSER_TITLE'] ?></textarea>
        </td>
    </tr>
    <tr>
        <td width="40%">Ключевые слова</td>
        <td width="60%">
            <textarea cols="70" rows="3"
                      name="META_KEYWORDS"><?= ($bVarsFromForm) ? $_POST['META_KEYWORDS'] : $enum['META_KEYWORDS'] ?></textarea>
        </td>
    </tr>
    <tr>
        <td width="40%">Описание</td>
        <td width="60%">
            <textarea cols="70" rows="3"
                      name="META_DESCRIPTION"><?= ($bVarsFromForm) ? $_POST['META_DESCRIPTION'] : $enum['META_DESCRIPTION'] ?></textarea>
        </td>
    </tr>
<?
// завершение формы - вывод кнопок сохранения изменений
$tabControl->Buttons(
    [
        "back_url" => "wecan_provider_types_list.php",
    ]
);
?>

<? if ($ID > 0 && !$bCopy): ?>
    <input type="hidden" name="ID" value="<?= $ID ?>">
<? endif; ?>
<?
// завершаем интерфейс закладок
$tabControl->End();
?>

<?
// дополнительное уведомление об ошибках - вывод иконки около поля, в котором возникла ошибка
$tabControl->ShowWarnings("post_form", $message);
?>

<?
// завершение страницы
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");