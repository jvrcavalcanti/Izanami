<?php

require_once "./vendor/autoload.php";
require_once "./config.php";

use Accolon\Izanami\Migration\Blueprint;
use Accolon\Izanami\Migration\Migration;
use Accolon\Izanami\Migration\Schema;
use PHPUnit\Framework\TestCase;

class CreateTestTable implements Migration
{
    public function up(): bool
    {
        return Schema::create("tests", function (Blueprint $table) {
            $table->id();
            $table->string("title")->unique();
            $table->text("text")->nullable();
            $table->integer("like")->default(0);
            $table->boolean("admin")->default(false);
            $table->enum("states", ['ACTIVE', 'DESACTIVE']);
            $table->json("tags");
            $table->timestamps();
        });
    }

    public function downIfExists(): bool
    {
        return Schema::dropIfExists('tests');
    }

    public function down(): bool
    {
        return Schema::drop('tests');
    }
}

class MigrationTest extends TestCase
{
    private CreateTestTable $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = new CreateTestTable();
    }

    public function testDropIfExists():void
    {
        $this->assertTrue($this->table->downIfExists());
    }

    public function testUp(): void
    {
        $this->assertTrue($this->table->up());
    }

    public function testDown(): void
    {
        $this->assertTrue($this->table->down());
    }
}
