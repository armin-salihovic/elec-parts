<?php

namespace App\Services;

use App\Models\Part;
use App\Models\Source;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class MouserService
{
    private static function isMouserFile($firstRow)
    {
        return $firstRow[6] === "Mouser No:" && $firstRow[8] === "Desc.:" && $firstRow[10] === "Order Qty.";
    }

    /**
     * @throws \Exception
     */
    public function getParts($file): Collection
    {
        $mouserRows = Excel::toArray([], $file);

        if (!$this->isMouserFile($mouserRows[0][0])) {
            throw new \Exception("Not a valid mouser file");
        }

        unset($mouserRows[0][0]);

        $partsCollection = collect();

        foreach ($mouserRows[0] as $mouserRow) {
            $row['sku'] = $mouserRow[6];
            $row['description'] = $mouserRow[8];
            $row['quantity'] = $mouserRow[10];

            $part = Part::where('sku', $row['sku'])->first();
            if ($part === null) {
                $part = Part::create([
                    'name' => $row['description'],
                    'sku' => $row['sku'],
                    'price' => null,
                    'url' => null,
                    'description' => null,
                    'category_id' => 1,
                    'source_id' => Source::where('name', 'Mouser')->first()->id,
                    'user_id' => auth()->user()->id,
                ]);
            }
            $part['quantity'] = $row['quantity'];

            $partsCollection->push($part);
        }
        return $partsCollection;
    }
}
