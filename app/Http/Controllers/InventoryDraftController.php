<?php

namespace App\Http\Controllers;

use App\Models\InventoryDraft;
use App\Models\Location;
use App\Sorts\InventoryDraftSizeSort;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class InventoryDraftController extends Controller
{
    public function index()
    {
        $inventoryDrafts = QueryBuilder::for(InventoryDraft::class)
            ->allowedFilters(['name'])
            ->where('inventory_drafts.user_id', auth()->user()->id)
            ->allowedSorts([AllowedSort::custom('size', new InventoryDraftSizeSort(), 'id'),])
            ->paginate(10)
            ->appends(request()->query())
            ->through(function ($inventoryDraft) {
                return [
                    'id' => $inventoryDraft->id,
                    'size' => $inventoryDraft->inventories->count(),
                    'created_at' => date('M. d, Y', strtotime($inventoryDraft->created_at)),
                ];
            });

        return Inertia::render('Inventories/Index', [
            'inventory_drafts' => $inventoryDrafts,
        ]);
    }

    public function create(InventoryDraft $inventoryDraft)
    {
        $locations = auth()->user()->locations;

        $inventoryDraftParts = auth()->user()
            ->inventories
            ->where('inventory_draft_id', $inventoryDraft->id)->map(function ($inventory) {
                return [
                    'id' => $inventory->id,
                    'name' => $inventory->part->name,
                    'sku' => $inventory->part->sku,
                    'category' => $inventory->part->category->name,
                    'quantity' => $inventory->quantity,
                    'part.source.name' => $inventory->part->source->name,
                    'location' => $inventory->location->id,
                ];
            });

        return Inertia::render('Inventories/Create', [
            'locations' => $locations,
            'location_id' => $inventoryDraft->location->id,
            'parts' => $inventoryDraftParts,
        ]);
    }

    public function store(Location $location)
    {
        $inventoryDraft = InventoryDraft::create([
            'location_id' => $location->id,
            'user_id' => auth()->user()->id,
        ]);

        return to_route('inventory-drafts.create', $inventoryDraft);
    }

    public function destroy(InventoryDraft $inventoryDraft)
    {
        $inventoryDraft->inventories->map(function ($inventory) {
            $inventory->delete();
        });
        $inventoryDraft->delete();
    }
}
