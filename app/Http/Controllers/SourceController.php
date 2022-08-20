<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class SourceController extends Controller
{
    public function index()
    {
        return Inertia::render('PartSources/Index', [
            'part_sources' => Source::all()->map(function ($partSource) {
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
        Source::create(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );
        return Redirect::route('pedal-types.index');
    }

    public function update(Source $partSource)
    {
        $partSource->update(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );
    }
}
