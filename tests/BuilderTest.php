<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function testSample()
    {
        $test = Test::builder()->username('foo')->build();

        $this->assertInstanceOf(Test::class, $test);
        $this->assertEquals('foo', $test->username);
    }
}
