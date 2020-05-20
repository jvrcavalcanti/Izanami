<?php

namespace Test;

use Accolon\DataLayer\Model;
use ReflectionClass;

class User extends Model
{
    protected string $table = "users";

    public string $id;
    public string $username;

    protected string $password;

    public function __construct()
    {
        //
    }

    public static function build(): User
    {
        return (new ReflectionClass(self::class))->newInstance();
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }
}