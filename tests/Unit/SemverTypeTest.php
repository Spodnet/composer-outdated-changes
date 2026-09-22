<?php

declare(strict_types=1);

use Spodnet\ComposerOutdatedChanges\ValueObject\SemverType;

test('it provides correct labels for semver types', function () {
    expect(SemverType::MAJOR->label())->toBe('MAJOR')
        ->and(SemverType::MINOR->label())->toBe('MINOR')
        ->and(SemverType::PATCH->label())->toBe('PATCH')
        ->and(SemverType::UNKNOWN->label())->toBe('UNKNOWN');
});

test('it provides termwind badge classes for each semver type', function () {
    expect(SemverType::MAJOR->termwindBadgeClass())->toContain('bg-red')
        ->and(SemverType::MINOR->termwindBadgeClass())->toContain('bg-amber')
        ->and(SemverType::PATCH->termwindBadgeClass())->toContain('bg-emerald')
        ->and(SemverType::UNKNOWN->termwindBadgeClass())->toContain('bg-gray');
});

test('it provides ansi colors for terminal formatting', function () {
    expect(SemverType::MAJOR->ansiColor())->toBe('red')
        ->and(SemverType::MINOR->ansiColor())->toBe('yellow')
        ->and(SemverType::PATCH->ansiColor())->toBe('green')
        ->and(SemverType::UNKNOWN->ansiColor())->toBe('gray');
});
