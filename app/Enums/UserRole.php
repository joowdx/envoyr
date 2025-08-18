<?php

namespace App\Enums;

use App\Enums\Contracts\HasDescription;
use App\Enums\Contracts\HasLabel;

enum UserRole: string implements HasDescription, HasLabel
{
    case ROOT = 'root';
    case ADMINISTRATOR = 'administrator';
    case LIAISON = 'liaison';
    case FRONT_DESK = 'front_desk';
    case USER = 'user';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ROOT => 'Root',
            self::ADMINISTRATOR => 'Administrator',
            self::LIAISON => 'Liaison',
            self::FRONT_DESK => 'Front Desk',
            self::USER => 'User',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::ROOT => 'User with full access to the system.',
            self::ADMINISTRATOR => 'User with full access to the office.',
            self::LIAISON => 'User with access to moderate incoming requests.',
            self::FRONT_DESK => 'User who typically receives the incoming documents.',
            self::USER => 'User with standard access to the system.',
            default => '',
        };
    }

    public static function options(bool $root = false): array
    {
        $filtered = array_filter(
            self::cases(),
            fn (self $role) => $root || ! in_array($role, [self::ROOT])
        );

        return array_combine(
            array_map(fn (self $role) => $role->value, $filtered),
            array_map(fn (self $role) => $role->getLabel(), $filtered)
        );
    }
}
