<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBuild;
use App\Models\ProjectPart;
use App\Services\ProjectBuildService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectBuildController extends Controller
{
    private $projectBuildService;

    public function __construct(ProjectBuildService $projectBuildService)
    {
        $this->projectBuildService = $projectBuildService;
    }

    public function index(Project $project)
    {
        return Inertia::render('Projects/Edit', [
            'project_name' => $project->name,
            'project_builds' => ProjectBuild::where('project_id', $project->id)->get()->map(function ($projectBuild) {
                return [
                    'id' => $projectBuild->id,
                    'quantity' => $projectBuild->quantity,
                    'completed' => $projectBuild->completed,
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

        return to_route('project-builds.edit', [$project->id, $projectBuild->id]);
    }

    public function edit(Project $project, ProjectBuild $projectBuild)
    {
        if ($projectBuild->completed) {
            return to_route('project-builds.show', [$project->id, $projectBuild->id]);
        }

        return Inertia::render('ProjectBuilds/Edit', [
            'project_name' => $project->name,
            'project_build' => [
                'quantity' => $projectBuild->quantity,
                'selection_priority' => $projectBuild->selection_priority,
                'created_at' => $projectBuild->created_at,
            ],
            'project_parts' => ProjectPart::where('project_id', $project->id)->get()->map(function ($projectPart) use ($projectBuild) {
                return [
                    'id' => $projectPart->id,
                    'quantity' => $projectPart->quantity,
                    'inventory_quantity' => $projectPart->inventoryQuantity($projectBuild),
                    'part_name' => $projectPart->part_name,
                    'description' => $projectPart->description,
                    'designators' => $projectPart->designators,
                    'matched_parts' => [],
                    'matched_parts_loading' => true,
                ];
            })
        ]);
    }

    public function show(Project $project, ProjectBuild $projectBuild)
    {
        return Inertia::render('ProjectBuilds/Show', [
            'project_name' => $project->name,
            'project_build' => [
                'quantity' => $projectBuild->quantity,
                'completed' => $projectBuild->completed,
                'selection_priority' => $projectBuild->selection_priority,
                'created_at' => $projectBuild->created_at,
            ],
            'project_parts' => ProjectPart::where('project_id', $project->id)->get()->map(function ($projectPart) use ($projectBuild) {
                return [
                    'id' => $projectPart->id,
                    'quantity' => $projectPart->quantity,
                    'inventory_quantity' => $projectPart->inventoryQuantity($projectBuild),
                    'part_name' => $projectPart->part_name,
                    'description' => $projectPart->description,
                    'designators' => $projectPart->designators,
                    'matched_parts' => [],
                    'matched_parts_loading' => true,
                ];
            })
        ]);
    }

    public function build(ProjectBuild $projectBuild)
    {
        if ($projectBuild->completed) {
            return to_route('project-builds.show', [$projectBuild->project_id, $projectBuild->id]);
        }

        $this->projectBuildService->buildProject($projectBuild);

        return to_route('project-builds.index', $projectBuild->project_id);
    }

    public function undoBuild(ProjectBuild $projectBuild)
    {
        $this->projectBuildService->undoProjectBuild($projectBuild);

        return to_route('project-builds.index', $projectBuild->project_id);
    }

    public function updateBuildSelectionPriority(ProjectBuild $projectBuild, Request $request)
    {
        $request->validate([
            'selection_priority' => 'required | integer | between:0,3'
        ]);

        $projectBuild->update(['selection_priority' => $request->input('selection_priority')]);
    }

    public function destroy(ProjectBuild $projectBuild)
    {
        if($projectBuild->completed) {
            $this->projectBuildService->undoProjectBuild($projectBuild);
        }

        foreach ($projectBuild->parts as $part) {
            $part->delete();
        }

        $projectBuild->delete();

        return to_route('project-builds.index', $projectBuild->project_id);
    }
}
