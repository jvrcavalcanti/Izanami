# DataLayer Accolon


## Config
```php
# config.php

define("DB_CONFIG", [
    "driver" => "mysql",
    "host" => "localhost",
    "port" => 3306,
    "name" => "accolon",
    "charset" => "utf8",
    "user" => "accolon",
    "password" => "password"
]);
```

## Model

```php
use Accolon\DataLayer\Model;

class User extends Model
{
    protected string $table = "users";

    protected $safes = [
        "password"
    ];
}
```

## Insert

```php
// First way
$user = new User();

$user->name = "Accolon";
$user->email = "test@gmail.com";

$user->save();

// Second way
$user = new User([
    "name" => "Accolon",
    "email" => "test@gmail.com"
]);

$user->save();


// Third way
$user = new User();

$user->create([
    "name" => "Accolon",
    "email" => "test@gmail.com"
]);
```

## Update

```php
$user = new User();

$user->where("name", "Accolon")->update([
    "email" => "test2@gmail.com"
]);

// Or

$user->email = "email" => "test@gmail.com";
$user->save();
```

## Delete

```php
$user = new User();

$user->where("name", "Accolon")->delete();

// Or

$user->name = "Accolon";
$user->delete();
```

## Query

### Get

```php
$table = new User();

// Return one element
$user = $table->where("name", "Accolon")->get();
```

### Get All

```php
$table = new User();

// Return array
$user = $table->where("id", ">", 1)->getAll();
```

### Where

```php
$table = new User();

$table->where("name", "Accolon");

// Equal

$table->where("name", "=", "Accolon");

// Other compares

$table->where("id", ">", 1);

$table->where("id", "<", 1);

// Multiple wheres

$table->where("name", "=", "Accolon")->where("id", 2);

// whereOr

$table->whereOr("id", 1)->whereOr("name", "Accolon");
```

### Find

```php
$table = new User();

$user = $table->find("id", 1);
```

### Find By Id

```php
$table = new User();

$user = $table->findById(1);
```

### Find Or Fail

```php
$table = new User();

try {
    $user = $table->findOrFail("id", 1);
} catch (\Exception $e) {
    die("Not found");
}
```

### First

```php
$table = new User();

$user = $table->where("id", ">", 2)->first();
```

### All

```php
$table = new User();

$user = $table->all();
```

### Order By

```php
$table = new User();

$user = $table->where("id", ">", 2)->order("id", "DESC")->getAll();
```

### Limit

```php
$table = new User();

$user = $table->where("id", ">", 2)->limit(5)->getAll();
```

### Count

```php
$table = new User();

$user = $table->where("id", ">", 2)->count();
```

### Raw

```php
$table = new User();

// Return boolean
$result = DB::raw("SELECT * FROM test WHERE id = 1");

// Return array
$result = DB::selectRaw("SELECT * FROM test WHERE id = 1");
```
