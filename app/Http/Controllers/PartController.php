<?php

namespace App\Http\Controllers;

use App\Http\Resources\PartResource;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PartController extends Controller
{
    public function index()
    {
        return Inertia::render('Parts/Index', [
            'pedal_types' => Part::all()->map(function ($part) {
                return [
                    'id' => $part->id,
                    'name' => $part->name,
                ];
            }),
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
}
