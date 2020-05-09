<?php

declare(strict_types=1);

namespace Tests\Stub;

class FooBarWithNullClass
{
    /**
     * @var string
     */
    public $name;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }
}
