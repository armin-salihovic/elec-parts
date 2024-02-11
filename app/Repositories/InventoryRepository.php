<?php

namespace App\Repositories;

use App\Models\Inventory;
use App\Models\ProjectBuildPart;

class InventoryRepository
{
    public function getReservedInventoryQuantity(Inventory $inventory): int
    {
        $projectBuildParts = ProjectBuildPart
            ::where('inventory_id', $inventory->id)
            ->where('used', 0)
            ->whereHas('projectBuild', function ($query) {
                $query->where('completed', 0);
            })
            ->get();

        $quantity = 0;
        foreach ($projectBuildParts as $projectBuildPart) {
            $quantity += $projectBuildPart->quantity;
        }

        return $quantity;
    }

    public function getUsableInventoryQuantity(Inventory $inventory): int
    {
        $reserveQuantity = $this->getReservedInventoryQuantity($inventory);
        return $inventory->quantity - $reserveQuantity;
    }

}
