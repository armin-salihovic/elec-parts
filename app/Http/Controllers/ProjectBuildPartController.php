<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Project;
use App\Models\ProjectBuild;
use App\Models\ProjectBuildPart;
use App\Models\ProjectPart;
use App\Repositories\InventoryRepository;
use App\Repositories\ProjectPartRepository;
use App\Services\InventoryService;
use App\Services\ProjectBuildService;
use Illuminate\Http\Request;

class ProjectBuildPartController extends Controller
{
    private ProjectBuildService $projectBuildService;
    private InventoryService $inventoryService;
    private InventoryRepository $inventoryRepository;
    private ProjectPartRepository $projectPartRepository;

    /**
     * @param ProjectBuildService $projectBuildService
     * @param InventoryService $inventoryService
     * @param InventoryRepository $inventoryRepository
     * @param ProjectPartRepository $projectPartRepository
     */
    public function __construct(ProjectBuildService $projectBuildService, InventoryService $inventoryService, InventoryRepository $inventoryRepository, ProjectPartRepository $projectPartRepository)
    {
        $this->projectBuildService = $projectBuildService;
        $this->inventoryService = $inventoryService;
        $this->inventoryRepository = $inventoryRepository;
        $this->projectPartRepository = $projectPartRepository;
    }


    public function index (Project $project, ProjectBuild $projectBuild, ProjectPart $projectPart)
    {
        $inventories = Inventory::where('inventory_draft_id', null)->whereHas('projectBuildParts', function ($q) use ($projectBuild, $projectPart) {
           $q->where('project_build_id', $projectBuild->id)->where('project_part_id', $projectPart->id);
        })->get();

        $projectBuildParts = $this->inventoryService->getSelectedInventoryParts($inventories, $projectPart);

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
        $inventory = Inventory::findOrFail($request->inventory_id);

        $usableInventoryQuantity = $this->inventoryRepository->getUsableInventoryQuantity($inventory);

        if($usableInventoryQuantity === 0) {
            return response()->json([
                'error' => "Usable inventory is 0. Cannot reserve this part.",
            ], 400);
        }

        $wantQuantity = $projectBuild->quantity * $projectPart->quantity;

        if($wantQuantity > $usableInventoryQuantity) {
            $wantQuantity = $usableInventoryQuantity;
        }

        ProjectBuildPart::create([
            'project_build_id' => $projectBuild->id,
            'project_part_id' => $request->project_part_id,
            'inventory_id' => $inventory->id,
            'quantity' => $wantQuantity,
        ]);

        return response()->json([
            'inventory_quantity' => $this->projectPartRepository->inventoryQuantity($projectBuild, $projectPart),
            'available_inv_quantity' => $this->inventoryRepository->getUsableInventoryQuantity($inventory),
            'is_loaded' => $this->projectPartRepository->isLoaded($projectBuild, $projectPart),
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
            'is_loaded' => $this->projectPartRepository->isLoaded($projectBuild, $projectPart),
        ]);
    }
}
