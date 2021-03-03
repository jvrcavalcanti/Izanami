<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Test\Models\User;

class BuilderTest extends TestCase
{
    public function testSample()
    {
        $test = User::builder()->username('foo')->build();

        $this->assertInstanceOf(User::class, $test);
        $this->assertEquals('foo', $test->username);
    }
}
