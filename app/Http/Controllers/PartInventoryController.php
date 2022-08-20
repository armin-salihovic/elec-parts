<?php

namespace App\Http\Controllers;

use App\Models\PartInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PartInventoryController extends Controller
{
    public function index()
    {
        return Inertia::render('PartInventories/Index', [
            'data' => PartInventory::all()->map(function ($partInventory) {
                return [
                    'id' => $partInventory->id,
                    'name' => $partInventory->name,
                ];
            }),
        ]);
    }

    public function create()
    {
        return Inertia::render('PartInventories/Create');
    }

    public function store(Request $request)
    {
        dd($request);

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
