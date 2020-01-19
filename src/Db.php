<?php

namespace Accolon\DataLayer;

use Accolon\DataLayer\Model;

use PDO;
use PDOException;

class Db
{
    protected static $driver = DB['DRIVER'] . ":";
    protected static $host = "host=" . DB['HOST'] . ";";
    protected static $dbname = "dbname=" . DB['NAME'] . ";";
    protected static $port = "port=" . DB['PORT'] . ";";
    protected static $charset = "charset=" . DB['CHARSET'];
    protected static $user = DB['USER'];
    protected static $password = DB['PASSWORD'];
    private static $instance;

    public static function connection(): PDO
    {
        try{
            if(!self::$instance) {
                self::$instance = new PDO(self::$driver . self::$host . self::$dbname . self::$port . self::$charset, self::$user, self::$password);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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