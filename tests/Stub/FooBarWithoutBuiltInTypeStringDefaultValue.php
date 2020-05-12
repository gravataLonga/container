<?php

namespace Tests\Stub;

class FooBarWithoutBuiltInTypeStringDefaultValue
{
    /**
     * @var string
     */
    public $name;

    public function __construct($name = 'hello world')
    {
        $this->name = $name;
    }
}
