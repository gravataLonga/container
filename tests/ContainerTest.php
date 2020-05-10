<?php

declare(strict_types=1);

namespace Tests;

use Gravatalonga\Container\Container;
use Gravatalonga\Container\ContainerException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

/**
 * @internal
 * @coversDefaultClass
 */
final class ContainerTest extends TestCase
{
    public function testCanBindDependency()
    {
        $rand = mt_rand(0, 10);
        $container = new Container();
        $container->factory('random', static function () use ($rand) {
            return $rand;
        });

        self::assertEquals($rand, $container->get('random'));
    }

    public function testCanCheckIfEntryExistOnContainer()
    {
        $container = new Container(['db' => 'my-db']);
        self::assertTrue($container->has('db'));
        self::assertFalse($container->has('key-not-exists'));
    }

    public function testCanCreateContainer()
    {
        $container = new Container();
        self::assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testCanGetContainerFromFactory()
    {
        $rand = mt_rand(0, 1000);
        $container = new Container();
        $container->factory('random', static function () use ($rand) {
            return $rand;
        });

        $container->factory('random1', static function (ContainerInterface $container) {
            return $container->get('random');
        });

        self::assertEquals($container->get('random'), $container->get('random1'));
    }

    public function testCanGetDifferentValueFromContainer()
    {
        $container = new Container();
        $container->factory('random', static function () {
            return mt_rand(0, 1000);
        });
        self::assertNotEquals($container->get('random'), $container->get('random'));
    }

    public function testCanGetInstanceFromShareBinding()
    {
        $container = new Container();
        $container->share('random', static function () {
            return mt_rand(1, 1000);
        });
        self::assertGreaterThan(0, $container->get('random'));
    }

    public function testCanGetInstanceOfContainer()
    {
        $container = new Container();
        $container::setInstance($container);

        self::assertInstanceOf(ContainerInterface::class, Container::getInstance());
        self::assertSame($container, Container::getInstance());
    }

    public function testCanGetValueFromContainer()
    {
        $container = new Container(['config' => true]);
        self::assertTrue($container->get('config'));
    }

    public function testCanOnlyAcceptStringForEntry()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Entry type must be string');
        $container = new Container();
        $container->set(static function () {
        }, '123');
    }

    public function testCanSetDirectValueRatherThanCallback()
    {
        $container = new Container();

        $container->set('hello', 'world');
        $container->set('abc', 123);
        $container->set('object', new stdClass());

        self::assertSame('world', $container->get('hello'));
        self::assertSame(123, $container->get('abc'));
        self::assertInstanceOf(stdClass::class, $container->get('object'));
    }

    public function testCanShareSameBindingAndCanCheckIfExists()
    {
        $container = new Container();
        $container->share('random', static function () {
            return new stdClass();
        });
        self::assertTrue($container->has('random'));
    }

    public function testIHaveSetMethodAliasForFactory()
    {
        $container = new Container();
        $container->set('random', static function () {
            return new stdClass();
        });
        self::assertNotSame($container->get('random'), $container->get('random'));
    }

    public function testMustThrowExceptionWhenTryGetEntryDontExists()
    {
        $container = new Container();
        $this->expectException(NotFoundExceptionInterface::class);
        $container->get('entry');
    }

    public function testWhenResolveFromShareBindingItReturnSameValue()
    {
        $container = new Container();
        $container->share('random', static function () {
            return mt_rand(1, 1000);
        });
        self::assertEquals($container->get('random'), $container->get('random'));
    }
}
