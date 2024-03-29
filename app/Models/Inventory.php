<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Inventory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function part(): MorphTo
    {
        return $this->morphTo('inventoryable');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function inventoryDraft(): BelongsTo
    {
        return $this->belongsTo(InventoryDraft::class);
    }

    public function projectBuildParts(): HasMany
    {
        return $this->hasMany(ProjectBuildPart::class);
    }
}
