<?php

namespace Test;

use Accolon\DataLayer\Model;

class User extends Model
{
    protected string $table = "users";

    public string $id;
    public string $username;

    protected string $password;

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }
}