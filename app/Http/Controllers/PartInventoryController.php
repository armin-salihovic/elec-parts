<?php

namespace App\Http\Controllers;

use App\Models\PartInventory;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PartInventoryController extends Controller
{
    public function index()
    {

        $partInventories = QueryBuilder::for(PartInventory::class)
            ->allowedFilters(['part.name', 'part.sku', 'part.source.name', AllowedFilter::exact('quantity')])
            ->allowedSorts('quantity')
            ->paginate(10)
            ->appends(request()->query())
            ->through(function ($partInventory) {
                return [
                    'id' => $partInventory->id,
                    'part.name' => $partInventory->part->name,
                    'part.sku' => $partInventory->part->sku,
                    'quantity' => $partInventory->quantity,
                    'part.source.name' => $partInventory->part->source->name,
                ];
            });

        $sources = Source::all()->map(function ($source) {
            return [
                'name' => $source->name,
            ];
        });

        return Inertia::render('PartInventories/Index', [
            'data' => $partInventories,
            'sources' => $sources,
        ]);
    }

    public function create()
    {
        return Inertia::render('PartInventories/Create');
    }

    public function store(Request $request)
    {
        // TODO: validate the request so we make sure that ID and quantity are there.

        foreach ($request['parts'] as $partInventory) {
            if($part = PartInventory::where('part_id', $partInventory['id'])->first()) {
                $part->quantity += $partInventory['quantity'];
                $part->save();
            } else {
                PartInventory::create([
                    'part_id' => $partInventory['id'],
                    'quantity' => $partInventory['quantity'],
                ]);
            }
        }

        return Redirect::route('part-inventories.index');
    }

    public function update(PartInventory $partInventory, Request $request)
    {
        $request->validate([
            'quantity' => ['required', 'max:50'],
        ]);

        $partInventory->quantity = $request->quantity;
        $partInventory->save();
    }
}
