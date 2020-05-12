<?php

declare(strict_types=1);

namespace Tests\Stub;

class FooBarWithoutBuiltInTypeNullableClass
{
    public $name;

    public function __construct($name = null)
    {
        $this->name = $name;
    }
}
