<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryDraft;
use App\Models\Part;
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
            ->where('user_id', auth()->user()->id)
            ->allowedFilters(['part.name', 'part.sku', 'part.source.name', 'location.name', AllowedFilter::exact('quantity')])
            ->allowedSorts('quantity')
            ->where('inventory_draft_id', null)
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

        return Inertia::render('Locations/Create', [
            'locations' => $locations,
        ]);
    }

    public function store(InventoryDraft $inventoryDraft, Request $request)
    {
        $inventories = auth()->user()->inventories->where('inventory_draft_id', null);

        $inventoryDraft->inventories->map(function ($inventoryDraftPart) use ($inventories) {
            if ($inventory = $inventories->where('part_id', $inventoryDraftPart->part->id)->where('location_id', $inventoryDraftPart->location->id)->first()) {
                $inventory->quantity += $inventoryDraftPart->quantity;
                $inventory->inventory_draft_id = null;
                $inventory->save();
                $inventoryDraftPart->delete();
            } else {
                $inventoryDraftPart->inventory_draft_id = null;
                $inventoryDraftPart->save();
            }
        });

        $inventoryDraft->delete();

        return Redirect::route('inventories.index');
    }

    public function update(Inventory $inventory, Request $request)
    {
        $request->validate([
            'quantity' => ['required'],
        ]);

        $inventory->quantity = $request->quantity;
        $inventory->save();
    }

    public function updateLocation(Inventory $inventory, Request $request)
    {
        $request->validate([
            'location' => ['required'],
        ]);

        $existingInventory = auth()->user()
            ->inventories
            ->where('inventory_draft_id', $inventory->inventory_draft_id)
            ->where('location_id', $request->input('location'))
            ->where('part_id', $inventory->part->id)
            ->first();

        if ($existingInventory && $inventory->id === $existingInventory->id) return;

        if ($existingInventory) {
            $existingInventory->quantity += $inventory->quantity;
            $existingInventory->save();
            $inventory->delete();
        } else {
            $inventory->location_id = $request->input('location');
            $inventory->save();
        }

        return response()->json();
    }

    public function addBySKU(Request $request)
    {
        $request->validate([
            'sku' => 'required',
            'location' => 'required',
            'quantity' => 'required | numeric',
        ]);

        $part = Part::where('sku', $request->sku)->first();

        if ($part === null) {
            return response()->json(['message' => "SKU <" . $request->sku . "> was not found."], 400);
        }

        $hasLocation = auth()->user()->locations->contains('id', $request->location);

        if (!$hasLocation) {
            return response()->json(['message' => "Location is not valid."], 400);
        }

        $inventoryDraftId = null;

        if ($request->has('draft')) {
            $hasDraft = auth()->user()->inventoryDrafts->contains('id', $request->draft);

            if (!$hasDraft) {
                return response()->json(['message' => "Draft is not valid."], 400);
            }

            $inventoryDraftId = $request->input('draft');
        }

        if ($inventoryDraftId) {
            $inventoryDraft = InventoryDraft::find($request->draft);
            $inventoryDraft->update(['location_id' => $request->location]);
        }

        $inventoryPart = auth()->user()->inventories
            ->where('inventory_draft_id', $inventoryDraftId)
            ->where('part_id', $part->id)
            ->where('location_id', $request->location)
            ->first();

        if ($inventoryPart) {
            $inventoryPart->quantity += $request->quantity;
            $inventoryPart->save();
        } else {
            Inventory::create([
                'part_id' => $part->id,
                'location_id' => $request->location,
                'inventory_draft_id' => $inventoryDraftId,
                'user_id' => auth()->user()->id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json();
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
    }
}
