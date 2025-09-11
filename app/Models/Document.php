<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public static function booted(): void
    {
        static::forceDeleting(function (self $document) {
            $document->attachments->each->delete();
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

    public function isDraft(): bool
    {
        return is_null($this->published_at);
    }

    public function isPublished(): bool
    {
        return ! is_null($this->published_at);
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

    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function attachment(): HasOne
    {
        return $this->hasOne(Attachment::class)
            ->whereNull('transmittal_id');
    }

    public function transmittals(): HasMany
    {
        return $this->hasMany(Transmittal::class)
            ->orderBy('id', 'desc');
    }

    public function transmittal(): HasOne
    {
        return $this->transmittals()
            ->one()
            ->ofMany();
    }

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

    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }

    public function scopeForOffice($query, $officeId)
    {
        return $query->where(function ($q) use ($officeId) {
            $q->where('office_id', $officeId)
                ->orWhereHas('transmittals', function ($transmittalQuery) use ($officeId) {
                    $transmittalQuery->where('to_office_id', $officeId)
                        ->whereNotNull('received_at');
                });
        });
    }

    public function isOwnedByOffice($officeId): bool
    {
        if ($this->activeTransmittal) {
            return $this->activeTransmittal->to_office_id === $officeId;
        }
        
        $lastReceivedTransmittal = $this->transmittals()
            ->whereNotNull('received_at')
            ->orderBy('received_at', 'desc')
            ->first();
            
        if ($lastReceivedTransmittal) {
            return $lastReceivedTransmittal->to_office_id === $officeId;
        }
        
        return $this->office_id === $officeId;
    }

}
