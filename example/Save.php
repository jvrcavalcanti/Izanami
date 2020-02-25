<?php

use Accolon\DataLayer\Db;

require_once "../vendor/autoload.php";

$db = DB::table('posts');

$date = new DateTime("now");
$now = $date->format("Y-m-d H:i:s");

dd($db->save(["title", "text", "tags", "like", "dislike", "user_id", "created_at", "updated_at"],
[
    "asfasf",
    "asas",
    "afsasf",
    0,
    0,
    1,
    $now,
    $now
]));