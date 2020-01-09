<?php

use App\Db;
use App\Table;

require_once "../vendor/autoload.php";

$db = DB::table('post')->select();

dd($db->find(60));