<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Project;
use App\Models\ProjectBuild;
use App\Models\ProjectBuildPart;
use App\Models\ProjectPart;
use Illuminate\Http\Request;

class ProjectBuildPartController extends Controller
{
    public function index (Project $project, ProjectBuild $projectBuild, ProjectPart $projectPart)
    {
        $inventories = Inventory::where('inventory_draft_id', null)->whereHas('projectBuildParts', function ($q) use ($projectBuild, $projectPart) {
           $q->where('project_build_id', $projectBuild->id)->where('project_part_id', $projectPart->id);
        })->get();

        $projectBuildParts = $projectPart->getSelectedInventoryParts($inventories);

        if($inventories->count() > 0) {
            $sorted = $projectBuildParts->sortByDesc('selected');
            return $sorted->values()->all();
        }

        return $projectBuildParts;
    }

    public function store (Project $project, ProjectBuild $projectBuild, Request $request)
    {
        $request->validate([
            'inventory_id' => 'required',
            'project_part_id' => 'required',
        ]);

        ProjectBuildPart::create([
            'project_build_id' => $projectBuild->id,
            'project_part_id' => $request->project_part_id,
            'inventory_id' => $request->inventory_id,
        ]);
    }

    public function show ()
    {

    }

    public function delete (Project $project, ProjectBuild $projectBuild, ProjectPart $projectPart, Inventory $inventory)
    {
        $projectBuildPart = ProjectBuildPart::where('project_build_id', $projectBuild->id)
            ->where('project_part_id', $projectPart->id)
            ->where('inventory_id', $inventory->id)
            ->first();

        $projectBuildPart->delete();
    }
}
