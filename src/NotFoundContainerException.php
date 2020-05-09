<?php

declare(strict_types=1);

namespace Gravatalonga\Container;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

final class NotFoundContainerException extends Exception implements NotFoundExceptionInterface
{
    /**
     * @param string $entry
     *
     * @return NotFoundContainerException
     */
    public static function entryNotFound(string $entry): NotFoundContainerException
    {
        return new self('Entry ' . $entry . ' not found');
    }
}
