<?php

namespace App\Http\Controllers;

use App\Models\PedalType;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class PedalTypeController extends Controller
{
    public function index() {
        return Inertia::render('PedalTypes/Index', [
            'pedal_types' => PedalType::all(),
            'create_url' => URL::route('pedal-types.create'),
        ]);
    }

    public function create() {
        return Inertia::render('PedalTypes/Create');
    }

    public function store() {
        PedalType::create(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );

        return Redirect::route('pedal-types.index');
    }
}
