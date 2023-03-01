<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Project;
use App\Models\ProjectPart;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectPartController extends Controller
{
    function index(Project $project)
    {
        return Inertia::render('Projects/Edit', [
            'project_name' => $project->name,
            'project_parts' => ProjectPart::where('project_id', $project->id)->get()->map(function ($projectPart) {
                return [
                    'id' => $projectPart->id,
                    'quantity' => $projectPart->quantity,
                    'part_name' => $projectPart->part_name,
                    'description' => $projectPart->description,
                    'designators' => $projectPart->designators,
                ];
            }),
        ]);
    }

    function store(Project $project, Request $request)
    {
        $request->validate([
            'quantity' => ['required', 'max:50'],
            'part_name' => ['required'],
            'designators' => ['required'],
        ]);

        ProjectPart::create([
            'quantity' => $request->quantity,
            'part_name' => $request->part_name,
            'description' => $request->description,
            'designators' => $request->designators,
            'project_id' => $project->id,
        ]);

    }

    function update(ProjectPart $projectPart, Request $request)
    {
        $request->validate([
            'quantity' => ['required', 'max:50'],
            'part_name' => ['required'],
            'designators' => ['required'],
        ]);

        $projectPart->update([
            'quantity' => $request->quantity,
            'part_name' => $request->part_name,
            'description' => $request->description,
            'designators' => $request->designators,
        ]);
    }

    function destroy(ProjectPart $projectPart)
    {
        $projectPart->delete();
    }

    function matchBomToParts(ProjectPart $projectPart)
    {
        $searchValues = preg_split('/\s+/', $projectPart->part_name, -1, PREG_SPLIT_NO_EMPTY);

        $inventories = Inventory::where('inventory_draft_id', null);

        foreach($searchValues as $searchTerm){
            $inventories->whereHas('part', function($relation) use ($searchTerm){
                    $relation->where('name', 'like', '%'.$searchTerm.'%');
                });
        }

        $inventories = $inventories->get();

        return $inventories->map(function ($inventory) {
            return [
                'name' => $inventory->part->name,
                'sku' => $inventory->part->sku,
                'quantity' => $inventory->quantity,
                'source' => $inventory->part->source->name,
                'location' => $inventory->location->name,
            ];
        });
    }
}
