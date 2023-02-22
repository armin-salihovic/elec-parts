<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
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
            ->allowedFilters(['part.name', 'part.sku', 'part.source.name', AllowedFilter::exact('quantity')])
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
                ];
            });

        $sources = Source::all()->map(function ($source) {
            return [
                'name' => $source->name,
            ];
        });

        return Inertia::render('Inventories/Index', [
            'data' => $inventories,
            'sources' => $sources,
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventories/Create');
    }

    public function store(Request $request)
    {
        // TODO: validate the request so we make sure that ID and quantity are there.

        foreach ($request['parts'] as $inventory) {
            if($part = Inventory::where('part_id', $inventory['id'])->first()) {
                $part->quantity += $inventory['quantity'];
                $part->save();
            } else {
                Inventory::create([
                    'part_id' => $inventory['id'],
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
