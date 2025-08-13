<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}