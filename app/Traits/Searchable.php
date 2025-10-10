<?php

// app/Traits/Searchable.php
namespace App\Traits;

trait Searchable
{
    /**
     * Recherche globale
     * À surcharger dans chaque model pour définir les champs
     */
    public function scopeSearch($query, string $term)
    {
        if (empty($term)) {
            return $query;
        }

        $searchableFields = $this->getSearchableFields();

        return $query->where(function($q) use ($term, $searchableFields) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'LIKE', "%{$term}%");
            }
        });
    }

    /**
     * Champs recherchables (à définir dans chaque model)
     */
    protected function getSearchableFields(): array
    {
        return property_exists($this, 'searchable') 
            ? $this->searchable 
            : ['name'];
    }

    /**
     * Recherche avancée avec relations
     */
    public function scopeSearchWithRelations($query, string $term, array $relations = [])
    {
        return $query->where(function($q) use ($term, $relations) {
            $q->search($term);

            foreach ($relations as $relation => $fields) {
                $q->orWhereHas($relation, function($subQuery) use ($term, $fields) {
                    foreach ($fields as $field) {
                        $subQuery->orWhere($field, 'LIKE', "%{$term}%");
                    }
                });
            }
        });
    }
}