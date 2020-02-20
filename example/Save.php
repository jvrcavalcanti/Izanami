<?php

use Accolon\DataLayer\Db;

require_once "../vendor/autoload.php";

$db = DB::table('post');

dd($db->save(["titulo", "texto_post", "tags", "idAutor", "dt_post", "gostei", "n_gostei"],[
    "Titulo muito louco",
    "Texto muito louco",
    "#outro#",
    1,
    "2020-01-18",
    0,
    0
]));