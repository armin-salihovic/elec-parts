<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Http\Resources\PartWithQuantityResource;
use App\Models\Inventory;
use App\Models\InventoryDraft;
use App\Models\Part;
use App\Models\Source;
use App\Services\MouserService;
use App\Services\TaydaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PartController extends Controller
{
    private TaydaService $taydaService;

    private MouserService $mouserService;

    public function __construct()
    {
        $this->taydaService = new TaydaService();

        $this->mouserService = new MouserService();
    }

    public function index()
    {
        $parts = QueryBuilder::for(Part::class)
            ->where('user_id', auth()->user()->id)
            ->allowedFilters(['name', 'sku', 'description', 'category.name', AllowedFilter::exact('price')])
            ->allowedSorts('price')
            ->paginate(10)
            ->appends(request()->query())
            ->through(function ($part) {
                return [
                    'id' => $part->id,
                    'name' => $part->name,
                    'sku' => $part->sku,
                    'price' => $part->price,
                    'description' => $part->description,
                    'url' => $part->url,
                    'category.name' => $part->category->name,
                ];
            });

        return Inertia::render('Parts/Index', [
            'data' => $parts,
            'categories' => auth()->user()->categories,
        ]);
    }

    public function create()
    {
        $categories = auth()->user()->categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
            ];
        });

        return Inertia::render('Parts/Create', [
            'categories' => $categories,
        ]);
    }

    public function edit(Part $part)
    {
        $categories = auth()->user()->categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
            ];
        });

        return Inertia::render('Parts/Edit', [
            'part' => [
                'name' => $part->name,
                'sku' => $part->sku,
                'price' => $part->price,
                'url' => $part->url,
                'description' => $part->description,
                'category_id' => $part->category_id,
            ],
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:50'],
        ]);

        Part::create([
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'url' => $request->url,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'source_id' => Source::where('name', 'Local')->first()->id,
            'user_id' => auth()->user()->id,
        ]);

        return Redirect::route('parts.index');
    }

    public function update(Part $part, Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:50'],
        ]);

        $part->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'url' => $request->url,
            'description' => $request->description,
            'category_id' => $request->category_id,
        ]);

        return Redirect::route('parts.index');
    }

    public function show(Part $part)
    {

    }

    /**
     * @throws \Exception
     */
    public function importPartsDocument(Request $request)
    {
        $request->validate([
            'locationId' => 'required',
            'draftId' => 'required',
            'documentType' => 'required',
        ]);

        if (!auth()->user()->locations->contains('id', $request->input('locationId'))) return;

        if (!auth()->user()->inventoryDrafts->contains('id', $request->input('draftId'))) return;

        if ($request->hasFile('document')) {

            $file = $request->file('document');

            switch ($request->documentType) {
                case DocumentType::TaydaInvoice:
                    $partsCollection = $this->taydaService->getParts($file);
                    break;

                case DocumentType::MouserExcel:
                    $partsCollection = $this->mouserService->getParts($file);
                    break;

                default:
                    throw new \Exception("Unsupported document type");
            }

            $inventoryDraft = InventoryDraft::find($request->input('draftId'));
            $inventoryDraft->update(['location_id' => $request->input('locationId')]);

            $partsCollection->map(function ($part) use ($inventoryDraft) {
                if ($inventory = $inventoryDraft->inventories->where('part_id', $part->id)->where('location_id', $inventoryDraft->location->id)->first()) {
                    $inventory->quantity += $part->quantity;
                    $inventory->save();
                } else {
                    Inventory::create([
                        'part_id' => $part->id,
                        'location_id' => $inventoryDraft->location->id,
                        'inventory_draft_id' => $inventoryDraft->id,
                        'user_id' => auth()->user()->id,
                        'quantity' => $part->quantity,
                    ]);
                }
            });

            return PartWithQuantityResource::collection($partsCollection);
        }

        return response()->json([
            'message' => 'Document is missing from the request.'
        ], 400);
    }
}
