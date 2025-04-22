<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUlids, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'office_id',
        'section_id',
        'is_approved',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'deactivated_at' => 'datetime',
            'is_approved' => 'boolean',
        ];
    }

    public function deactivate(User $deactivatedBy): void
    {
        $this->update([
            'deactivated_at' => now(),
            'deactivated_by' => $deactivatedBy->id,
        ]);
    }

    public function reactivate(): void
    {
        $this->update([
            'deactivated_at' => null,
        ]);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }


    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
