<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = QueryBuilder::for(Project::class)
            ->allowedFilters(['name', 'sku', 'source.name'])
            ->paginate(10)
            ->appends(request()->query())
            ->through(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                ];
            });

        return Inertia::render('Projects/Index', [
            'data' => $projects,
        ]);
    }

    public function create()
    {
        return Inertia::render('Projects/Create');
    }

    public function show(Project $project)
    {
        return Inertia::render('Projects/Edit',[
            'project_name' => $project->name,
            'project_details' => [
                'bom_entries' => $project->projectParts->count(),
                'description' => $project->description,
                'created_at' => date('M. d, Y', strtotime($project->created_at)) . ' (' . $project->created_at->diffForHumans() . ')',
                'updated_at' => date('M. d, Y', strtotime($project->created_at)) . ' (' . $project->updated_at->diffForHumans() . ')',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:50'],
        ]);

        $project = Project::create([
            'name' => $request->name,
            'user_id' => auth()->user()->id,
        ]);

        return Redirect::route('projects.show', $project->id);
    }

    public function update(Project $project, Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:50'],
        ]);

        $project->update([
            'name' => $request->name,
        ]);
    }

    public function updateDescription(Project $project, Request $request)
    {
        $project->update([
            'description' => $request->description,
        ]);
    }

}
