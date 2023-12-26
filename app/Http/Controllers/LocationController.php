<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryDraft;
use App\Models\Location;
use App\Sorts\InventoryLocationSizeSort;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class LocationController extends Controller
{
    public function index()
    {
        $locations = QueryBuilder::for(Location::class)
            ->where('locations.user_id', auth()->user()->id)
            ->allowedFilters(['name'])
            ->allowedSorts([AllowedSort::custom('size', new InventoryLocationSizeSort(), 'id'),])
            ->paginate(10)
            ->appends(request()->query())
            ->through(function ($location) {
                $locationSize = Inventory::where('inventory_draft_id', '=', null)
                    ->where('location_id', $location->id)
                    ->where('quantity', '>', 0)
                    ->count();

                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'size' => $locationSize,
                ];
            });

        return Inertia::render('Inventories/Index', [
            'locations' => $locations,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $location = Location::create([
            'name' => $request->name,
            'user_id' => auth()->user()->id,
        ]);

        $inventoryDraft = InventoryDraft::create([
            'location_id' => $location->id,
            'user_id' => auth()->user()->id,
        ]);

        return to_route('inventory-drafts.create', $inventoryDraft);
    }

    public function update(Location $location, Request $request)
    {
        $location->update(
            $request->validate([
                'name' => 'required',
            ])
        );
    }

    public function edit(Location $location)
    {
        $locations = auth()->user()->locations;

        $inventoryPartsFiltered = Inventory::where('inventory_draft_id', '=', null)
            ->where('location_id', $location->id)
            ->where('quantity', '>', 0)
            ->get();

        $inventoryParts = $inventoryPartsFiltered->map(function ($inventory) {
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

        return Inertia::render('Locations/Edit', [
            'locations' => $locations,
            'location_id' => $location->id,
            'location_name' => $location->name,
            'parts' => $inventoryParts,
        ]);
    }

    public function destroy(Location $location)
    {
        if ($location->inventories->contains('inventory_draft_id', null)) {
            return to_route('locations.index')->withErrors([
                'message' => 'Location contains parts in inventories.',
            ]);
        }

        foreach ($location->inventoryDrafts as $inventoryDraft) {
            if ($inventoryDraft->inventories->count() > 0) {
                return to_route('locations.index')->withErrors([
                    'message' => 'Location contains parts in drafts.',
                ]);
            } else {
                $inventoryDraft->delete();
            }
        }

        $location->delete();

        return to_route('locations.index');
    }
}
