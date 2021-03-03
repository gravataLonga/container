<?php

declare(strict_types=1);

namespace Gravatalonga\Container;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Tag
{
    /**
     * @var string[]
     */
    private array $tags = [];

    public function __construct(string ...$tags)
    {
        $this->tags = $tags;
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}
