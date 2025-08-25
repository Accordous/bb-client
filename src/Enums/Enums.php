<?php

namespace Accordous\BbClient\Enums;

use ReflectionClass;

abstract class Enums
{
    public static function isValid(string $type)
    {
        return in_array($type, (new ReflectionClass(static::class))->getConstants());
    }
}