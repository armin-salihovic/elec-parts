<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\ProjectPart;
use App\Repositories\InventoryRepository;
use App\Repositories\ProjectPartRepository;
use Illuminate\Support\Collection;

class InventoryService
{
    private InventoryRepository $inventoryRepository;
    private ProjectPartRepository $projectPartRepository;

    /**
     * @param InventoryRepository $inventoryRepository
     * @param ProjectPartRepository $projectPartRepository
     */
    public function __construct(InventoryRepository $inventoryRepository, ProjectPartRepository $projectPartRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
        $this->projectPartRepository = $projectPartRepository;
    }

    public function getSelectedInventoryParts(Collection $projectBuildParts, ProjectPart $projectPart): Collection
    {
        $searchValues = preg_split('/\s+/', $projectPart->part_name, -1, PREG_SPLIT_NO_EMPTY);

        $inventories = Inventory::where('inventory_draft_id', null);

        foreach ($searchValues as $searchTerm) {
            $inventories->whereHas('part', function ($relation) use ($searchTerm) {
                $relation->where('name', 'like', '%' . $searchTerm . '%');
            });
        }

        $inventories = $inventories->get();

        return $inventories->map(function ($inventory) use ($projectBuildParts, $projectPart) {

            $reservedQuantity = $this->inventoryRepository->getReservedInventoryQuantity($inventory);

            return [
                'id'                => $inventory->id,
                'name'              => $inventory->part->name,
                'sku'               => $inventory->part->sku,
                'quantity'          => $inventory->quantity - $reservedQuantity,
                'reserved_quantity' => $reservedQuantity,
                'source'            => $inventory->part->source->name,
                'location'          => $inventory->location->name,
                'selected'          => $projectBuildParts->contains('id', $inventory->id),
            ];
        });
    }
}
