<?php

namespace Accolon\Izanami\Migration;

interface Migration
{
    public function up();
    public function down();
}
