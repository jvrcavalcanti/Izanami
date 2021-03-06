<?php

namespace Accolon\Izanami;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;
use Accolon\Izanami\Interfaces\Arrayable;
use Accolon\Izanami\Interfaces\Jsonable;

class Collection implements Iterator, Countable, JsonSerializable, ArrayAccess, Jsonable, Arrayable
{
    /**
     * @property \Accolon\Izanami\Model[] $models
     */
    private $models = [];
    private $position = 0;

    public function __construct(array $models = [])
    {
        foreach ($models as $model) {
            $this->add($model);
        }
    }

    public function forEach(callable $callback)
    {
        foreach ($this->models as $key => $model) {
            $this->models[$key] = $callback($model, $key);
        }

        return $this;
    }

    public function map(callable $callback)
    {
        $new = new Collection();
        foreach ($this->models as $key => $model) {
            $new->add($callback($model, $key));
        }
        return $new;
    }

    public function filter(callable $callback)
    {
        $new = new Collection();
        foreach ($this->models as $key => $model) {
            if ($callback($model, $key) === true) {
                $new->add($model);
            }
        }
        return $new;
    }

    public function find(callable $callback): ?Model
    {
        foreach ($this->models as $key => $model) {
            if ($callback($model, $key) === true) {
                return $model;
            }
        }

        return null;
    }

    public function exists(int $key)
    {
        return $this->offsetExists($key);
    }

    public function add(?Model $model)
    {
        if ($model !== null) {
            $this->models[] = $model;
        }
    }

    public function del(int $key)
    {
        $this->offsetUnset($key);
    }

    public function get(int $key)
    {
        return $this->offsetGet($key);
    }

    public function set(int $key, Model $model)
    {
        return $this->offsetSet($key, $model);
    }

    public function offsetExists($key)
    {
        return isset($this->models[$key]);
    }

    public function offsetGet($offset)
    {
        return $this->models[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Model) {
            throw new \InvalidArgumentException("Must be an int");
        }

        if (empty($offset)) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->models[$offset]);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current(): Model
    {
        return $this->models[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next()
    {
        return ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->models[$this->position]);
    }

    public function count(): int
    {
        return sizeof($this->models);
    }

    public function jsonSerialize()
    {
        return $this->models;
    }

    public function toJson(): string
    {
        return json_encode($this);
    }

    public function toArray(): array
    {
        return $this->models;
    }
}
