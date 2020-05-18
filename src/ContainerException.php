<?php

declare(strict_types=1);

namespace Gravatalonga\Container;

use Exception;
use Psr\Container\ContainerExceptionInterface;

final class ContainerException extends Exception implements ContainerExceptionInterface
{
    /**
     * @return ContainerException
     */
    public static function circularDependency(): ContainerException
    {
        return new self('Detect Circular Dependency');
    }

    /**
     * @param string|null $type
     *
     * @return ContainerException
     */
    public static function findType(?string $type): ContainerException
    {
        return new self(sprintf('Unable to find type hint (%s)', ($type ?: '')));
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
