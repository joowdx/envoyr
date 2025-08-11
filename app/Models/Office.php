<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasUlids;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'acronym',
        'head_name',
        'designation',
    ];

    public function sections(): HasMany{
        return $this->hasMany(Section::class);
    }

    public function documents(): HasMany{
        return $this->hasMany(Document::class);
    }

    public function users(): HasMany{
        return $this->hasMany(User::class);
    }
}
