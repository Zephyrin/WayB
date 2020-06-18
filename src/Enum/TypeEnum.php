<?php

namespace App\Enum;

use ReflectionClass;
use UnexpectedValueException;

abstract class TypeEnum
{
    /**
     * @param  string $typeShortName
     * @return string
     */
    public static function getByName($typeShortName)
    {
        $availableConstList = self::getAvailableConstantList();

        if (isset($availableConstList[$typeShortName]))
            return $availableConstList[$typeShortName];
        else {
            throw new UnexpectedValueException('Unknown constant name');
        }
    }

    /**
     * @return string[]
     */
    public static function getAvailableTypes()
    {
        return self::getAvailableConstantList();
    }

    /**
     * @param $constantName - value to check
     * @return bool
     */
    public static function isValidName($constantName)
    {
        $availableConstList = self::getAvailableConstantList();
        return isset($availableConstList[$constantName]);
    }

    /**
     * @param $constantValue - value to check
     * @return bool
     */
    public static function isValidValue($constantValue)
    {
        $availableConstList = self::getAvailableConstantList();
        return array_search($constantValue, $availableConstList, true) !== false;
    }

    /**
     * returns a array with available constants
     * @return array|null
     */
    private static function getAvailableConstantList()
    {
        return (new ReflectionClass(static::class))->getConstants();
    }
}