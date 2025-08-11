<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_at',
    ];
}
