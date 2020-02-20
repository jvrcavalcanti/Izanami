<?php

use Accolon\DataLayer\Db;

require_once "../vendor/autoload.php";

$db = DB::table('post')->where(["id", "=", 80]);

dd($db->delete());