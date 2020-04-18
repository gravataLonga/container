<?php

namespace Gravatalonga;

use Closure;
use Psr\Container\ContainerInterface;
use ReflectionParameter;

/**
 * Class Container
 *
 * @package Gravatalonga\Container
 */
class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $bindings;

    /**
     * @var array
     */
    private $share;

    /**
     * @var array
     */
    private $resolved = [];

    /**
     * Container constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->bindings = $config;
        $this->share = [];
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            if (!class_exists($id)) {
                throw NotFoundContainerException::entryNotFound($id);
            }

            try {
                $reflection = new \ReflectionClass($id);
                return $reflection->newInstanceArgs($this->buildDependecies($reflection));
            } catch (\ReflectionException $e) {
                throw NotFoundContainerException::entryNotFound($id);
            }
        }

        if (isset($this->resolved[$id])) {
            return $this->resolved[$id];
        }

        $get = $this->bindings[$id] ?? $this->share[$id];

        if ($get instanceof Closure) {
            $value = $get($this);
            if (isset($this->share[$id])) {
                $this->resolved[$id] = $value;
            }
            return $value;
        }

        return $get;
    }

    /**
     * @inheritDoc
     */
    public function has($id)
    {
        return isset($this->bindings[$id]) || isset($this->share[$id]);
    }

    /**
     * Factory binding
     *
     * @param string $id
     * @param Closure $factory
     * @return void
     */
    public function factory($id, Closure $factory)
    {
        $this->bindings[$id] = $factory;
    }

    /**
     * Alias for Factory method
     *
     * @param string $id
     * @param Closure $factory
     * @return void
     */
    public function set($id, Closure $factory)
    {
        $this->factory($id, $factory);
    }

    /**
     * Share rather resolve as factory
     *
     * @param string $id
     * @param callable $factory
     * @return void
     */
    public function share($id, callable $factory)
    {
        $this->share[$id] = $factory;
    }

    /**
     * @param \ReflectionClass $reflection
     * @return array<int, mixed>
     */
    protected function buildDependecies(\ReflectionClass $reflection)
    {
        if (!$constructor = $reflection->getConstructor()) {
            return [];
        }

        $params = $constructor->getParameters();

        return array_map(function (ReflectionParameter $param) {
            if (!$type = $param->getType()) {
                throw ContainerException::findType($param->getClass());
            }
            return $this->get($type->getName());
        }, $params);
    }
}
