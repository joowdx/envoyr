<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transmittal extends Model
{
    use HasUlid;

    protected $fillable = [
        'code',
        'purpose',
        'remarks',
        'pick_up',
        'document_id',
        'from_office_id',
        'to_office_id',
        'from_section_id',
        'to_section_id',
        'from_user_id',
        'to_user_id',
        'liaison_id',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'pick_up' => 'boolean',
    ];

    public function infraOffice(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->from_office_id === $this->to_office_id,
        );
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function fromOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'from_office_id');
    }

    public function toOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'to_office_id');
    }

    public function fromSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'from_section_id');
    }

    public function toSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'to_section_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function liaison(): BelongsTo
    {
        return $this->belongsTo(User::class, 'liaison_id');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public static function booted(): void
    {
        static::creating(function (self $transmittal) {
            $faker = fake()->unique();

            do {
                $codes = collect(range(1, 10))->map(fn () => $faker->bothify('??????????'))->toArray();

                $available = array_diff($codes, self::whereIn('code', $codes)->pluck('code')->toArray());
            } while (empty($available));

            $transmittal->code = reset($available);
        });
    }
}
