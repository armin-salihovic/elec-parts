<?php

namespace App\Sorts;

use Illuminate\Database\Eloquent\Builder;

class InventoryLocationSizeSort implements \Spatie\QueryBuilder\Sorts\Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $direction = $descending ? 'DESC' : 'ASC';
        $query
            ->selectRaw('locations.id as id, locations.name as name, count(inventories.id) as location_size')
            ->leftJoin('inventories', 'locations.id', '=', 'inventories.location_id')
            ->groupBy('name', 'id')
            ->orderByRaw("location_size {$direction}");
    }
}
