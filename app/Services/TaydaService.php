<?php

namespace App\Services;

use App\Models\DistributorPart;
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
        return DistributorPart::where('sku', $product['sku'])->first();
    }

    /**
     * @throws \Exception
     */
    public function getParts($file): array
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
            $part = DistributorPart::where('sku', $product['sku'])->first();
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

        return [
            'parts' => $partsCollection,
            'failed_parts' => $failed,
        ];
    }

}
