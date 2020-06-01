<?php

namespace Accolon\DataLayer;

use Accolon\DataLayer\Exceptions\TransactionException;
use Accolon\DataLayer\Model;
use Closure;
use PDO;
use PDOException;

class Db
{
    protected static $instance;

    public static function connection(): PDO
    {
        try{
            if(!self::$instance) {
                $config = DB_CONFIG ?? null;

                if(!$config) return null;

                self::$instance = new PDO("{$config['driver']}:host={$config['host']};port={$config['port']};charset={$config['charset']};dbname={$config['name']}",
                    $config['user'],
                    $config['password']);
                    
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

                return self::connection();
            }

            return self::$instance;

        }catch(PDOException $e){
            die("ERROR: {$e->getMessage()}");
        }
    }

    public static function table(string $tableName): Model
    {
        return new class($tableName) extends Model {
            protected string $table;

            public function __construct($tableName = null)
            {
                if (is_string($tableName)) {
                    $this->table = $tableName;
                }   
            }
        };
    }

    public static function bruteSelect($sql, $params = [])
    {
        try {
            $stmt = self::connection()->prepare($sql);
            $result = $stmt->execute($params);

            return $stmt->fetchAll();

        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }

    public static function bruteSQL($sql, $params = []): bool
    {
        try {
            $stmt = self::connection()->prepare($sql);
            $result = $stmt->execute($params);

            return $result;

        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }

    public static function transaction(Closure $callback)
    {
        try {
            Db::beginTransaction();
            $callback();
            Db::commit();
        } catch (PDOException $e) {
            Db::rollBack();
            throw new TransactionException("Transaction roll back");
        }
    }

    public static function beginTransaction()
    {
        Db::connection()->beginTransaction();
    }

    public static function commit()
    {
        Db::connection()->commit();
    }

    public static function rollBack()
    {
        Db::connection()->rollBack();
    }
}
