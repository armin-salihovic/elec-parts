<?php

namespace App\Http\Controllers;

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
}
