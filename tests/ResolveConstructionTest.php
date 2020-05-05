<?php

namespace Tests;

use Gravatalonga\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tests\Stub\Bar;
use Tests\Stub\Dog;
use Tests\Stub\EmptyConstructionTest;
use Tests\Stub\FooBarClass;
use Tests\Stub\FooBarWithNullClass;
use Tests\Stub\FooBarWithoutBuiltInTypeClass;
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

    /**
     * @test
     */
    public function can_resolve_by_var_name_from_container()
    {
        $container = new Container();
        $container->set('name', 'my-var');

        $class = $container->get(FooBarClass::class);

        $this->assertInstanceOf(FooBarClass::class, $class);
        $this->assertEquals('my-var', $class->name);
    }

    /**
     * @test
     */
    public function can_resolve_to_null_if_cant_resolve_from_container()
    {
        $container = new Container();

        $class = $container->get(FooBarWithNullClass::class);

        $this->assertInstanceOf(FooBarWithNullClass::class, $class);
        $this->assertEquals(null, $class->name);
    }

    /**
     * @test
     */
    public function if_param_is_nullable_but_we_have_value_from_container()
    {
        $container = new Container();
        $container->set('name', 'my-var');

        $class = $container->get(FooBarWithNullClass::class);

        $this->assertInstanceOf(FooBarWithNullClass::class, $class);
        $this->assertEquals('my-var', $class->name);
    }

    /**
     * @test
     */
    public function can_resolve_from_container_without_builtin_type()
    {
        $container = new Container();
        $container->set('name', 'my-var');

        $class = $container->get(FooBarWithoutBuiltInTypeClass::class);

        $this->assertInstanceOf(FooBarWithoutBuiltInTypeClass::class, $class);
        $this->assertEquals('my-var', $class->name);
    }

    /**
     * @test
     */
    public function can_resolve_class_from_container_if_dont_have_argument_from_constructor()
    {
        $container = new Container();

        $class = $container->get(EmptyConstructionTest::class);

        $this->assertInstanceOf(EmptyConstructionTest::class, $class);
    }

    /**
     * @test
     */
    public function can_resolve_buitin_type_of_factory()
    {
        $container = new Container();
        $container->set('myVar', '123');
        $container->factory('complexVar', function (ContainerInterface $container, $myVar = null) {
            return 'abc'.$myVar;
        });

        $this->assertTrue($container->has('myVar'));
        $this->assertTrue($container->has('complexVar'));
        $this->assertSame('123', $container->get('myVar'));
        $this->assertSame('abc123', $container->get('complexVar'));
    }
}
