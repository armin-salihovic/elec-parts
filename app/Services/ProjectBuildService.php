<?php

namespace App\Services;

use App\Enums\PriorityType;
use App\Models\Project;
use App\Models\ProjectBuild;
use App\Models\ProjectPart;

class ProjectBuildService
{
    public function buildProject(Project $project, ProjectBuild $projectBuild): void
    {
        foreach ($project->projectParts as $projectPart) {
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
            $projectBuildPart->quantity = 0;
            $projectBuildPart->used = false;

            $projectBuildPart->inventory->save();
            $projectBuildPart->save();
        }

        $projectBuild->update(['completed' => false]);
    }

    private function updateInventories($projectPart, ProjectBuild $projectBuild, $projectBuildParts): void
    {
        $need = $projectPart->quantity * $projectBuild->quantity;

        foreach ($projectBuildParts as $projectBuildPart) {

            $inventoryPart = $projectBuildPart->inventory;

            if ($inventoryPart->quantity >= $need) {
                $inventoryPart->quantity -= $need;
                $projectBuildPart->quantity = $need;
                $need = 0;
            } else {
                $projectBuildPart->quantity = $inventoryPart->quantity;
                $need -= $inventoryPart->quantity;
                $inventoryPart->quantity = 0;
            }
            $projectBuildPart->used = true;

            $inventoryPart->save();
            $projectBuildPart->save();

            if ($need === 0) break;
        }
    }

    // todo: refactor $this->updateInventories(...
    public function getProjectBuildPartsDraft(ProjectBuild $projectBuild, ProjectPart $projectPart)
    {
        $projectBuildParts = $projectBuild->parts->where('project_part_id', $projectPart->id);

        $projectBuildParts = $this->sortProjectBuildPartsByPriority($projectBuild, $projectBuildParts);

        $need = $projectPart->quantity * $projectBuild->quantity;

        foreach ($projectBuildParts as $projectBuildPart) {

            $inventoryPart = $projectBuildPart->inventory;

            if ($inventoryPart->quantity >= $need) {
                $inventoryPart->quantity -= $need;
                $projectBuildPart->quantity = $need;
                $need = 0;
            } else {
                $projectBuildPart->quantity = $inventoryPart->quantity;
                $need -= $inventoryPart->quantity;
                $inventoryPart->quantity = 0;
            }
            $projectBuildPart->used = true;

//            $inventoryPart->save();
//            $projectBuildPart->save();

            if ($need === 0) break;
        }

        return $projectBuildParts->where('used', true);
    }


    public function sortProjectBuildPartsByPriority(ProjectBuild $projectBuild, $projectBuildParts)
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
