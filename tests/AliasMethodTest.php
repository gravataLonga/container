<?php

declare(strict_types=1);

namespace Tests;

use Gravatalonga\Container\Aware;
use Gravatalonga\Container\NotFoundContainerException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tests\Stub\Bar;
use Tests\Stub\FooBarWithNullClass;

/**
 * @internal
 * @coversDefaultClass
 */
final class AliasMethodTest extends TestCase
{
    public function testAliasOneVarToAnotherClass()
    {
        $container = new Aware();
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
        $container = new Aware();
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
        $container = new Aware();
        $container->set('foobar', 'Hello World');

        $container->alias('foobar', 'hello');

        self::assertTrue($container->has('hello'));
        self::assertEquals('Hello World', $container->get('hello'));
        self::assertSame($container->get('foobar'), $container->get('hello'));
    }

    public function testCanUseMakeOverAlias()
    {
        $container = new Aware();
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
        $container = new Aware();

        self::assertTrue($container->has(ContainerInterface::class));
        self::assertTrue($container->has(Aware::class));
        self::assertSame($container->get(ContainerInterface::class), $container->get(ContainerInterface::class));
        self::assertSame($container->get(Aware::class), $container->get(Aware::class));
        self::assertSame($container->get(ContainerInterface::class), $container->get(Aware::class));
    }

    public function testGotExceptionIfAliasReferedToNonEntry()
    {
        $this->expectException(NotFoundContainerException::class);
        $this->expectExceptionMessage('Entry bar not found');
        $container = new Aware();

        $container->alias('bar', 'foo');

        self::assertFalse($container->has('foo'));
    }
}
