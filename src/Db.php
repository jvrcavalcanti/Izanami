<?php

namespace Accolon\DataLayer;

use Accolon\DataLayer\Model;

use PDO;
use PDOException;

class Db
{
    private static $instance;

    public static function connection(): PDO
    {
        try{
            if(!self::$instance) {
                $config = require "../config.php";
                $config = $config["db"];

                self::$instance = new PDO("{$config->driver}:host={$config->host};port={$config->port};charset={$config->charset};dbname={$config->name}",
                    $config->user,
                    $config->password);
                    
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

                return self::connection();
            }
            return self::$instance;

        }catch(PDOException $e){
            die("ERROR: {$e->getMessage()}");
        }
    }

    public static function table(string $table): object
    {
        return new class($table) extends Model {
            protected $table;

            public function __construct($table)
            {
                $this->table = $table;
            }
        };
    }
}