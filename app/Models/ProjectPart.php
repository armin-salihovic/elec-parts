<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPart extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    function getSelectedInventoryParts($projectBuildParts)
    {
        $searchValues = preg_split('/\s+/', $this->part_name, -1, PREG_SPLIT_NO_EMPTY);

        $inventories = Inventory::where('inventory_draft_id', null);

        foreach($searchValues as $searchTerm){
            $inventories->whereHas('part', function($relation) use ($searchTerm){
                $relation->where('name', 'like', '%'.$searchTerm.'%');
            });
        }

        $inventories = $inventories->get();

        return $inventories->map(function ($inventory) use ($projectBuildParts) {
            return [
                'id' => $inventory->id,
                'name' => $inventory->part->name,
                'sku' => $inventory->part->sku,
                'quantity' => $inventory->quantity,
                'source' => $inventory->part->source->name,
                'location' => $inventory->location->name,
                'selected' => $projectBuildParts->contains('id', $inventory->id),
            ];
        });
    }

}
