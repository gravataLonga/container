<?php

declare(strict_types=1);

namespace Tests;

use Gravatalonga\Container\Container;
use Gravatalonga\Container\NotFoundContainerException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Tests\Stub\AnimalInterface;
use Tests\Stub\Bar;
use Tests\Stub\Dog;
use Tests\Stub\FooInterface;
use TypeError;

/**
 * @internal
 * @covers \Gravatalonga\Container\Container
 */
final class ExtendedMethodTest extends TestCase
{
    public function testCanExtendedAlreadyBindedEntry()
    {
        $container = new Container();
        $container->set(FooInterface::class, static function () {
            return new Bar();
        });
        $container->factory(AnimalInterface::class, static function (ContainerInterface $c) {
            return new Dog($c->get(FooInterface::class));
        });

        $container->extend(AnimalInterface::class, function (ContainerInterface $container, AnimalInterface $dog) {
            return new class($dog) implements AnimalInterface {
                public $dog;

                public function __construct($dog)
                {
                    $this->dog = $dog;
                }
            };
        });

        self::assertTrue($container->has(FooInterface::class));
        self::assertTrue($container->has(AnimalInterface::class));
        self::assertFalse($container->isAlias(AnimalInterface::class));
        $animal = $container->get(AnimalInterface::class);
        self::assertInstanceOf(AnimalInterface::class, $animal);
        self::assertNotInstanceOf(Dog::class, $animal);
        self::assertInstanceOf(Dog::class, $animal->dog);
    }

    public function testCanExtendedFromCallableArray()
    {
        $class = $this->newClass('Jonathan');
        $container = new Container();
        $container->factory('hello', static function () {
            return 'Hello';
        });
        $container->extend('hello', [$class, 'setName']);

        self::assertTrue($container->has('hello'));
        self::assertEquals('Hello Jonathan', $container->get('hello'));
    }

    public function testCanExtendedShareIfNotResolvedYet()
    {
        $container = new Container();
        $container->share('foo', static function () {
            return 'Hello Foo';
        });

        $container->extend('foo', static function (ContainerInterface $container, $foo) {
            return 'He say: ' . $foo;
        });

        self::assertTrue($container->has('foo'));
        self::assertFalse($container->isAlias('foo'));
        self::assertEquals('He say: Hello Foo', $container->get('foo'));
    }

    public function testExtendItsOnlyCallBeforeResolvingFromContainer()
    {
        $container = new Container();
        $container->set('foo', static function () {
            return 'Hello Foo';
        });
        $firstValue = $container->get('foo');

        $container->extend('foo', static function (ContainerInterface $container, $foo) {
            return 'He say: ' . $foo;
        });
        $secondValue = $container->get('foo');

        self::assertTrue($container->has('foo'));
        self::assertNotSame($firstValue, $secondValue);
        self::assertSame('Hello Foo', $firstValue);
        self::assertSame('He say: Hello Foo', $secondValue);
        self::assertEquals('He say: Hello Foo', $container->get('foo'));
    }

    public function testIfShareAlreadyResolveMustBeResolvedAgain()
    {
        $firstClass = new class() {
        };
        $secondClass = new class() {
        };

        $container = new Container();
        $container->share('hello', static function () use ($firstClass) {
            return $firstClass;
        });
        $firstValue = $container->get('hello');
        $container->extend('hello', static function () use ($secondClass) {
            return $secondClass;
        });
        $secondValue = $container->get('hello');

        self::assertTrue($container->has('hello'));
        self::assertSame($firstClass, $firstValue);
        self::assertSame($secondClass, $secondValue);
        self::assertNotSame($firstValue, $secondValue);
    }

    public function testThrowExceptionIfAttemptExtendedNotFoundEntry()
    {
        $this->expectException(NotFoundContainerException::class);
        $this->expectExceptionMessage('Entry foo not found');
        $container = new Container();
        $container->extend('foo', static function (ContainerInterface $container, $foo) {
            return 'He say: ' . $foo;
        });
    }

    public function testThrowExceptionIfExtendedIsNotCallable()
    {
        $this->expectException(TypeError::class);
        $container = new Container();
        $container->factory('hello', static function () {
            return 'Hello';
        });
        $container->extend('hello', 'Olá');
    }

    public function testWhenUnsetItRemoveExtendedEntriesAlso()
    {
        $container = new Container();
        $container->factory('hello', static function () {
            return 'Hello';
        });
        $container->extend('hello', static function ($c, $a) {
            return $a;
        });

        unset($container['hello']);
        $ref = new ReflectionClass($container);
        $ex = $ref->getProperty('extended');
        $ex->setAccessible(true);

        self::assertArrayNotHasKey('hello', $ex->getValue($container));
    }

    private function newClass($arg = null)
    {
        return new class($arg) {
            /**
             * @var int
             */
            private static $arg;

            public function __construct($arg)
            {
                self::$arg = $arg;
            }

            public function get(): int
            {
                return self::$arg;
            }

            public function setName(ContainerInterface $c, $hello)
            {
                return self::$arg = $hello . ' ' . self::$arg;
            }
        };
    }
}
