<?php

namespace App\Repositories;

use App\Models\Inventory;
use App\Models\ProjectBuild;
use App\Models\ProjectBuildPart;
use App\Models\ProjectPart;

class ProjectPartRepository
{
    private InventoryRepository $inventoryRepository;

    /**
     * @param InventoryRepository $inventoryRepository
     */
    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    public function isLoaded(ProjectBuild $projectBuild, ProjectPart $projectPart): bool
    {
        if (!$projectBuild->completed)
            return $this->inventoryQuantity($projectBuild, $projectPart) >= $projectPart->quantity * $projectBuild->quantity;

        $need = $projectPart->quantity * $projectBuild->quantity;
        $have = 0;

        $projectBuildParts = $projectBuild->parts->where('project_part_id', $projectPart->id)->where('used', true);

        foreach ($projectBuildParts as $projectBuildPart) {
            $have += $projectBuildPart->quantity;
        }

        return $need === $have;
    }

    public function inventoryQuantity(ProjectBuild $projectBuild, ProjectPart $projectPart): int
    {
        return (int)$projectPart
            ->projectBuildParts()
            ->where('project_build_id', $projectBuild->id)
            ->sum('quantity');
    }

}
