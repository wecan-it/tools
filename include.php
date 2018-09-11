<?php
use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

Loader::registerAutoLoadClasses(
    'wecan.tools',
    [
        'Helper'      => 'helper.php',
        'ArrayHelper' => 'arrayhelper.php'
    ]
);

if(is_dir($eventsDir = realpath(dirname(__FILE__) . '/../../php_interface/events'))) {
    $em = \Bitrix\Main\EventManager::getInstance();
    $eventsDir = new \Bitrix\Main\IO\Directory($eventsDir);
    foreach ($eventsDir->getChildren() as $child) {
        if (
            $child instanceof File
            && ($moduleName = rtrim($child->getName(), '.php'))
            && ModuleManager::isModuleInstalled($moduleName)
        ) {
            $events = require $child->getPath();
            if(is_array($events) && !empty($events)) {
                foreach($events as $event => $handler) {
                    $em->addEventHandler($moduleName, $event, $handler);
                }
            }
        }
    }
}

//\Wecan\Tools\AliasLoader::getInstance([
//    'App'     => '\Bitrix\Main\Application',
//    'Context' => '\Bitrix\Main\Context',
//])->register();