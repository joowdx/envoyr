<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionType extends Model
{
    protected $fillable = [
        'office_id',
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(OfficeAction::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($actionType) {
            $actionType->slug = Str::slug($actionType->name);
        });

        static::updating(function ($actionType) {
            if ($actionType->isDirty('name')) {
                $actionType->slug = Str::slug($actionType->name);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
