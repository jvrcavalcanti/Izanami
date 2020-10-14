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

    protected $sentives = [
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
$user = (new User)->find(1);
// $user->name == "Accolon"
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
$user = $table->where("name", "Accolon")->first();
```

### Get All

```php
$table = new User();

// Return array
$user = $table->where("id", ">", 1)->all();
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

// Where In
$table->whereIn('id', [1, 2, 3]);
```

### Find

```php
$table = new User();

$user = $table->find(1);
```

### Find Or Fail

```php
$table = new User();

try {
    $user = $table->findOrFail(1);
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

$users = $table->where("id", ">", 2)->order("id", "DESC")->getAll();

$user = $table->where("id", ">", 2)->desc()->all();

$user = $table->where("id", ">", 2)->asc()->all();
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

### Relationships

```php
use Accolon\Izanami\Model;

class User extends Model
{
    // One to One
    public function phone()
    {
        return $this->hasOne(Phone::class);
    }

    // One to Many
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    // One to Many (Inverse)
    public function user()
    {
        return $this->belongsToOne(User::class);
    }
}

class Phone extends Model
{
    // One to One (Inverse)
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}

```

### Raw

```php
$table = new User();

// Return boolean
$result = DB::raw("SELECT * FROM test WHERE id = 1");

// Return array
$result = DB::selectRaw("SELECT * FROM test WHERE id = 1");
```
