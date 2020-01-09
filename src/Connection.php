<?php

namespace Accolon\DataLayer;

use PDO;
use PDOException;

class Connection
{
    protected static $driver = DB['DRIVER'] . ":";
    protected static $host = "host=" . DB['HOST'] . ";";
    protected static $dbname = "dbname=" . DB['NAME'] . ";";
    protected static $port = "port=" . DB['PORT'] . ";";
    protected static $charset = "charset=" . DB['CHARSET'];
    protected static $user = DB['USER'];
    protected static $password = DB['PASSWORD'];

    public static function conn()
    {
        try{
            $conn = new PDO(self::$driver . self::$host . self::$dbname . self::$port . self::$charset, self::$user, self::$password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;
        }catch(PDOException $e){
            die("ERROR: {$e->getMessage()}");
        }
    }
}