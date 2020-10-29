<?php

declare(strict_types=1);

namespace Gravatalonga\Container;

use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

abstract class AutoWiringAware implements ContainerInterface
{
    /**
     * @throws ContainerException
     * @throws ReflectionException
     *
     * @return mixed
     */
    protected function argumentWithoutType(ReflectionParameter $reflector)
    {
        if (false === $reflector->isOptional()) {
            throw ContainerException::findType($reflector->getName());
        }

        return $reflector->getDefaultValue();
    }

    /**
     * @throws ContainerException
     * @throws ReflectionException
     *
     * @return mixed|null
     */
    protected function autoWiringArguments(ReflectionParameter $reflector)
    {
        if (true === $this->has($reflector->getName())) {
            return $this->get($reflector->getName());
        }

        /** @var ReflectionNamedType|null $type */
        $type = $reflector->getType();

        if (null !== $type && !$type->isBuiltin()) {
            return $this->get($type->getName());
        }

        return $this->argumentWithoutType($reflector);
    }
}
