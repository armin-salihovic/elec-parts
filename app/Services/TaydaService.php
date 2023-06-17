<?php

namespace App\Services;

use App\Models\Part;
use Illuminate\Support\Collection;

class TaydaService
{
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

    /**
     * @throws \Exception
     */
    public function getParts($file): Collection
    {
        // Parse PDF file and build necessary objects.
        $parser = new \Smalot\PdfParser\Parser();

        $pdf = $parser->parseFile($file);

        $text = $pdf->getText();
        $text = trim(preg_replace('/\s+/', ' ', $text));
        $documentArray = explode(' ', $text);

        $products = TaydaService::processDocument($documentArray);

        $failed = [];

        $partsCollection = collect();

        foreach ($products as $product) {
            $part = Part::where('sku', $product['sku'])->first();
            if ($part === null) {
                $part = TaydaService::fixSkuError($documentArray, $product);

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

            throw new \Exception("Failed products: " . count($failed));
        } else if (count($products) === 0) {
//            dd("failed");
            throw new \Exception("Failed");
        }

        return $partsCollection;
    }

}
