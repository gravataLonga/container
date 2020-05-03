<?php

namespace Tests;

use Gravatalonga\Container\Container;
use PHPUnit\Framework\TestCase;
use Tests\Stub\Bar;
use Tests\Stub\Dog;
use Tests\Stub\FooInterface;

class ResolveConstructionTest extends TestCase
{
    /**
     * @test
     */
    public function can_resolve_class_from_qualified_name_concrete()
    {
        $container = new Container();
        $container->factory(FooInterface::class, function () {
            return new Bar();
        });

        $this->assertInstanceOf(Bar::class, $container->get(FooInterface::class));
    }

    /**
     * @test
     */
    public function can_resolve_construction_for_class()
    {
        $container = new Container();
        $container->factory(FooInterface::class, function () {
            return new Bar();
        });

        $this->assertInstanceOf(Dog::class, $container->get(Dog::class));
    }
}
