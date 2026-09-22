<?php

declare(strict_types=1);

namespace Spodnet\ComposerOutdatedChanges\ValueObject;

enum SemverType: string
{
    case MAJOR = 'MAJOR';
    case MINOR = 'MINOR';
    case PATCH = 'PATCH';
    case UNKNOWN = 'UNKNOWN';

    public function label(): string
    {
        return $this->value;
    }

    public function termwindBadgeClass(): string
    {
        return match ($this) {
            self::MAJOR => 'bg-red-600 text-white font-bold px-1',
            self::MINOR => 'bg-amber-500 text-black font-bold px-1',
            self::PATCH => 'bg-emerald-600 text-white font-bold px-1',
            self::UNKNOWN => 'bg-gray-600 text-white font-bold px-1',
        };
    }

    public function ansiColor(): string
    {
        return match ($this) {
            self::MAJOR => 'red',
            self::MINOR => 'yellow',
            self::PATCH => 'green',
            self::UNKNOWN => 'gray',
        };
    }
}
