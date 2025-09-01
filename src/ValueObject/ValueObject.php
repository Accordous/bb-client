<?php

namespace Accordous\BbClient\ValueObject;

abstract class ValueObject
{
    public function toJson(): string|false
    {
        return json_encode($this, JSON_UNESCAPED_UNICODE);
    }

    public function toArray(): array
    {
        return json_decode(json_encode($this), true);
    }
}