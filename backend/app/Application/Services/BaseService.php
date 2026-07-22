<?php

namespace App\Application\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseService
{
    /**
     * The model class path associated with the service.
     */
    protected string $modelClass;

    /**
     * Get all records, optionally filtered.
     */
    public function get(array $filters = []): Collection
    {
        $query = $this->modelClass::query();

        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                // simple search or filter
                if (in_array($key, (new $this->modelClass)->getFillable())) {
                    $query->where($key, $value);
                }
            }
        }

        return $query->get();
    }

    /**
     * Find a record by its ID.
     */
    public function find(int|string $id): Model
    {
        return $this->modelClass::findOrFail($id);
    }

    /**
     * Create a new record.
     */
    public function post(array $data): Model
    {
        return $this->modelClass::create($data);
    }

    /**
     * Update an existing record.
     */
    public function update(int|string $id, array $data): Model
    {
        $model = $this->find($id);
        $model->update($data);
        return $model;
    }

    /**
     * Delete a record by ID.
     */
    public function deleted(int|string $id): bool
    {
        $model = $this->find($id);
        return (bool) $model->delete();
    }
}
