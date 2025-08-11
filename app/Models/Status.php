<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasUlids;

    protected $fillable = [
        'title',
        'classification_id',
        'office_id',
    ];
}
