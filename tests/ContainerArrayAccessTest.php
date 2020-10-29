<?php

declare(strict_types=1);

namespace Tests;

use Gravatalonga\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @covers \Gravatalonga\Container\Container
 */
final class ContainerArrayAccessTest extends TestCase
{
    public function testCanUseOffsetExists()
    {
        $container = new Container();
        $container['fact'] = static function (ContainerInterface $container) {
            return mt_rand(1, 100);
        };

        self::assertTrue(isset($container['fact']), "cannot find entry using 'isset'");
        self::assertNotEmpty($container['fact'], "cannot find entry using 'empty'");
    }

    public function testCanUseOffsetGet()
    {
        $container = new Container();
        $container['fact'] = static function (ContainerInterface $container) {
            return mt_rand(1, 100);
        };

        self::assertGreaterThanOrEqual(1, $container['fact']);
    }

    public function testCanUseOffsetSet()
    {
        $container = new Container();
        $container['fact'] = static function (ContainerInterface $container) {
            return mt_rand(0, 500000);
        };

        self::assertTrue($container->has('fact'));
        self::assertNotEmpty($container->get('fact'));
        self::assertGreaterThanOrEqual(0, $container->get('fact'));
        self::assertNotSame($container->get('fact'), $container->get('fact'));
    }

    public function testCanUseOffsetUnset()
    {
        $container = new Container();
        $container->share('hello', static function () {
            return 'world';
        });
        $container->set('my', static function (ContainerInterface $container) {
            return 'you';
        });
        $container->set('my-constant', '123');
        $container->get('hello');
        $container->alias('my', 'yours');

        unset($container['hello'], $container['my'], $container['my-constant'], $container['yours']);

        self::assertFalse($container->has('hello'));
        self::assertFalse($container->has('my'));
        self::assertFalse($container->has('my-constant'));
        self::assertFalse($container->has('yours'));
    }

    public function testCanUserAliasFromArraySet()
    {
        $container = new Container();
        $container->share('hello', static function () {
            return 'world';
        });
        $container->alias('hello', 'h');

        self::assertTrue(isset($container['h']));
    }
}
