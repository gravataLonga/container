<?php

namespace Tests;

use Gravatalonga\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase
{
    /**
     * @test
     */
    public function can_create_container()
    {
        $container = new Container();
        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    /**
     * @test
     */
    public function can_get_value_from_container()
    {
        $container = new Container(['config' => true]);
        $this->assertTrue($container->get('config'));
    }

    /**
     * @test
     */
    public function can_check_if_entry_exist_on_container()
    {
        $container = new Container(['db' => 'my-db']);
        $this->assertTrue($container->has('db'));
        $this->assertFalse($container->has('key-not-exists'));
    }

    /**
     * @test
     */
    public function must_throw_exception_when_try_get_entry_dont_exists()
    {
        $container = new Container();
        $this->expectException(NotFoundExceptionInterface::class);
        $container->get('entry');
    }

    /**
     * @test
     */
    public function can_bind_dependency()
    {
        $rand = rand(0, 10);
        $container = new Container();
        $container->factory('random', function () use ($rand) {
            return $rand;
        });

        $this->assertEquals($rand, $container->get('random'));
    }

    /**
     * @test
     */
    public function can_get_different_value_from_container()
    {
        $container = new Container();
        $container->factory('random', function () {
            return rand(0, 1000);
        });
        $this->assertNotEquals($container->get('random'), $container->get('random'));
    }
    
    /**
     * @test
     */
    public function can_get_container_from_factory()
    {
        $rand = rand(0, 1000);
        $container = new Container();
        $container->factory('random', function () use ($rand) {
            return $rand;
        });

        $container->factory('random1', function (ContainerInterface $container) {
            return $container->get('random');
        });

        $this->assertEquals($container->get('random'), $container->get('random1'));
    }

    /**
     * @test
     */
    public function i_have_set_method_alias_for_factory()
    {
        $container = new Container();
        $container->set('random', function () {
            return rand(0, 1000);
        });
        $this->assertNotEquals($container->get('random'), $container->get('random'));
    }

    /**
     * @test
     */
    public function can_share_same_binding_and_can_check_if_exists()
    {
        $container = new Container();
        $container->share('random', function () {
            return rand(0, 1000);
        });
        $this->assertTrue($container->has('random'));
    }

    /**
     * @test
     */
    public function can_get_instance_from_share_binding()
    {
        $container = new Container();
        $container->share('random', function () {
            return rand(1, 1000);
        });
        $this->assertGreaterThan(0, $container->get('random'));
    }

    /**
     * @test
     */
    public function when_resolve_from_share_binding_it_return_same_value()
    {
        $container = new Container();
        $container->share('random', function () {
            return rand(1, 1000);
        });
        $this->assertEquals($container->get('random'), $container->get('random'));
    }

    /**
     * @test
     */
    public function can_get_instance_of_container()
    {
        $container = new Container();
        $container::setInstance($container);

        $this->assertInstanceOf(ContainerInterface::class, Container::getInstance());
        $this->assertSame($container, Container::getInstance());
    }

    /**
     * @test
     */
    public function can_set_direct_value_rather_than_callback()
    {
        $container = new Container();

        $container->set('hello', 'world');
        $container->set('abc', 123);
        $container->set('object', new \stdClass());

        $this->assertSame('world', $container->get('hello'));
        $this->assertSame(123, $container->get('abc'));
        $this->assertInstanceOf(\stdClass::class, $container->get('object'));
    }
}
