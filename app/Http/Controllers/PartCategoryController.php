<?php

namespace App\Http\Controllers;

use App\Models\PartCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PartCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('PartCategories/Index', [
            'pedal_types' => PartCategory::all()->map(function ($partCategory) {
                return [
                    'id' => $partCategory->id,
                    'name' => $partCategory->name,
                ];
            }),
        ]);
    }

    public function create()
    {
        return Inertia::render('PartCategories/Create');
    }

    public function store()
    {
        PartCategory::create(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );
        return Redirect::route('part-categories.index');
    }

    public function update(PartCategory $partCategory)
    {
        $partCategory->update(
            Request::validate([
                'name' => ['required', 'max:50'],
            ])
        );
    }
}
