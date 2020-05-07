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
     * @var ContainerInterface
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
     * @return ContainerInterface
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
     * @param Closure $factory
     * @return void
     */
    public function share($id, Closure $factory)
    {
        $this->share[$id] = $factory;
    }

    /**
     * @inheritDoc
     * @throws \ReflectionException
     */
    public function get($id)
    {
        return $this->resolve($id, []);
    }

    /**
     * @param $id
     * @param array $arguments
     * @return mixed|object
     * @throws NotFoundContainerException
     * @throws \ReflectionException
     * @throws ContainerException
     */
    public function make($id, array $arguments = [])
    {
        if (isset($this->share[$id])) {
            throw ContainerException::shareOnMake($id);
        }

        return $this->resolve($id, $arguments);
    }

    /**
     * @param string $id
     * @param array $arguments
     * @return mixed|object
     * @throws NotFoundContainerException
     * @throws \ReflectionException
     */
    protected function resolve(string $id, array $arguments = [])
    {
        if (isset($this->resolved[$id])) {
            return $this->resolved[$id];
        }

        if (!$this->has($id) && !class_exists($id)) {
            throw NotFoundContainerException::entryNotFound($id);
        } else if (!$this->has($id) && class_exists($id)) {
            return $this->resolveClass($id, $arguments);
        } else if ($this->has($id)) {
            return $this->resolveEntry($id, $arguments);
        }
        throw NotFoundContainerException::entryNotFound($id);
    }

    /**
     * @param class-string|object $id
     * @param array $arguments
     * @return object
     * @throws \ReflectionException
     */
    protected function resolveClass($id, array $arguments = [])
    {
        $reflection = new \ReflectionClass($id);
        return $reflection->newInstanceArgs($this->resolveArguments($reflection, $arguments));
    }

    /**
     * @param string $id
     * @param array $arguments
     * @return mixed
     * @throws \ReflectionException
     */
    protected function resolveEntry(string $id, array $arguments = [])
    {
        $get = $this->bindings[$id] ?? $this->share[$id];

        if ($get instanceof Closure) {
            $reflection = new \ReflectionFunction($get);
            $value = $reflection->invokeArgs($this->resolveArguments($reflection, $arguments));
            if (isset($this->share[$id])) {
                $this->resolved[$id] = $value;
            }
            return $value;
        }

        return $get;
    }

    /**
     * @param \Reflector $reflection
     * @param array $arguments
     * @return array
     */
    protected function resolveArguments(\Reflector $reflection, array $arguments = [])
    {
        $params = [];
        if ($reflection instanceof \ReflectionClass) {
            if (!$constructor = $reflection->getConstructor()) {
                $params = [];
            } else {
                $params = $constructor->getParameters();
            }
        } else if ($reflection instanceof \ReflectionFunction) {
            if (!$params = $reflection->getParameters()) {
                $params = [];
            }
        }

        return $this->buildDependencies($params, $arguments);
    }

    /**
     * @param ReflectionParameter[] $params
     * @param array $arguments
     * @return array<string, string>
     */
    protected function buildDependencies(array $params, array $arguments = [])
    {
        return array_map(function (ReflectionParameter $param) use ($arguments) {
            if (isset($arguments[$param->getName()])) {
                return $arguments[$param->getName()];
            }

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
     * @throws \ReflectionException
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
