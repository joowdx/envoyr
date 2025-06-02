<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    use HasUlids;

    protected $fillable = ['name'];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
