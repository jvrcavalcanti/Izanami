<?php

use Accolon\DataLayer\Db;

require_once "../vendor/autoload.php";

$db = DB::table('post')->where(["id", "=", 61]);

dd($db->update(["tags"], [
    "#musicas#outros#"
]));