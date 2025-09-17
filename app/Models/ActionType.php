<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionType extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'office_id',
        'name',
        'status_name',
        'finalizing_action',
        'prerequisite_action_type_id',
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

    public function prerequisiteActionType(): BelongsTo
    {
        return $this->belongsTo(ActionType::class, 'prerequisite_action_type_id');
    }
}
