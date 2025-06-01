<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Document extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'classification_id',
        'user_id',
        'office_id',
        'section_id',
        'source_id',
        'directive',
        'digital',
        'published_at',
        'status',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public static function booted(): void
    {
        static::forceDeleting(function (self $document) {
            $document->attachment->delete();

            $document->actions->each->delete();
        });

        static::creating(function (self $document) {
            $faker = fake()->unique();

            do {
                $codes = collect(range(1, 10))->map(fn () => $faker->bothify('??????####'))->toArray();

                $available = array_diff($codes, self::whereIn('code', $codes)->pluck('code')->toArray());
            } while (empty($available));

            $document->code = reset($available);
        });
    }

    // ✅ Add the publish method
    public function publish(): bool
    {
        // Check if already published
        if ($this->isPublished()) {
            return false;
        }

        // Update the document
        return $this->update([
            'published_at' => now(),
            'status' => 'published',
        ]);
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); 
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // For getting all transmittals
    public function transmittals(): HasMany
    {
        return $this->hasMany(Transmittal::class);
    }

    // For getting latest transmittal
    public function transmittal(): HasOne
    {
        return $this->transmittals()
            ->one()
            ->ofMany('created_at', 'max');
    }

    // For getting active (unreceived) transmittal
    public function activeTransmittal(): HasOne
    {
        return $this->transmittals()
            ->one()
            ->ofMany([
                'created_at' => 'max',
            ], function ($query) {
                $query->whereNull('received_at');
            });
    }

    // Add helper methods
    public function isPublished(): bool
    {
        return !is_null($this->published_at);
    }

    public function isDraft(): bool
    {
        return is_null($this->published_at);
    }

    // Add scopes
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function scopeDraft($query)
    {
        return $query->whereNull('published_at');
    }
}
