<?php

declare(strict_types=1);

use Spodnet\ComposerOutdatedChanges\Model\OutdatedPackage;
use Spodnet\ComposerOutdatedChanges\Service\ChangelogResolver;

test('it returns null if source is not github', function () {
    $resolver = new ChangelogResolver;
    $pkg = new OutdatedPackage(
        name: 'foo/bar',
        currentVersion: '1.0.0',
        latestVersion: '1.1.0',
        source: 'https://gitlab.com/foo/bar'
    );

    expect($resolver->fetchReleaseNotes($pkg))->toBeNull();
});

test('it extracts release body from github api response', function () {
    $fetcher = function (string $url): ?string {
        if (str_contains($url, 'tags/v1.1.0')) {
            return json_encode([
                'tag_name' => 'v1.1.0',
                'body' => '### Fixed\n- Fixed important bug',
            ], JSON_THROW_ON_ERROR);
        }

        return null;
    };

    $resolver = new ChangelogResolver(httpFetcher: $fetcher);
    $pkg = new OutdatedPackage(
        name: 'foo/bar',
        currentVersion: '1.0.0',
        latestVersion: 'v1.1.0',
        source: 'https://github.com/foo/bar'
    );

    $notes = $resolver->fetchReleaseNotes($pkg);
    expect($notes)->toBe('### Fixed\n- Fixed important bug');
});

test('it falls back to alternating v prefix if initial tag not found', function () {
    $fetcher = function (string $url): ?string {
        // Only responds to 'v1.1.0', but requested version is '1.1.0'
        if (str_contains($url, 'tags/v1.1.0')) {
            return json_encode([
                'tag_name' => 'v1.1.0',
                'body' => 'Found via v prefix fallback',
            ], JSON_THROW_ON_ERROR);
        }

        return null;
    };

    $resolver = new ChangelogResolver(httpFetcher: $fetcher);
    $pkg = new OutdatedPackage(
        name: 'foo/bar',
        currentVersion: '1.0.0',
        latestVersion: '1.1.0',
        source: 'https://github.com/foo/bar'
    );

    $notes = $resolver->fetchReleaseNotes($pkg);
    expect($notes)->toBe('Found via v prefix fallback');
});
