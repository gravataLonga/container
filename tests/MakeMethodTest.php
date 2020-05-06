<?php

namespace Tests;

use Gravatalonga\Container\Container;
use Gravatalonga\Container\ContainerException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tests\Stub\Bar;
use Tests\Stub\FooBarClass;

class MakeMethodTest extends TestCase
{
    /**
     * @test
     * @throws ContainerException
     * @throws \Gravatalonga\Container\NotFoundContainerException
     * @throws \ReflectionException
     */
    public function make_accepted_only_entry_name()
    {
        $container = new Container();
        $container->set('myFactory', function (ContainerInterface $container) {
           return 'Hello World';
        });

        $this->assertTrue($container->has('myFactory'));
        $this->assertEquals('Hello World', $container->make('myFactory'));
    }

    /**
     * @test
     */
    public function make_it_can_resolve_class()
    {
        $container = new Container();

        $bar = $container->make(Bar::class);
        $this->assertInstanceOf(Bar::class, $bar);
        $this->assertNotSame($bar, $container->make(Bar::class));
    }

    /**
     * @test
     */
    public function can_resolve_argument_of_class_by_container()
    {
        $container = new Container();
        $container->set('name', 'my-var');

        $bar = $container->make(FooBarClass::class);
        $this->assertInstanceOf(FooBarClass::class, $bar);
        $this->assertNotSame($bar, $container->make(FooBarClass::class));
    }

    /**
     * @test
    */
    public function pass_argument_to_be_resolve_by_array()
    {
        $container = new Container();

        $bar = $container->make(FooBarClass::class, ['name' => 'my-var-hello']);
        $this->assertEquals('my-var-hello', $bar->name);
        $this->assertInstanceOf(FooBarClass::class, $bar);
        $this->assertNotSame($bar, $container->make(FooBarClass::class, ['name' => 'other class']));
    }

    /**
     * @test
     */
    public function cannot_call_make_on_share_entry()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage("Entry is share and cannot be called on make: entry");
        $container = new Container();
        $container->share('entry', function (ContainerInterface $container, $arg, $arg2) {
            return $arg.' '.$arg2;
        });

        $container->make('entry', ['arg' => 1, 'arg2' => 2]);
    }
}
