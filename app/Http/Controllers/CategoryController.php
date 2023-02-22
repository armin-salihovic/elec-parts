<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = QueryBuilder::for(Category::class)
            ->where('user_id', auth()->user()->id)
            ->allowedFilters(['name'])
            ->allowedSorts('name')
            ->paginate(10)
            ->appends(request()->query())
            ->through(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
            });

        return Inertia::render('Categories/Index', [
            'data' => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render('Categories/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:50'],
        ]);

        Category::create([
            'name' => $request->name,
            'source_id' => Source::where('name', 'Local')->first()->id,
            'user_id' => auth()->user()->id,
        ]);

        return Redirect::route('categories.index');
    }

    public function update(Category $category, Request $request)
    {
        $category->update(
            $request->validate([
                'name' => ['required', 'max:50'],
            ])
        );
    }
}
