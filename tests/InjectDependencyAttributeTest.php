<?php

declare(strict_types=1);

namespace Tests;

use Gravatalonga\Container\Container;
use Gravatalonga\Container\Tag;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class InjectDependencyAttributeTest extends TestCase
{
    public function testGetClassByTags()
    {
        $c = new Container();
        $c->set(MyReportCustom::class, static function () {
            return new MyReportCustom();
        });
        $c->set(User::class, static function () {
            return new User();
        });

        $classes = $c->tag('model');
        self::assertCount(2, $classes);
    }
}

#[Tag('report', 'model')]
class MyReportCustom
{
}

#[Tag('user', 'model')]
class User
{
}
