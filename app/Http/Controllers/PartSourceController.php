<?php

namespace App\Http\Controllers;

use App\Models\PartSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PartSourceController extends Controller
{
    public function index()
    {
        return Inertia::render('PartSources/Index', [
            'part_sources' => PartSource::all()->map(function ($partSource) {
                return [
                    'id' => $partSource->id,
                    'name' => $partSource->name,
                ];
            }),
        ]);
    }

    public function create()
    {
        return Inertia::render('PartSources/Create');
    }

    public function store()
    {
        PartSource::create(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );
        return Redirect::route('pedal-types.index');
    }

    public function update(PartSource $partSource)
    {
        $partSource->update(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );
    }
}
