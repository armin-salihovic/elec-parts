<?php

namespace App\Http\Controllers;

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
            ->whereHas('inventories', function ($query) {
                $query->where('inventory_draft_id', null);
            })
            ->allowedFilters(['name'])
            ->allowedSorts([AllowedSort::custom('size', new InventoryLocationSizeSort(), 'id'),])
            ->paginate(10)
            ->appends(request()->query())
            ->through(function ($location) {

                $locationSize = $location->inventories->filter(function ($inventory) {
                    return $inventory->inventory_draft_id === null;
                })->count();

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

    public function destroy(Location $location)
    {
        if (!$location->inventories->count())
            $location->delete();
    }
}
