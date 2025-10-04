<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Scope a query to apply filters based on the request.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $filters)
    {
        foreach ($filters as $field => $ops) {
            // Handle OR group
            if ($field === 'or') {
                $query->where(function($q) use ($ops) {
                    foreach ($ops as $index => $condition) {
                        $q->where(function($subQuery) use ($condition) {
                            foreach ($condition as $f => $condOps) {
                                foreach ($condOps as $op => $value) {
                                    $this->applyFilter($subQuery, $f, $op, $value, 'or');
                                }
                            }
                        });
                    }
                });
                continue;
            }

            // Handle search parameter
            if ($field === 'search') {
                $this->applySearchFilter($query, $ops);
                continue;
            }

            // Handle sorting
            if ($field === 'sort') {
                $this->applySorting($query, $ops);
                continue;
            }

            // Handle normal filters
            foreach ($ops as $op => $value) {
                // Check if field is a relation (contains dot notation)
                if (strpos($field, '.') !== false) {
                    $this->applyRelationFilter($query, $field, $op, $value);
                } else {
                    $this->applyFilter($query, $field, $op, $value);
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

        return $query->whereHas($relation, function ($q) use ($field, $op, $value) {
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