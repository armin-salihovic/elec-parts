<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectPart extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projectBuildParts(): HasMany
    {
        return $this->hasMany(ProjectBuildPart::class);
    }

    function inventoryQuantity(ProjectBuild $projectBuild)
    {
        $inventories = Inventory::where('inventory_draft_id', null)->whereHas('projectBuildParts', function ($q) use ($projectBuild) {
            $q->where('project_build_id', $projectBuild->id)->where('project_part_id', $this->id);
        })->get();

        $inventoryQuantity = 0;

        foreach ($inventories as $inventory) {
            $inventoryQuantity += $inventory->quantity;
        }

        return $inventoryQuantity;
    }

    function isLoaded(ProjectBuild $projectBuild): bool
    {

        if (!$projectBuild->completed)
            return $this->inventoryQuantity($projectBuild) >= $this->quantity * $projectBuild->quantity;

        $need = $this->quantity * $projectBuild->quantity;
        $have = 0;

        $projectBuildParts = $projectBuild->parts->where('project_part_id', $this->id)->where('used', true);

        foreach ($projectBuildParts as $projectBuildPart) {
            $have += $projectBuildPart->quantity;
        }

        return $need === $have;
    }

}
