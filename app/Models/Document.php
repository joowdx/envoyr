<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Document extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'dissemination',
        'electronic',
        'classification_id',
        'user_id',
        'office_id',
        'section_id',
        'source_id',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'dissemination' => 'boolean',
        'electronic' => 'boolean',
    ];

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function wasUnpublished(): bool
    {
        return !is_null($this->unpublished_at);
    }


    public function publish(): bool
    {
        if ($this->isPublished()) {
            return false;
        }

        return $this->update([
            'published_at' => now(),
        ]);
    }


    public function unpublish(): bool
    {
        if ($this->isDraft()) {
            return false;
        }

        return $this->update([
            'published_at' => null,
        ]);
    }


    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
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

    public function transmittals(): HasMany
    {
        return $this->hasMany(Transmittal::class);
    }

    public function attachment(): HasOne
    {
        return $this->hasOne(Attachment::class);
    }

    public function actions(): MorphMany
    {
        return $this->morphMany(Action::class, 'actionable');
    }


    public static function booted(): void
    {
        static::forceDeleting(function (self $document) {
            $document->attachment?->delete();
            $document->actions->each->delete();
        });

        static::creating(function (self $document) {
            if (empty($document->code)) {
                $faker = fake()->unique();

                do {
                    $codes = collect(range(1, 10))->map(fn () => $faker->bothify('??????####'))->toArray();
                    $available = array_diff($codes, self::whereIn('code', $codes)->pluck('code')->toArray());
                } while (empty($available));

                $document->code = reset($available);
            }

            // Set default status if not provided
            if (empty($document->status)) {
                $document->status = 'draft';
            }
        });
    }
}
