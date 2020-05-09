<?php

declare(strict_types=1);

namespace Gravatalonga\Container;

use ArrayAccess;
use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;
use Reflector;

use function array_key_exists;

/**
 * Class Container.
 */
class Container implements ArrayAccess, ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    protected static $instance;

    /**
     * @var array<string, mixed>
     */
    private $bindings;

    /**
     * @var array<string, mixed>
     */
    private $resolved = [];

    /**
     * @var array<string, mixed>
     */
    private $share;

    /**
     * Container constructor.
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->bindings = $config;
        $this->share = [];
    }

    /**
     * Factory binding.
     *
     * @param string $id
     * @param Closure $factory
     *
     * @return void
     */
    public function factory($id, Closure $factory)
    {
        $this->bindings[$id] = $factory;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ReflectionException
     */
    public function get($id)
    {
        return $this->resolve($id, []);
    }

    /**
     * @return ContainerInterface
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return array_key_exists($id, $this->bindings) || array_key_exists($id, $this->share);
    }

    /**
     * @param string $id
     * @param array<string, mixed> $arguments
     *
     * @throws NotFoundContainerException
     * @throws ContainerException|ReflectionException
     *
     * @return mixed|object
     */
    public function make($id, array $arguments = [])
    {
        if (array_key_exists($id, $this->share)) {
            throw ContainerException::shareOnMake($id);
        }

        return $this->resolve($id, $arguments);
    }

    /**
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param string $offset
     *
     * @throws ReflectionException
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->factory($offset, $value);
    }

    /**
     * @param string $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->bindings[$offset], $this->share[$offset]);
    }

    /**
     * Alias for Factory method.
     *
     * @param string $id
     * @param mixed $factory
     *
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
     * @param ContainerInterface $container
     */
    public static function setInstance(ContainerInterface $container): void
    {
        self::$instance = $container;
    }

    /**
     * Share rather resolve as factory.
     *
     * @param string $id
     * @param Closure $factory
     *
     * @return void
     */
    public function share($id, Closure $factory)
    {
        $this->share[$id] = $factory;
    }

    /**
     * @param ReflectionParameter[] $params
     * @param array<string, mixed> $arguments
     *
     * @return array<int, string>
     */
    private function buildDependencies(array $params, array $arguments = [])
    {
        return array_map(
            function (ReflectionParameter $param) use ($arguments) {
                if (true === array_key_exists($param->getName(), $arguments)) {
                    return $arguments[$param->getName()];
                }

                /** @var ReflectionNamedType|null $type */
                $type = $param->getType();

                // in case we can't find type hint, we guess by variable name.
                // e.g.: $cache it will attempt resolve 'cache' from container.
                if (null === $type) {
                    if ($this->has($param->getName())) {
                        return $this->get($param->getName());
                    }

                    throw ContainerException::findType($type);
                }

                if (true === $type->isBuiltin()) {
                    if ($this->has($param->getName())) {
                        return $this->get($param->getName());
                    }

                    if ($type->allowsNull()) {
                        return null;
                    }

                    throw ContainerException::findType($type);
                }

                if (ContainerInterface::class === $type->getName()) {
                    return $this;
                }

                return $this->get($type->getName());
            },
            $params
        );
    }

    /**
     * @param string $id
     * @param array<string, mixed> $arguments
     *
     * @throws NotFoundContainerException
     * @throws ReflectionException
     *
     * @return mixed|object
     */
    private function resolve(string $id, array $arguments = [])
    {
        if (isset($this->resolved[$id])) {
            return $this->resolved[$id];
        }

        if (!$this->has($id) && !class_exists($id)) {
            throw NotFoundContainerException::entryNotFound($id);
        }

        if (!$this->has($id) && class_exists($id)) {
            return $this->resolveClass($id, $arguments);
        }

        if ($this->has($id)) {
            return $this->resolveEntry($id, $arguments);
        }

        throw NotFoundContainerException::entryNotFound($id);
    }

    /**
     * @param Reflector $reflection
     * @param array<string, mixed> $arguments
     *
     * @return array<int, mixed>
     */
    private function resolveArguments(Reflector $reflection, array $arguments = [])
    {
        $params = [];

        if ($reflection instanceof ReflectionClass) {
            if (!$constructor = $reflection->getConstructor()) {
                $params = [];
            } else {
                $params = $constructor->getParameters();
            }
        } elseif ($reflection instanceof ReflectionFunction) {
            if (!$params = $reflection->getParameters()) {
                $params = [];
            }
        }

        return $this->buildDependencies($params, $arguments);
    }

    /**
     * @param class-string|object $id
     * @param array<string, mixed> $arguments
     *
     * @throws ReflectionException
     *
     * @return object
     */
    private function resolveClass($id, array $arguments = [])
    {
        $reflection = new ReflectionClass($id);

        return $reflection->newInstanceArgs($this->resolveArguments($reflection, $arguments));
    }

    /**
     * @param string $id
     * @param array<string, mixed> $arguments
     *
     * @throws ReflectionException
     *
     * @return mixed
     */
    private function resolveEntry(string $id, array $arguments = [])
    {
        $get = $this->bindings[$id] ?? $this->share[$id];

        if ($get instanceof Closure) {
            $reflection = new ReflectionFunction($get);
            $value = $reflection->invokeArgs($this->resolveArguments($reflection, $arguments));

            if (isset($this->share[$id])) {
                $this->resolved[$id] = $value;
            }

            return $value;
        }

        return $get;
    }
}
