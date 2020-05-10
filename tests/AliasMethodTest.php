<?php

declare(strict_types=1);

namespace Tests;

use Gravatalonga\Container\Container;
use Gravatalonga\Container\NotFoundContainerException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tests\Stub\Bar;
use Tests\Stub\FooBarWithNullClass;

/**
 * @internal
 * @coversNothing
 */
final class AliasMethodTest extends TestCase
{
    public function testAliasOneVarToAnotherClass()
    {
        $container = new Container();
        $container->set(Bar::class, static function () {
            return new Bar();
        });

        $container->alias(Bar::class, 'bar');

        self::assertTrue($container->has('bar'));
        self::assertTrue($container->has(Bar::class));
        self::assertInstanceOf(Bar::class, $container->get('bar'));
        self::assertNotSame($container->get('bar'), $container->get('bar'));
        self::assertNotSame($container->get('bar'), $container->get(Bar::class));
    }

    public function testAliasOneVarToAnotherShareEntry()
    {
        $container = new Container();
        $container->share(Bar::class, static function () {
            return new Bar();
        });

        $container->alias(Bar::class, 'bar');

        self::assertTrue($container->has('bar'));
        self::assertTrue($container->has(Bar::class));
        self::assertInstanceOf(Bar::class, $container->get('bar'));
        self::assertSame($container->get('bar'), $container->get('bar'));
        self::assertSame($container->get('bar'), $container->get(Bar::class));
    }

    public function testAliasOneVarToAnotherVar()
    {
        $container = new Container();
        $container->set('foobar', 'Hello World');

        $container->alias('foobar', 'hello');

        self::assertTrue($container->has('hello'));
        self::assertEquals('Hello World', $container->get('hello'));
        self::assertSame($container->get('foobar'), $container->get('hello'));
    }

    public function testCanUseMakeOverAlias()
    {
        $container = new Container();
        $container->factory(FooBarWithNullClass::class, static function (ContainerInterface $container, $name = null) {
            return new FooBarWithNullClass($name);
        });

        $container->alias(FooBarWithNullClass::class, 'foo.bar');

        $foo = $container->make(FooBarWithNullClass::class, ['name' => 'Hello World']);
        self::assertTrue($container->has(FooBarWithNullClass::class));
        self::assertTrue($container->has('foo.bar'));
        self::assertInstanceOf(FooBarWithNullClass::class, $foo);
        self::assertEquals('Hello World', $foo->name);
    }

    public function testContainerInterfaceIsAliasOfItSelf()
    {
        $container = new Container();

        self::assertTrue($container->has(ContainerInterface::class));
        self::assertTrue($container->has(Container::class));
        self::assertSame($container->get(ContainerInterface::class), $container->get(ContainerInterface::class));
        self::assertSame($container->get(Container::class), $container->get(Container::class));
        self::assertSame($container->get(ContainerInterface::class), $container->get(Container::class));
    }

    public function testGotExceptionIfAliasReferedToNonEntry()
    {
        $this->expectException(NotFoundContainerException::class);
        $this->expectExceptionMessage('Entry bar not found');
        $container = new Container();

        $container->alias('bar', 'foo');

        self::assertFalse($container->has('foo'));
    }
}
