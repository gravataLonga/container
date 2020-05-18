<?php

declare(strict_types=1);

namespace Tests\Stub;

class CircularDependencyValue1
{
    public function __construct(CircularDependencyValue2 $c)
    {
        $this->c = $c;
    }
}
