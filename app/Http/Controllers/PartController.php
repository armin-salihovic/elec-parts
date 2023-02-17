<?php

namespace App\Http\Controllers;

use App\Http\Resources\PartResource;
use App\Http\Resources\PartWithQuantityResource;
use App\Models\Part;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;

class PartController extends Controller
{
    public function index()
    {
        $parts = QueryBuilder::for(Part::class)
            ->allowedFilters(['name', 'sku', 'source.name'])
            ->paginate(10)
            ->appends(request()->query())
            ->through(function ($part) {
                return [
                    'id' => $part->id,
                    'name' => $part->name,
                    'sku' => $part->sku,
                    'url' => $part->url,
                    'source.name' => $part->source->name,
                ];
            });

        $sources = Source::all()->map(function ($source) {
            return [
                'name' => $source->name,
            ];
        });

        return Inertia::render('Parts/Index', [
            'data' => $parts,
            'sources' => $sources,
        ]);
    }

    public function create()
    {
        return Inertia::render('Parts/Create');
    }

    public function store()
    {
        Part::create(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );
        return Redirect::route('parts.index');
    }

    public function update(Part $part)
    {
        $part->update(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );
    }

    public function show(Part $part)
    {

    }

    public function findBySKU(Request $request)
    {
        if($sku = $request->input('sku')) {
            $part = Part::where('sku', $sku)->first();

            if($part === null) {
                return response()->json(['message' => "SKU <".$sku."> was not found."], 400);
            }

            return new PartResource($part);
        }

        return response()->json('Missing the SKU parameter.', 400);
    }

    private static function processDocument($page) {
        $array = [];
        for ($i = 0; $i < count($page); $i++) {
            if($page[$i][0] == '$' && $page[$i+1][0] == '$') {
                $item = [];
                $item['sku'] = $page[$i-2];
                $item['quantity'] = $page[$i-1];
                $item['price'] = $page[$i];
                $item['total'] = $page[$i+1];
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
                if($part === null) {
                    $part = PartController::fixSkuError($documentArray, $product);

                    if($part === null) {
                        $failed[]['product'] = $product;
                        continue;
                    }
                }
                $part['quantity'] = $product['quantity'];

                $partsCollection->push($part);
            }

            if(count($failed) > 0) {
                dump("Failed products: ".count($failed));
                dump($failed);
            } else if(count($products) === 0) {
                dd("failed");
            }

            return PartWithQuantityResource::collection($partsCollection);
        }

        return response()->json([
            'message' => 'Tayda Invoice PDF is missing from the request.'
        ], 400);
    }
}
