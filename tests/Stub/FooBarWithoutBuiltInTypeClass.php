<?php

declare(strict_types=1);

namespace Tests\Stub;

class FooBarWithoutBuiltInTypeClass
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
