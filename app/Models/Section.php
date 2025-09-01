<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Section extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $with = ['head'];

    protected $fillable = [
        'name',
        'office_id',
        'user_id',
        'head_name',
        'designation',
    ];

    protected static function booted()
    {
        static::creating(function ($section) {
            if ($section->user_id && !$section->head_name) {
                $user = User::find($section->user_id);
                if ($user) {
                    $section->head_name = $user->name;
                    $section->designation = $user->designation;
                }
            }
        });

        static::updating(function ($section) {
            if ($section->isDirty('user_id') && $section->user_id) {
                $user = User::find($section->user_id);
                if ($user) {
                    $section->head_name = $user->name;
                    $section->designation = $user->designation;
                }
            }
        });
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function transmittals(): HasMany
    {
        return $this->hasMany(Transmittal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sectionHeadName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->head?->name ?? $this->head_name,
        );
    }

    public function sectionHeadDesignation(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->head?->designation ?? $this->designation,
        );
    }
}
