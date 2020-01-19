<?php

use Accolon\DataLayer\Db;

require_once "../vendor/autoload.php";

$db = Db::table('post');

dd($db->where(["id", "=", 60])->get());