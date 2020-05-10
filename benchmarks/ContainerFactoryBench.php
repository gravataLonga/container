<?php

declare(strict_types=1);

use Gravatalonga\Container\Container;

/**
 * @Revs({1, 8, 64, 4096})
 * @BeforeMethods({"init"})
 */
class ContainerBench
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @Iterations(5)
     */
    public function benchFactory()
    {
        $this->container->set('my-logger', static function () {
            return mt_rand(0, 1000);
        });
        $this->container->get('my-logger');
    }

    public function init()
    {
        $this->container = new Container();
    }
}
