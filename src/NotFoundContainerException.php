<?php

namespace Gravatalonga\Container;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundContainerException extends Exception implements NotFoundExceptionInterface
{
    /**
     * @param string $entry
     * @return static
     */
    public static function entryNotFound(string $entry)
    {
        return new static("Entry " . $entry . " not found");
    }
}
