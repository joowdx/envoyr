<?php

namespace App\Models;

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
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    use HasFactory, HasUlids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'office_id',
        'section_id',
        'designation',
        'invitation_token',
        'invitation_expires_at',
        'invitation_accepted_at',
        'invited_by',
        'approved_by',
        'approved_at',
        'deactivated_at',
        'deactivated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'invitation_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
        'invitation_expires_at' => 'datetime',
        'invitation_accepted_at' => 'datetime',
        'approved_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    // Invitation methods
    public static function createInvitation(string $email, UserRole $role, string $officeId, int $invitedBy): self
    {
        return self::create([
            'email' => $email,
            'role' => $role,
            'office_id' => $officeId,
            'invited_by' => $invitedBy,
            'invitation_token' => Str::random(64),
            'invitation_expires_at' => now()->addDays(7),
        ]);
    }

    public function isPendingInvitation(): bool
    {
        return !is_null($this->invitation_token) && 
               is_null($this->invitation_accepted_at) && 
               !$this->isInvitationExpired();
    }

    public function isInvitationExpired(): bool
    {
        return $this->invitation_expires_at && $this->invitation_expires_at->isPast();
    }

    public function acceptInvitation(array $data): void
    {
        $this->update([
            'name' => $data['name'],
            'password' => $data['password'], // Will be hashed by cast
            'designation' => $data['designation'],
            'invitation_accepted_at' => now(),
            'invitation_token' => null, // Clear token after acceptance
            'invitation_expires_at' => null,
        ]);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return !$this->isPendingInvitation() && 
               in_array($this->role, [
                   UserRole::ROOT,
                   UserRole::ADMINISTRATOR,
                   UserRole::LIAISON,
                   UserRole::FRONT_DESK,
               ]);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar;
    }

    // Relationships
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function deactivatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deactivated_by');
    }

    // Utility methods
    public function deactivate(User $deactivatedBy): void
    {
        $this->update([
            'deactivated_at' => now(),
            'deactivated_by' => $deactivatedBy->id,
        ]);
    }
}
