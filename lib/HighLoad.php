<?php
/**
 * Created by PhpStorm.
 * User: almat
 * Date: 11.09.18
 * Time: 9:53
 */

namespace Zeitnot\Helper;

use Bitrix\Highloadblock as HL;

class HighLoad
{
    private static function getEntity(string $name)
    {
        $entity = HL\HighloadBlockTable::getList(array('filter' => array('NAME' => $name)))->fetch();

        if ($entity) {
            return HL\HighloadBlockTable::compileEntity($entity)->getDataClass();
        } else {
            throw new \Exception('HighLoadBlock not find');
        }
    }

    public static function getElementByName(string $entityName, string $name)
    {
        $entity = self::getEntity($entityName);

        $resDb = $entity::getList(
            array(
                'filter' => array(
                    'UF_NAME' => $name
                )
            )
        );

        if ($result = $resDb->fetch()) {
            return $result;
        } else {
            return false;
        }
    }

}