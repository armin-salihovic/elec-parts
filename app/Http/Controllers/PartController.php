<?php

namespace App\Http\Controllers;

use App\Http\Resources\PartWithQuantityResource;
use App\Models\Inventory;
use App\Models\InventoryDraft;
use App\Models\Part;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PartController extends Controller
{
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

    private static function processDocument($page)
    {
        $array = [];
        for ($i = 0; $i < count($page); $i++) {
            if ($page[$i][0] == '$' && $page[$i + 1][0] == '$') {
                $item = [];
                $item['sku'] = $page[$i - 2];
                $item['quantity'] = $page[$i - 1];
                $item['price'] = $page[$i];
                $item['total'] = $page[$i + 1];
                $array[] = $item;
                $i += 2;
            }
        }
        return $array;
    }

    private static function fixSkuError(array $document, mixed $product)
    {
        for ($i = 0; $i < count($document); $i++) {
            if ($document[$i] == $product['sku']) {
                $product['sku'] = $document[$i - 1] . $product['sku'];
                break;
            }
        }
        return Part::where('sku', $product['sku'])->first();
    }

    public function pdfToProducts(Request $request)
    {
        $request->validate([
            'locationId' => 'required',
            'draftId' => 'required',

        ]);

        if (!auth()->user()->locations->contains('id', $request->input('locationId'))) return;

        if (!auth()->user()->inventoryDrafts->contains('id', $request->input('draftId'))) return;

        if ($request->hasFile('taydaInvoice')) {

            // Parse PDF file and build necessary objects.
            $parser = new \Smalot\PdfParser\Parser();

            $pdf = $parser->parseFile($request->file('taydaInvoice'));

            $text = $pdf->getText();
            $text = trim(preg_replace('/\s+/', ' ', $text));
            $documentArray = explode(' ', $text);

            $products = PartController::processDocument($documentArray);

            $failed = [];

            $partsCollection = collect();

            foreach ($products as $product) {
                $part = Part::where('sku', $product['sku'])->first();
                if ($part === null) {
                    $part = PartController::fixSkuError($documentArray, $product);

                    if ($part === null) {
                        $failed[]['product'] = $product;
                        continue;
                    }
                }
                $part['quantity'] = $product['quantity'];

                $partsCollection->push($part);
            }

            if (count($failed) > 0) {
                dump("Failed products: " . count($failed));
                dump($failed);
            } else if (count($products) === 0) {
                dd("failed");
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
            'message' => 'Tayda Invoice PDF is missing from the request.'
        ], 400);
    }
}
