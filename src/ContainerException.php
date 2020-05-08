<?php

declare(strict_types=1);

namespace Gravatalonga\Container;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;

final class ContainerException extends Exception implements ContainerExceptionInterface
{
    /**
     * @param ReflectionClass|null $class
     *
     * @return ContainerException
     */
    public static function findType(?ReflectionClass $class): ContainerException
    {
        return new self('Unable to find type hint of ' . ($class ? $class->getName() : ''));
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
