<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $location = Location::create([
            'name' => $request->name,
            'user_id' => auth()->user()->id,
        ]);

        return to_route('inventories.create', [
            'location' => $location->id,
        ]);
    }
}
