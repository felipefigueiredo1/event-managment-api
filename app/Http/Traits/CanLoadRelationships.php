<?php

namespace App\Http\Traits;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

trait CanLoadRelationships
{
    protected function loadRelationships(
        Model|QueryBuilder|EloquentBuilder $for,
        ?array $relationships = null
    ): Model|QueryBuilder|EloquentBuilder
    {
        $relationships = $relationships ?? $this->relationships ?? [];
        foreach($relationships as $relation) {
            $for->when($this->shouldIncluseRelation($relation),
            fn($q) => $for instanceof Model ? $for->load($relation) : $q->with($relation));
        }

        return $for;
    }


    protected function shouldIncluseRelation(string $relation): bool
    {
        $include = request()->query('include');

        if(!$include) {
            return false;
        }

        $relations = array_map('trim', explode(",", $include));

        return  in_array($relation, $relations);
    }

}
