<?php

namespace Gravatalonga\Container;

use Psr\Container\ContainerExceptionInterface;

class ContainerException extends \Exception implements ContainerExceptionInterface
{
    /**
     * @param \ReflectionClass|null $class
     * @return ContainerException
     */
    public static function findType(?\ReflectionClass $class)
    {
        return new static("unable to find type hint of " . ($class ? $class : ''));
    }

    /**
     * @param string $id
     * @return static
     */
    public static function shareOnMake(string $id)
    {
        return new static(sprintf("Entry is share and cannot be called on make: %s", $id));
    }

}
