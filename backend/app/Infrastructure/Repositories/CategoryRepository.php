<?php

namespace App\Infrastructure\Repositories;

use App\Domains\Product\Category;

class CategoryRepository
{
    public function __construct(protected Category $model) {}

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find(int|string $id)
    {
        return $this->model->find($id);
    }

    public function update(int|string $id, array $data)
    {
        $record = $this->find($id);
        if ($record) {
            $record->update($data);
        }
        return $record;
    }

    public function delete(int|string $id)
    {
        $record = $this->find($id);
        return $record ? $record->delete() : false;
    }
}
