<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartInventory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
