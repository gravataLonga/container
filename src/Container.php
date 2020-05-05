<?php

namespace Gravatalonga\Container;

use Closure;
use Psr\Container\ContainerInterface;
use ReflectionParameter;

/**
 * Class Container
 *
 * @package Gravatalonga\Container
 */
class Container implements ContainerInterface, \ArrayAccess
{
    /**
     * @var static
     */
    protected static $instance;

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
     * @return Container|static
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * @param ContainerInterface $container
     */
    public static function setInstance(ContainerInterface $container)
    {
        self::$instance = $container;
    }

    /**
     * @inheritDoc
     * @throws \ReflectionException
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            if (!class_exists($id)) {
                throw NotFoundContainerException::entryNotFound($id);
            }

            try {
                $reflection = new \ReflectionClass($id);
                return $reflection->newInstanceArgs($this->buildDependencies($reflection));
            } catch (\ReflectionException $e) {
                throw NotFoundContainerException::entryNotFound($id);
            }
        }

        if (isset($this->resolved[$id])) {
            return $this->resolved[$id];
        }

        $get = $this->bindings[$id] ?? $this->share[$id];

        if ($get instanceof Closure) {
            $reflection = new \ReflectionFunction($get);
            $value = $reflection->invokeArgs($this->buildDependencies($reflection));
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
     * @param mixed $factory
     * @return void
     */
    public function set($id, $factory)
    {
        if ($factory instanceof Closure) {
            $this->factory($id, $factory);
            return;
        }
        $this->bindings[$id] = $factory;
    }

    /**
     * Share rather resolve as factory
     *
     * @param string $id
     * @param callable $factory
     * @return void
     */
    public function share($id, Closure $factory)
    {
        $this->share[$id] = $factory;
    }

    /**
     * @param \ReflectionClass $reflection
     * @return array<int, mixed>
     */
    protected function buildDependencies(\Reflector $reflection)
    {
        if ($reflection instanceof \ReflectionClass) {
            if (!$constructor = $reflection->getConstructor()) {
                return [];
            }
            $params = $constructor->getParameters();
        } else if ($reflection instanceof \ReflectionFunction) {
            if (!$params = $reflection->getParameters()) {
                return [];
            }
        }

        return array_map(function (ReflectionParameter $param) {
            if (!$type = $param->getType()) {
                if ($this->has($param->getName())) {
                    return $this->get($param->getName());
                }
                throw ContainerException::findType($param->getClass());
            }

            if ($type->isBuiltin()) {
                if ($this->has($param->getName())) {
                    return $this->get($param->getName());
                }
                if ($type->allowsNull()) {
                    return null;
                }
            }

            if ($type->getName() === ContainerInterface::class) {
                return $this;
            }

             return $this->get($type->getName());
        }, $params);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->factory($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->bindings[$offset]);
        unset($this->share[$offset]);
    }
}
