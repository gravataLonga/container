<?php

namespace Tests;

use Gravatalonga\Container\Container;
use Gravatalonga\Container\Tag;
use PHPUnit\Framework\TestCase;

class InjectDependencyAttributeTest extends TestCase
{
    /** @test */
    public function get_class_by_tags()
    {
        $c = new Container();
        $c->set(MyReportCustom::class, function () {
            return new MyReportCustom();
        });
        $c->set(User::class, function () {
            return new User();
        });

        $classes = $c->tag("model");
        $this->assertCount(2, $classes);
    }
}

#[Tag("report", "model")]
class MyReportCustom
{
}

#[Tag("user", "model")]
class User
{
}
