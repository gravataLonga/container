<?php

namespace Tests\Stub;

class FooBarWithCustomTypes
{
    /**
     * @var Bar
     */
    public $bar;

    /**
     * @var Cat
     */
    public $cat;

    public function __construct(Bar $bar, Cat $cat)
    {
        $this->bar = $bar;
        $this->cat = $cat;
    }
}
