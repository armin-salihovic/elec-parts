<?php

namespace App\Http\Controllers;

use App\Models\PedalType;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class PedalTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('PedalTypes/Index', [
            'pedal_types' => PedalType::all()->map(function ($pedalType) {
                return [
                    'id' => $pedalType->id,
                    'name' => $pedalType->name,
                ];
            }),
        ]);
    }

    public function create()
    {
        return Inertia::render('PedalTypes/Create');
    }

    public function store()
    {
        PedalType::create(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );
        return Redirect::route('pedal-types.index');
    }

    public function update(PedalType $pedalType)
    {
        $pedalType->update(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );
    }
}
