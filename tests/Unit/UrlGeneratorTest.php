<?php

declare(strict_types=1);

use Spodnet\ComposerOutdatedChanges\Service\UrlGenerator\BitbucketUrlGenerator;
use Spodnet\ComposerOutdatedChanges\Service\UrlGenerator\GithubUrlGenerator;
use Spodnet\ComposerOutdatedChanges\Service\UrlGenerator\GitlabUrlGenerator;
use Spodnet\ComposerOutdatedChanges\Service\UrlGenerator\UrlGeneratorFactory;

test('it formats github compare and release urls properly', function () {
    $generator = new GithubUrlGenerator;

    expect($generator->supports('https://github.com/sebastianbergmann/phpunit/tree/13.3.3'))->toBeTrue()
        ->and($generator->supports('git@github.com:Spodnet/laravel-http-client-replay.git'))->toBeTrue()
        ->and($generator->supports('https://gitlab.com/vendor/pkg'))->toBeFalse();

    $compare = $generator->generateCompareUrl(
        'https://github.com/Spodnet/laravel-http-client-replay/tree/v0.1.0',
        'v0.1.0',
        'v0.2.0'
    );
    expect($compare)->toBe('https://github.com/Spodnet/laravel-http-client-replay/compare/v0.1.0...v0.2.0');

    $release = $generator->generateReleaseUrl(
        'https://github.com/Spodnet/laravel-http-client-replay.git',
        'v0.2.0'
    );
    expect($release)->toBe('https://github.com/Spodnet/laravel-http-client-replay/releases/tag/v0.2.0');
});

test('it formats gitlab compare and release urls properly', function () {
    $generator = new GitlabUrlGenerator;

    expect($generator->supports('https://gitlab.com/my-org/my-repo'))->toBeTrue()
        ->and($generator->supports('https://github.com/my-org/my-repo'))->toBeFalse();

    $compare = $generator->generateCompareUrl(
        'https://gitlab.com/my-org/my-repo.git',
        '1.0.0',
        '2.0.0'
    );
    expect($compare)->toBe('https://gitlab.com/my-org/my-repo/-/compare/1.0.0...2.0.0');

    $release = $generator->generateReleaseUrl(
        'https://gitlab.com/my-org/my-repo',
        '2.0.0'
    );
    expect($release)->toBe('https://gitlab.com/my-org/my-repo/-/releases/2.0.0');
});

test('it formats bitbucket compare and release urls properly', function () {
    $generator = new BitbucketUrlGenerator;

    expect($generator->supports('https://bitbucket.org/my-org/my-repo'))->toBeTrue();

    $compare = $generator->generateCompareUrl(
        'https://bitbucket.org/my-org/my-repo.git',
        '1.0.0',
        '1.1.0'
    );
    expect($compare)->toBe('https://bitbucket.org/my-org/my-repo/branches/compare/1.1.0%0D1.0.0');

    $release = $generator->generateReleaseUrl(
        'https://bitbucket.org/my-org/my-repo',
        '1.1.0'
    );
    expect($release)->toBe('https://bitbucket.org/my-org/my-repo/commits/tag/1.1.0');
});

test('factory resolves appropriate generator for source url', function () {
    $factory = new UrlGeneratorFactory;

    expect($factory->getGeneratorForUrl('https://github.com/foo/bar'))->toBeInstanceOf(GithubUrlGenerator::class)
        ->and($factory->getGeneratorForUrl('https://gitlab.com/foo/bar'))->toBeInstanceOf(GitlabUrlGenerator::class)
        ->and($factory->getGeneratorForUrl('https://bitbucket.org/foo/bar'))->toBeInstanceOf(BitbucketUrlGenerator::class)
        ->and($factory->getGeneratorForUrl('https://example.com/custom'))->toBeNull()
        ->and($factory->getGeneratorForUrl(null))->toBeNull();
});
