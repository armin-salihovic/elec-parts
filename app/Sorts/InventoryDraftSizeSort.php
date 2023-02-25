<?php

namespace App\Sorts;

use Illuminate\Database\Eloquent\Builder;

class InventoryDraftSizeSort implements \Spatie\QueryBuilder\Sorts\Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $direction = $descending ? 'DESC' : 'ASC';
        $query
            ->selectRaw('inventory_drafts.id as id, inventory_drafts.created_at as created_at, count(inventories.id) as draft_size')
            ->leftJoin('inventories', 'inventory_drafts.id', '=', 'inventories.inventory_draft_id')
            ->groupBy('id', 'created_at')
            ->orderByRaw("draft_size {$direction}");
    }
}
