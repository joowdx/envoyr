<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    protected $fillable = ['model', 'brand', 'color', 'storage', 'ram', 'battery', 'camera', 'price'];
}
