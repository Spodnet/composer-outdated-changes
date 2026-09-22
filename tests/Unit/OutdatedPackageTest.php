<?php

declare(strict_types=1);

use Spodnet\ComposerOutdatedChanges\Model\OutdatedPackage;
use Spodnet\ComposerOutdatedChanges\ValueObject\SemverType;

test('it correctly detects major version bumps', function () {
    $pkg1 = new OutdatedPackage(name: 'foo/bar', currentVersion: '1.2.3', latestVersion: '2.0.0');
    expect($pkg1->getSemverType())->toBe(SemverType::MAJOR);

    $pkg2 = new OutdatedPackage(name: 'brick/math', currentVersion: '0.19.1', latestVersion: '1.0.0');
    expect($pkg2->getSemverType())->toBe(SemverType::MAJOR);

    $pkg3 = new OutdatedPackage(name: 'guzzle/guzzle', currentVersion: 'v7.15.5', latestVersion: 'v8.0.0');
    expect($pkg3->getSemverType())->toBe(SemverType::MAJOR);
});

test('it correctly detects minor version bumps', function () {
    $pkg1 = new OutdatedPackage(name: 'foo/bar', currentVersion: '1.2.3', latestVersion: '1.3.0');
    expect($pkg1->getSemverType())->toBe(SemverType::MINOR);

    $pkg2 = new OutdatedPackage(name: 'spodnet/replay', currentVersion: 'v0.1.0', latestVersion: 'v0.2.0');
    expect($pkg2->getSemverType())->toBe(SemverType::MINOR);
});

test('it correctly detects patch version bumps', function () {
    $pkg1 = new OutdatedPackage(name: 'phpunit/phpunit', currentVersion: '13.3.3', latestVersion: '13.3.4');
    expect($pkg1->getSemverType())->toBe(SemverType::PATCH);

    $pkg2 = new OutdatedPackage(name: 'foo/bar', currentVersion: 'v2.1.0', latestVersion: 'v2.1.1');
    expect($pkg2->getSemverType())->toBe(SemverType::PATCH);
});

test('it handles dev versions as unknown', function () {
    $pkg = new OutdatedPackage(name: 'foo/bar', currentVersion: 'dev-master', latestVersion: '1.0.0');
    expect($pkg->getSemverType())->toBe(SemverType::UNKNOWN);
});

test('it correctly exposes package properties and abandonment', function () {
    $pkg = new OutdatedPackage(
        name: 'old/lib',
        currentVersion: '1.0.0',
        latestVersion: '1.1.0',
        description: 'Old library',
        status: 'update-possible',
        source: 'https://github.com/old/lib',
        homepage: 'https://example.com',
        isDirect: true,
        abandoned: 'new/lib',
        releaseDate: '2024-01-01',
        latestReleaseDate: '2024-02-01',
    );

    expect($pkg->getName())->toBe('old/lib')
        ->and($pkg->getCurrentVersion())->toBe('1.0.0')
        ->and($pkg->getLatestVersion())->toBe('1.1.0')
        ->and($pkg->getDescription())->toBe('Old library')
        ->and($pkg->getStatus())->toBe('update-possible')
        ->and($pkg->getSource())->toBe('https://github.com/old/lib')
        ->and($pkg->getHomepage())->toBe('https://example.com')
        ->and($pkg->isDirect())->toBeTrue()
        ->and($pkg->isAbandoned())->toBeTrue()
        ->and($pkg->getAbandonedReplacement())->toBe('new/lib')
        ->and($pkg->getReleaseDate())->toBe('2024-01-01')
        ->and($pkg->getLatestReleaseDate())->toBe('2024-02-01');
});
