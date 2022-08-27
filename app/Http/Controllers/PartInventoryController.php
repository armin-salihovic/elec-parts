<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\Part;
use App\Models\PartInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PartInventoryController extends Controller
{
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

    public function index()
    {
        return Inertia::render('PartInventories/Index', [
            'data' => PartInventory::all()->map(function ($partInventory) {
                return [
                    'id' => $partInventory->id,
                    'name' => $partInventory->part->name,
                    'sku' => $partInventory->part->sku,
                    'quantity' => $partInventory->quantity,
                    'source' => $partInventory->part->source->name,
                ];
            }),
        ]);
    }

    public function create()
    {
        return Inertia::render('PartInventories/Create');
    }

    public static function processDocument($page) {
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

    public function store(Request $request)
    {
        if ($request->hasFile('file')) {
            // Parse PDF file and build necessary objects.
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($request->file[0]);

            $text = $pdf->getText();
            $text = trim(preg_replace('/\s+/', ' ', $text));
            $documentArray = explode(' ', $text);

            $products = PartInventoryController::processDocument($documentArray);

            $failed = [];

            foreach ($products as $product) {
                $part = Part::where('sku', $product['sku'])->first();
                if($part === null) {
                    $part = PartInventoryController::fixSkuError($documentArray, $product);

                    if($part === null) {
                        $failed[]['product'] = $product;
                        continue;
                    }
                }
                PartInventory::create([
                    'part_id' => $part->id,
                    'quantity' => $product['quantity'],
                ]);
            }
            if(count($failed) > 0) {
                dump("Failed products: ".count($failed));
                dump($failed);
            } else if(count($products) === 0) {
                dd("failed");
            }
            dd("success");
        }

        PartInventory::create(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );
        return Redirect::route('part-inventories.index');
    }

    public function update(PartInventory $partInventory)
    {
        $partInventory->update(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );
    }
}
