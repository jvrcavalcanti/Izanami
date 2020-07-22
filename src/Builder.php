<?php

namespace Accolon\DataLayer;

class Builder
{
    private Model $model;

    public function __construct(string $model)
    {
        $this->model = new $model;
    }

    public function __call($name, $value): Builder
    {
        $this->model->$name = $value[0];
        return $this;
    }

    public function build()
    {
        return $this->model;
    }
}
