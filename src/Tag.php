<?php

namespace Gravatalonga\Container;

use Attribute;

#[Attribute]
class Tag
{
    /**
     * @var string[]
     */
    private array $tags;

    public function __construct(string ...$tags)
    {
        $this->tags = $tags;
    }
}
