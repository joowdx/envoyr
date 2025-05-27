<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];

    public static function booted(): void
    {
        static::forceDeleting(function (self $document) {
            $document->attachment->delete();

            $document->actions->each->delete();
        });

        static::creating(function (self $document) {
            $faker = fake();

            do {
                $codes = collect(range(1, 10))->map(fn () => $faker->bothify('??????####'))->toArray();

                $available = array_diff($codes, self::whereIn('code', $codes)->pluck('code')->toArray());
            } while (empty($available));

            $document->code = reset($available);
        });
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

    public function transmittal()
    {
        return $this->hasOne(Transmittal::class);
    }
}
