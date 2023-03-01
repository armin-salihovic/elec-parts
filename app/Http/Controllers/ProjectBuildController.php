<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBuild;
use App\Models\ProjectPart;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectBuildController extends Controller
{
    public function index(Project $project)
    {
        return Inertia::render('Projects/Edit', [
            'project_name' => $project->name,
            'project_builds' => ProjectBuild::where('project_id', $project->id)->get()->map(function ($projectBuild) {
                return [
                    'id' => $projectBuild->id,
                    'quantity' => $projectBuild->quantity,
                    'created_at' => date('M. d, Y', strtotime($projectBuild->created_at)),
                ];
            }),
        ]);
    }

    public function create(Project $project)
    {
        return Inertia::render('ProjectBuilds/Create', [
            'project_name' => $project->name,
        ]);
    }

    public function store(Project $project, Request $request)
    {
        $request->validate([
            'quantity' => 'required | numeric',
        ]);

        $projectBuild = ProjectBuild::create([
            'quantity' => $request->quantity,
            'project_id' => $project->id,
        ]);

        return to_route('projects.builds.show', [$project->id, $projectBuild->id]);
    }

    public function show(Project $project, ProjectBuild $projectBuild)
    {
        return Inertia::render('ProjectBuilds/Edit', [
            'project_name' => $project->name,
            'project_build' => [
                'quantity' => $projectBuild->quantity,
                'created_at' => $projectBuild->created_at,
            ],
            'project_parts' => ProjectPart::where('project_id', $project->id)->get()->map(function ($projectPart) {
                return [
                    'id' => $projectPart->id,
                    'quantity' => $projectPart->quantity,
                    'part_name' => $projectPart->part_name,
                    'description' => $projectPart->description,
                    'designators' => $projectPart->designators,
                    'matched_parts' => [],
                    'matched_parts_loading' => true,
                ];
            })
        ]);
    }
}
