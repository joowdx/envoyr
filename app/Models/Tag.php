<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasUlids;

    protected $fillable = ['tag'];

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class);
    }
}
