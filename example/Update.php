<?php

use Accolon\DataLayer\Db;

require_once "../vendor/autoload.php";

$db = DB::table('posts')->where(["id", "=", 1]);

dd($db->update(["tags"], [
    "#musicas#outros#"
]));