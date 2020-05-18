<?php

declare(strict_types=1);

namespace Tests\Stub;

class CircularDependencyValue2
{
    /**
     * @var CircularDependencyValue1
     */
    private $c;

    public function __construct(CircularDependencyValue1 $c)
    {
        $this->c = $c;
    }
}
