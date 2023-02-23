<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InventoryController extends Controller
{
    public function index()
    {
        $inventories = QueryBuilder::for(Inventory::class)
            ->allowedFilters(['part.name', 'part.sku', 'part.source.name', 'location.name', AllowedFilter::exact('quantity')])
            ->allowedSorts('quantity')
            ->paginate(10)
            ->appends(request()->query())
            ->through(function ($inventory) {
                return [
                    'id' => $inventory->id,
                    'part.name' => $inventory->part->name,
                    'part.sku' => $inventory->part->sku,
                    'quantity' => $inventory->quantity,
                    'part.source.name' => $inventory->part->source->name,
                    'location.name' => $inventory->location->name,
                ];
            });

        $sources = Source::all()->map(function ($source) {
            return [
                'name' => $source->name,
            ];
        });

        return Inertia::render('Inventories/Index', [
            'data' => $inventories,
            'locations' => auth()->user()->locations,
            'sources' => $sources,
        ]);
    }

    public function create(Request $request)
    {
        $locations = auth()->user()->locations;

        if ($request->has('location')) {
            $locationId = $request->input('location');
            $location = Location::where('id', $locationId)->where('user_id', auth()->user()->id)->first();

            if($location === null) {
                return to_route('inventories.create');
            }

        } else {
            return Inertia::render('Locations/Create', [
                'locations' => $locations,
            ]);
        }

        return Inertia::render('Inventories/Create', [
            'locations' => $locations,
            'location_id' => $locationId,
        ]);
    }

    public function store(Request $request)
    {
        // TODO: validate the request so we make sure that ID and quantity are there.

        foreach ($request['parts'] as $inventory) {
            if($part = Inventory::where('part_id', $inventory['id'])->where('location_id', $inventory['location'])->first()) {
                $part->quantity += $inventory['quantity'];
                $part->save();
            } else {
                Inventory::create([
                    'part_id' => $inventory['id'],
                    'location_id' => $inventory['location'],
                    'quantity' => $inventory['quantity'],
                ]);
            }
        }

        return Redirect::route('inventories.index');
    }

    public function update(Inventory $inventory, Request $request)
    {
        $request->validate([
            'quantity' => ['required', 'max:50'],
        ]);

        $inventory->quantity = $request->quantity;
        $inventory->save();
    }
}
