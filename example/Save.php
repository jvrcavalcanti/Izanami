<?php

use Accolon\DataLayer\Db;
use App\Post;

require_once "../vendor/autoload.php";

$db = new Post();

$db->title = "test save";
$db->text = "Eu aqui testando novo save";
$db->tags = "#outros#";
$db->user_id = 1;

dd($db->save());