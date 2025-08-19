<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Filament\Panel;

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
        'password_reset_at',
        'otp_expires_at',
        'role',
        'avatar',
        'office_id',
        'section_id',
        'approved_by',
        'approved_at',
        'deactivated_at',
        'deactivated_by',
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
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        //'role' => UserRole::class,
        'deactivated_at' => 'datetime',
    ];

    public function deactivate(User $deactivatedBy): void
    {
        $this->update([
            'deactivated_at' => now(),
            'deactivated_by' => $deactivatedBy->id,
        ]);
    }

    public function needsPasswordReset(): bool
    {
        return is_null($this->password_reset_at) &&
        ($this->otp_expires_at === null || $this->otp_expires_at->isFuture());
    }
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // Adjust this logic based on your requirements
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar;
    }

    public function deactivatedByUser()
    {
        return is_null($this->password_reset_at) &&
        ($this->otp_expires_at === null || $this->otp_expires_at->isFuture());
    }
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // Adjust this logic based on your requirements
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar;
    }
}
