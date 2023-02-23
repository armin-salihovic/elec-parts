<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Sorts\InventorySizeSort;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class LocationController extends Controller
{
    public function index()
    {
        $locations = QueryBuilder::for(Location::class)
            ->where('user_id', auth()->user()->id)
            ->allowedFilters(['name'])
            ->allowedSorts([AllowedSort::custom('size', new InventorySizeSort(), 'id'),])
            ->paginate(10)
            ->appends(request()->query())
            ->through(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'size' => $location->inventories->count(),
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

        return to_route('inventories.create', [
            'location' => $location->id,
        ]);
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
