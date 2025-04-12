<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use HasUlids;

    protected $fillable = [
        'acronym',
        'name',
        'type',
        'head_name',
        'designation',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
