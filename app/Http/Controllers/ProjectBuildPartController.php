<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Project;
use App\Models\ProjectBuild;
use App\Models\ProjectBuildPart;
use App\Models\ProjectPart;
use App\Services\ProjectBuildService;
use Illuminate\Http\Request;

class ProjectBuildPartController extends Controller
{
    private $projectBuildService;

    public function __construct(ProjectBuildService $projectBuildService)
    {
        $this->projectBuildService = $projectBuildService;
    }

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

    public function getProjectBuildPartsDraft(Project $project, ProjectBuild $projectBuild, ProjectPart $projectPart)
    {
        if($projectBuild->completed) {
            $projectBuildParts = $projectBuild->parts->where('project_part_id', $projectPart->id)->where('used', true);
        } else {
            $projectBuildParts = $this->projectBuildService->getProjectBuildPartsDraft($projectBuild, $projectPart);
        }

        return $projectBuildParts->map(function ($projectBuildPart) {
            return [
                'id' => $projectBuildPart->inventory->id,
                'name' => $projectBuildPart->inventory->part->name,
                'sku' => $projectBuildPart->inventory->part->sku,
                'quantity' => $projectBuildPart->quantity,
                'source' => $projectBuildPart->inventory->part->source->name,
                'location' => $projectBuildPart->inventory->location->name,
            ];
        });
    }

    public function store (Project $project, ProjectBuild $projectBuild, Request $request)
    {
        $request->validate([
            'inventory_id' => 'required',
            'project_part_id' => 'required',
        ]);

        $projectPart = ProjectPart::findOrFail($request->project_part_id);

        ProjectBuildPart::create([
            'project_build_id' => $projectBuild->id,
            'project_part_id' => $request->project_part_id,
            'inventory_id' => $request->inventory_id,
        ]);

        return response()->json([
            'inventory_quantity' => $projectPart->inventoryQuantity($projectBuild),
        ]);
    }

    public function show ()
    {

    }

    public function destroy (Project $project, ProjectBuild $projectBuild, ProjectPart $projectPart, Inventory $inventory)
    {
        $projectBuildPart = ProjectBuildPart::where('project_build_id', $projectBuild->id)
            ->where('project_part_id', $projectPart->id)
            ->where('inventory_id', $inventory->id)
            ->first();

        $projectBuildPart->delete();

        return response()->json([
            'inventory_quantity' => $projectPart->inventoryQuantity($projectBuild),
        ]);
    }
}
