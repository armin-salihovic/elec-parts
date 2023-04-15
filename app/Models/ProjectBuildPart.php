<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBuildPart extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
