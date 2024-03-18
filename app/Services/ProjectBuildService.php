<?php

namespace App\Services;

use App\Enums\PriorityType;
use App\Models\ProjectBuild;
use App\Models\ProjectBuildPart;
use App\Models\ProjectPart;
use App\Repositories\InventoryRepository;
use Illuminate\Database\Eloquent\Collection;

class ProjectBuildService
{
    private InventoryRepository $inventoryRepository;

    /**
     * @param InventoryRepository $inventoryRepository
     */
    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    public function buildProject(ProjectBuild $projectBuild): void
    {
        foreach ($projectBuild->project->projectParts as $projectPart) {
            $projectBuildParts = $projectBuild->parts->where('project_part_id', $projectPart->id);

            $projectBuildParts = $this->sortProjectBuildPartsByPriority($projectBuild, $projectBuildParts);

            $this->updateInventories($projectPart, $projectBuild, $projectBuildParts);
        }

        $projectBuild->update(['completed' => true]);
    }

    public function undoProjectBuild(ProjectBuild $projectBuild): void
    {
        $projectBuildParts = $projectBuild->parts->where('used', true);

        foreach ($projectBuildParts as $projectBuildPart) {
            $projectBuildPart->inventory->quantity += $projectBuildPart->quantity;
            $projectBuildPart->used = false;

            $projectBuildPart->inventory->save();
            $projectBuildPart->save();
        }

        $projectBuild->update(['completed' => false]);
    }

    private function updateInventories($projectPart, ProjectBuild $projectBuild, $projectBuildParts): void
    {
        $need = $projectPart->quantity * $projectBuild->quantity;

        /** @var ProjectBuildPart $projectBuildPart */
        foreach ($projectBuildParts as $projectBuildPart) {

            $inventory = $projectBuildPart->inventory;

            $inventory->quantity -= $projectBuildPart->quantity;
            $need -= $projectBuildPart->quantity;

            $projectBuildPart->used = true;

            $inventory->save();
            $projectBuildPart->save();

//            dd($projectBuildPart->inventory->quantity);

            if ($need === 0) break;
        }
    }

    // todo: refactor $this->updateInventories(...
    public function getProjectBuildPartsDraft(ProjectBuild $projectBuild, ProjectPart $projectPart): Collection
    {
        $projectBuildParts = $projectBuild->parts->where('project_part_id', $projectPart->id);

        $projectBuildParts = $this->sortProjectBuildPartsByPriority($projectBuild, $projectBuildParts);

        $need = $projectPart->quantity * $projectBuild->quantity;

        foreach ($projectBuildParts as $projectBuildPart) {

            $inventory = $projectBuildPart->inventory;

            if ($inventory->quantity >= $need) {
                $inventory->quantity -= $need;
                $projectBuildPart->quantity = $need;
                $need = 0;
            } else {
                $projectBuildPart->quantity = $inventory->quantity;
                $need -= $inventory->quantity;
                $inventory->quantity = 0;
            }
            $projectBuildPart->used = true;

//            $inventory->save();
//            $projectBuildPart->save();

            if ($need === 0) break;
        }

        return $projectBuildParts->where('used', true);
    }


    public function sortProjectBuildPartsByPriority(ProjectBuild $projectBuild, Collection $projectBuildParts): Collection
    {
        switch ($projectBuild->selection_priority) {
            case PriorityType::FIFO:
                $projectBuildParts = $projectBuildParts->sortBy('inventory.created_at');
                break;
            case PriorityType::LIFO:
                $projectBuildParts = $projectBuildParts->sortByDesc('inventory.created_at');
                break;
            case PriorityType::SmallestStock:
                $projectBuildParts = $projectBuildParts->sortBy('inventory.quantity');
                break;
            case PriorityType::LargestStock:
                $projectBuildParts = $projectBuildParts->sortByDesc('inventory.quantity');
                break;
        }
        return $projectBuildParts;
    }

}
