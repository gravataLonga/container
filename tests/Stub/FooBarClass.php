<?php

declare(strict_types=1);

namespace Tests\Stub;

class FooBarClass
{
    /**
     * @var string
     */
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
