<?php

declare(strict_types=1);

namespace Tests;

use Gravatalonga\Container\Container;
use Gravatalonga\Container\ContainerException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tests\Stub\Bar;
use Tests\Stub\Dog;
use Tests\Stub\EmptyConstructionTest;
use Tests\Stub\FooBarClass;
use Tests\Stub\FooBarWithNullClass;
use Tests\Stub\FooBarWithoutBuiltInTypeClass;
use Tests\Stub\FooInterface;

/**
 * @internal
 * @coversDefaultClass
 */
final class ResolveConstructionTest extends TestCase
{
    public function testCanResolveBuitinTypeOfFactory()
    {
        $container = new Container();
        $container->set('myVar', '123');
        $container->factory('complexVar', static function (ContainerInterface $container, $myVar = null) {
            return 'abc' . $myVar;
        });

        self::assertTrue($container->has('myVar'));
        self::assertTrue($container->has('complexVar'));
        self::assertSame('123', $container->get('myVar'));
        self::assertSame('abc123', $container->get('complexVar'));
    }

    public function testCanResolveByVarNameFromContainer()
    {
        $container = new Container();
        $container->set('name', 'my-var');

        $class = $container->get(FooBarClass::class);

        self::assertInstanceOf(FooBarClass::class, $class);
        self::assertEquals('my-var', $class->name);
    }

    public function testCanResolveClassFromContainerIfDontHaveArgumentFromConstructor()
    {
        $container = new Container();

        $class = $container->get(EmptyConstructionTest::class);

        self::assertInstanceOf(EmptyConstructionTest::class, $class);
    }

    public function testCanResolveClassFromQualifiedNameConcrete()
    {
        $container = new Container();
        $container->factory(FooInterface::class, static function () {
            return new Bar();
        });

        self::assertInstanceOf(Bar::class, $container->get(FooInterface::class));
    }

    public function testCanResolveConstructionForClass()
    {
        $container = new Container();
        $container->factory(FooInterface::class, static function () {
            return new Bar();
        });

        self::assertInstanceOf(Dog::class, $container->get(Dog::class));
    }

    public function testCanResolveFromContainerWithoutBuiltinType()
    {
        $container = new Container();
        $container->set('name', 'my-var');

        $class = $container->get(FooBarWithoutBuiltInTypeClass::class);

        self::assertInstanceOf(FooBarWithoutBuiltInTypeClass::class, $class);
        self::assertEquals('my-var', $class->name);
    }

    public function testCanResolveToNullIfCantResolveFromContainer()
    {
        $container = new Container();

        $class = $container->get(FooBarWithNullClass::class);

        self::assertInstanceOf(FooBarWithNullClass::class, $class);
        self::assertNull($class->name);
    }

    public function testGotExceptionFromVariableNameAndBuiltTypeNotIntoContainer()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Unable to find type hint (string)');
        $container = new Container();
        $class = $container->get(FooBarClass::class);
    }

    public function testGotExceptionFromVariableNameNotIntoContainer()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Unable to find type hint ()');
        $container = new Container();
        $class = $container->get(FooBarWithoutBuiltInTypeClass::class);
    }

    public function testIfParamIsNullableButWeHaveValueFromContainer()
    {
        $container = new Container();
        $container->set('name', 'my-var');

        $class = $container->get(FooBarWithNullClass::class);

        self::assertInstanceOf(FooBarWithNullClass::class, $class);
        self::assertEquals('my-var', $class->name);
    }
}
