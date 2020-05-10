<?php

declare(strict_types=1);

namespace Gravatalonga\Container;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use ReflectionNamedType;

final class ContainerException extends Exception implements ContainerExceptionInterface
{
    /**
     * @param ReflectionNamedType|null $type
     *
     * @return ContainerException
     */
    public static function findType(?ReflectionNamedType $type): ContainerException
    {
        return new self(sprintf('Unable to find type hint (%s)', ($type ? $type->getName() : '')));
    }

    /**
     * @param string $id
     *
     * @return ContainerException
     */
    public static function shareOnMake(string $id): ContainerException
    {
        return new self(sprintf('Entry is shared and cannot be called on make: %s', $id));
    }
}
