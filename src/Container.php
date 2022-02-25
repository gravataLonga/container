<?php

declare(strict_types=1);

namespace Gravatalonga\Container;

use ArrayAccess;
use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;
use Reflector;

use function array_key_exists;
use function is_callable;

/**
 * Class Container.
 */
class Container extends AutoWiringAware implements ArrayAccess, ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    protected static $instance;

    /**
     * @var array<string, string>
     */
    private array $aliases = [];

    /**
     * @var array<string, mixed>
     */
    private array $bindings;

    /**
     * @var array<string, boolean>
     */
    private array $entriesBeingResolved = [];

    /**
     * @var array <string, mixed>
     */
    private array $extended = [];

    /**
     * @var array<string, mixed>
     */
    private array $resolved = [];

    /**
     * @var array<string, mixed>
     */
    private array $share;

    /**
     * Container constructor.
     *
     * @param array<string, mixed> $config
     *
     * @throws NotFoundContainerException
     */
    public function __construct(array $config = [])
    {
        $this->bindings = $config;
        $this->share = [];

        $self = $this;
        $this->share(ContainerInterface::class, static function () use ($self) {
            return $self;
        });
        $this->alias(ContainerInterface::class, Container::class);
    }

    /**
     * @param string $entry
     * @param string $alias
     *
     * @throws NotFoundContainerException
     *
     * @return void
     */
    public function alias($entry, $alias)
    {
        if (true === $this->isAlias($entry)) {
            throw NotFoundContainerException::entryNotFound($entry);
        }

        if (false === $this->has($entry)) {
            throw NotFoundContainerException::entryNotFound($entry);
        }

        $this->aliases[$alias] = $entry;
    }

    /**
     * @param string $id
     * @param callable|mixed $factory
     *
     * @throws NotFoundContainerException
     *
     * @return void
     */
    public function extend($id, $factory)
    {
        if (!$this->has($id)) {
            throw NotFoundContainerException::entryNotFound($id);
        }

        $factory = $this->prepareEntry($factory);

        if (true === array_key_exists($id, $this->resolved)) {
            unset($this->resolved[$id]);
        }

        $this->extended[$id][] = $factory;
    }

    /**
     * Factory binding.
     *
     * @param callable|mixed $factory
     *
     * @return void
     */
    public function factory(string $id, $factory)
    {
        $this->bindings[$id] = $this->prepareEntry($factory);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ReflectionException
     */
    public function get(string $id)
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
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->bindings)
            || array_key_exists($id, $this->share)
            || array_key_exists($id, $this->aliases);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function isAlias($id)
    {
        return true === array_key_exists($id, $this->aliases);
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
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        $this->factory($offset, $value);
    }

    /**
     * @param string $offset
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset(
            $this->bindings[$offset],
            $this->share[$offset],
            $this->resolved[$offset],
            $this->aliases[$offset],
            $this->extended[$offset]
        );
    }

    /**
     * Alias for Factory method.
     *
     * @param mixed $factory
     *
     * @return void
     */
    public function set(string $id, $factory)
    {
        $this->factory($id, $factory);
    }

    public static function setInstance(ContainerInterface $container): void
    {
        self::$instance = $container;
    }

    /**
     * Share rather resolve as factory.
     *
     * @param string $id
     * @param mixed $factory
     *
     * @return void
     */
    public function share($id, $factory)
    {
        if (true === array_key_exists($id, $this->resolved)) {
            unset($this->resolved[$id]);
        }

        $this->share[$id] = $this->prepareEntry($factory);
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
                if (true === array_key_exists($param->getName(), $this->entriesBeingResolved)) {
                    throw ContainerException::circularDependency();
                }

                $this->entriesBeingResolved[$param->getName()] = true;

                if (true === array_key_exists($param->getName(), $arguments)) {
                    return $arguments[$param->getName()];
                }

                $type = $param->getType();

                // https://github.com/phpstan/phpstan/issues/1133
                // @phpstan-ignore-next-line
                if (null !== $type && true === array_key_exists($type->getName(), $arguments)) {
                    // @phpstan-ignore-next-line
                    return $arguments[$type->getName()];
                }

                return $this->autoWiringArguments($param);
            },
            $params
        );
    }

    /**
     * Get all extenders for particular entry id.
     *
     * @return array|mixed
     */
    private function getExtenders(string $id)
    {
        return $this->extended[$id] ?? [];
    }

    /**
     * @param mixed $factory
     *
     * @return callable|Closure
     */
    private function prepareEntry($factory)
    {
        return is_callable($factory) ?
            ($factory instanceof Closure ?
                $factory :
                Closure::fromCallable($factory)) :
            $factory;
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @throws NotFoundContainerException
     * @throws ReflectionException
     *
     * @return mixed|object
     */
    private function resolve(string $id, array $arguments = [])
    {
        if (true === array_key_exists($id, $this->resolved)) {
            return $this->resolved[$id];
        }

        if (true === array_key_exists($id, $this->aliases)) {
            return $this->resolve($this->aliases[$id], $arguments);
        }

        if ((false === $this->has($id)) && (true === class_exists($id))) {
            $get = $this->resolveClass($id, $arguments);

            foreach ($this->getExtenders($id) as $extend) {
                $get = $extend($this, $get);
            }

            return $get;
        }

        if (true === $this->has($id)) {
            $get = $this->resolveEntry($id, $arguments);

            foreach ($this->getExtenders($id) as $extend) {
                if (is_callable($extend)) {
                    $get = $extend($this, $get);
                    continue;
                }
                $get = $extend;
            }

            return $get;
        }

        throw NotFoundContainerException::entryNotFound($id);
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @return array<int, mixed>
     */
    private function resolveArguments(Reflector $reflection, array $arguments = [])
    {
        $params = [];

        if ($reflection instanceof ReflectionClass) {
            if (null !== $constructor = $reflection->getConstructor()) {
                $params = $constructor->getParameters();
            }
        }

        if ($reflection instanceof ReflectionFunction) {
            $params = $reflection->getParameters();
        }

        $value = $this->buildDependencies($params, $arguments);
        $this->entriesBeingResolved = [];

        return $value;
    }

    /**
     * @param mixed $id
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

            if (true === array_key_exists($id, $this->share)) {
                $this->resolved[$id] = $value;
            }

            return $value;
        }

        return $get;
    }
}