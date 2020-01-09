<?php

use App\Db;

require_once "../vendor/autoload.php";

dd(DB::table('post')->count());