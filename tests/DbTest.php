<?php

namespace Accolon\DataLayer\Test;

require_once "./vendor/autoload.php";

use Accolon\DataLayer\Db;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    public function testConnection(): void
    {
        $this->assertIsObject(Db::connection());
    }

    public function testCreateObject(): void
    {
        $this->assertIsObject(Db::table('post'));
    }
}