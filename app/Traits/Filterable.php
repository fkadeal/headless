<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Supported operators for filtering.
     */
    protected $operators = ['eq', 'ne', 'gt', 'gte', 'lt', 'lte', 'contains', 'in', 'nin'];

    /**
     * Scope a query to apply filters based on the request.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $filters)
    {
        foreach ($filters as $field => $data) {
            // Handle OR group
            if ($field === 'or') {
                $query->where(function ($q) use ($data) {
                    foreach ($data as $index => $condition) {
                        $q->where(function ($subQuery) use ($condition) {
                            foreach ($condition as $f => $condData) {
                                $this->scopeFilter($subQuery, [$f => $condData], 'or');
                            }
                        });
                    }
                });
                continue;
            }

            // Handle search parameter
            if ($field === 'search') {
                $this->applySearchFilter($query, $data);
                continue;
            }

            // Handle sorting
            if ($field === 'sort') {
                $this->applySorting($query, $data);
                continue;
            }

            // If it's not an array, default to eq operator
            if (!is_array($data)) {
                $data = ['eq' => $data];
            }

            // Handle normal filters
            foreach ($data as $key => $value) {
                if (in_array($key, $this->operators)) {
                    // It's an operator, apply the filter
                    $op = $key;
                    // Check if field is a relation (contains dot notation)
                    if (strpos($field, '.') !== false) {
                        $this->applyRelationFilter($query, $field, $op, $value);
                    } elseif (method_exists($this, $field) && !in_array($field, $this->getFillable())) {
                        // If field is a method but not fillable, treat as relation
                        // Filter by relation's primary key (usually 'id')
                        $this->applyRelationFilter($query, $field . '.id', $op, $value);
                    } else {
                        $this->applyFilter($query, $field, $op, $value);
                    }
                } else {
                    // It's a nested field, recurse using dot notation
                    $this->scopeFilter($query, ["$field.$key" => $value]);
                }
            }
        }

        return $query;
    }

    /**
     * Apply a filter to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $field
     * @param string $op
     * @param mixed $value
     * @param string $boolean
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFilter($query, $field, $op, $value, $boolean = 'and')
    {
        // Qualify field name with table name to avoid ambiguity in subqueries (e.g. Postgres whereHas)
        if (strpos($field, '.') === false) {
            $model = $query->getModel();
            $table = $model->getTable();

            // Check if column exists
            if (!\Illuminate\Support\Facades\Schema::hasColumn($table, $field)) {
                abort(400, "Filter error: Column '{$field}' not found on model '" . get_class($model) . "'.");
            }

            $field = "{$table}.{$field}";
        }

        $method = match ($op) {
            'eq' => 'where',
            'ne' => 'whereNot',
            'gt' => 'where',
            'gte' => 'where',
            'lt' => 'where',
            'lte' => 'where',
            'contains' => 'where',
            'in' => 'whereIn',
            'nin' => 'whereNotIn',
            default => 'where',
        };

        // Special handling for different operators
        return match ($op) {
            'eq' => $query->$method($field, '=', $value, $boolean),
            'ne' => $query->$method($field, '!=', $value, $boolean),
            'gt' => $query->$method($field, '>', $value, $boolean),
            'gte' => $query->$method($field, '>=', $value, $boolean),
            'lt' => $query->$method($field, '<', $value, $boolean),
            'lte' => $query->$method($field, '<=', $value, $boolean),
            'contains' => $query->$method($field, 'LIKE', "%$value%", $boolean),
            'in' => $query->$method($field, is_array($value) ? $value : explode(',', $value), $boolean),
            'nin' => $query->$method($field, is_array($value) ? $value : explode(',', $value), $boolean),
            default => $query->$method($field, '=', $value, $boolean),
        };
    }

    /**
     * Apply a filter based on relationship fields.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $field
     * @param string $op
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyRelationFilter($query, $field, $op, $value)
    {
        // Split field by dot to separate relation and field
        $parts = explode('.', $field, 2);
        $relation = $parts[0];
        $field = $parts[1];

        $model = $query->getModel();
        if (!method_exists($model, $relation)) {
            abort(400, "Filter error: Relationship '{$relation}' not found on model '" . get_class($model) . "'.");
        }

        return $query->whereHas($relation, function ($q) use ($field, $op, $value) {
            // If field still has dots, it's a nested relation
            if (strpos($field, '.') !== false) {
                return $this->applyRelationFilter($q, $field, $op, $value);
            }
            // Otherwise, it's a column on the related model
            $this->applyFilter($q, $field, $op, $value);
        });
    }

    /**
     * Apply search filter to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySearchFilter($query, $search)
    {
        $query->where(function ($q) use ($search) {
            // Get the model's fillable fields to search in
            $searchableFields = $this->getSearchableFields();

            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'LIKE', "%$search%");
            }
        });

        return $query;
    }

    /**
     * Apply sorting to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sort
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySorting($query, $sort)
    {
        foreach (explode(',', $sort) as $sortItem) {
            $direction = str_starts_with($sortItem, '-') ? 'desc' : 'asc';
            $field = ltrim($sortItem, '-');
            $query->orderBy($field, $direction);
        }

        return $query;
    }

    /**
     * Get the fields that should be searchable.
     *
     * @return array
     */
    protected function getSearchableFields()
    {
        // Allow models to define their own searchable fields
        if (property_exists($this, 'searchable')) {
            return $this->searchable;
        }

        // Default to fillable fields if no searchable property defined
        return $this->fillable ?? [];
    }
}
