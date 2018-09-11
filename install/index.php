<?php
IncludeModuleLangFile(__FILE__);

class wecan_tools  extends CModule
{
    public $MODULE_ID = "wecan.tools";
    public $MODULE_NAME;

    public function __construct() {
        $this->MODULE_NAME = "WeCan tools";
    }

    public function InstallFiles() {
        return true;
    }

    public function UnInstallFiles() {
        return true;
    }

    public function DoInstall() {
        RegisterModule($this->MODULE_ID);
        $this->InstallFiles();
        if(\Bitrix\Main\Loader::includeModule($this->MODULE_ID)) {
            \Bitrix\Main\EventManager::getInstance()->registerEventHandler(
                'iblock',
                'OnIBlockPropertyBuildList',
                $this->MODULE_ID,
                \Wecan\Tools\Properties\MultiSelect::class,
                'GetPropertyFieldHtmlMulty'
            );
        }
    }

    public function DoUninstall() {
        UnRegisterModule($this->MODULE_ID);
        $this->UnInstallFiles();
    }
}
