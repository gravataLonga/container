<?php

declare(strict_types=1);

namespace Tests;

use Gravatalonga\Container\Container;
use Gravatalonga\Container\ContainerException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use Tests\Stub\Bar;
use Tests\Stub\FooBarClass;

/**
 * @internal
 * @covers \Gravatalonga\Container\Container
 */
final class MakeMethodTest extends TestCase
{
    public function testCannotCallMakeOnShareEntry()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Entry is shared and cannot be called on make: entry');
        $container = new Container();
        $container->share('entry', static function (ContainerInterface $container, $arg, $arg2) {
            return $arg . ' ' . $arg2;
        });

        $container->make('entry', ['arg' => 1, 'arg2' => 2]);
    }

    public function testCanResolveArgumentOfClassByContainer()
    {
        $container = new Container();
        $container->set('name', 'my-var');

        $bar = $container->make(FooBarClass::class);
        self::assertInstanceOf(FooBarClass::class, $bar);
        self::assertNotSame($bar, $container->make(FooBarClass::class));
    }

    /**
     * @throws ContainerException
     * @throws \Gravatalonga\Container\NotFoundContainerException
     * @throws ReflectionException
     */
    public function testMakeAcceptedOnlyEntryName()
    {
        $container = new Container();
        $container->set('myFactory', static function (ContainerInterface $container) {
            return 'Hello World';
        });

        self::assertTrue($container->has('myFactory'));
        self::assertEquals('Hello World', $container->make('myFactory'));
    }

    public function testMakeItCanResolveClass()
    {
        $container = new Container();

        $bar = $container->make(Bar::class);
        self::assertInstanceOf(Bar::class, $bar);
        self::assertNotSame($bar, $container->make(Bar::class));
    }

    public function testPassArgumentToBeResolveByArray()
    {
        $container = new Container();

        $bar = $container->make(FooBarClass::class, ['name' => 'my-var-hello']);
        self::assertEquals('my-var-hello', $bar->name);
        self::assertInstanceOf(FooBarClass::class, $bar);
        self::assertNotSame($bar, $container->make(FooBarClass::class, ['name' => 'other class']));
    }
}
