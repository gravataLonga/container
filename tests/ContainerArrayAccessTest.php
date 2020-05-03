<?php

namespace Tests;

use Gravatalonga\Container\Container;
use PHPUnit\Framework\TestCase;

class ContainerArrayAccessTest extends TestCase
{
    /**
     * @test
     */
    public function can_use_offset_set()
    {
        $container = new Container();
        $container['fact'] = function ($container) {
            return rand(0, 100);
        };

        $this->assertTrue($container->has('fact'));
        $this->assertNotEmpty($container->get('fact'));
        $this->assertGreaterThanOrEqual(0, $container->get('fact'));
        $this->assertNotSame($container->get('fact'), $container->get('fact'));
    }

    /**
     * @test
     */
    public function can_use_offset_get()
    {
        $container = new Container();
        $container['fact'] = function ($container) {
            return rand(1, 100);
        };

        $this->assertGreaterThanOrEqual(1, $container['fact']);
    }

    /**
     * @test
     */
    public function can_use_offset_exists()
    {
        $container = new Container();
        $container['fact'] = function ($container) {
            return rand(0, 100);
        };

        $this->assertTrue(isset($container['fact']));
        $this->assertFalse(empty($container['fact']));
    }

    /**
     * @test
     */
    public function can_use_offset_unset()
    {
        $container = new Container();
        $container->share('hello', function () {
            return 'world';
        });
        $container->set('my', function ($container) {
            return 'you';
        });
        $container->set('my-constant', '123');

        unset($container['hello']);
        unset($container['my']);
        unset($container['my-constant']);

        $this->assertFalse($container->has('hello'));
        $this->assertFalse($container->has('my'));
        $this->assertFalse($container->has('my-constant'));
    }
}
