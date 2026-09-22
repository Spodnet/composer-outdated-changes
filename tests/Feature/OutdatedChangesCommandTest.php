<?php

declare(strict_types=1);

use Mockery\MockInterface;
use Spodnet\ComposerOutdatedChanges\Command\OutdatedChangesCommand;
use Spodnet\ComposerOutdatedChanges\Model\OutdatedPackage;
use Spodnet\ComposerOutdatedChanges\Service\ChangelogResolver;
use Spodnet\ComposerOutdatedChanges\Service\ComposerRunner;
use Spodnet\ComposerOutdatedChanges\View\TerminalRenderer;
use Symfony\Component\Console\Tester\CommandTester;

test('it executes command and filters by major-only', function () {
    /** @var ComposerRunner&MockInterface $runner */
    $runner = Mockery::mock(ComposerRunner::class);
    $runner->shouldReceive('getOutdatedPackages')
        ->once()
        ->andReturn([
            new OutdatedPackage(name: 'major/pkg', currentVersion: '1.0.0', latestVersion: '2.0.0'),
            new OutdatedPackage(name: 'minor/pkg', currentVersion: '1.0.0', latestVersion: '1.1.0'),
        ]);

    /** @var ChangelogResolver&MockInterface $resolver */
    $resolver = Mockery::mock(ChangelogResolver::class);
    $resolver->shouldReceive('fetchReleaseNotes')->andReturn(null);

    $command = new OutdatedChangesCommand(
        composerRunner: $runner,
        changelogResolver: $resolver,
        terminalRenderer: new TerminalRenderer
    );

    $tester = new CommandTester($command);
    $exitCode = $tester->execute([
        '--major-only' => true,
        '--format' => 'json',
    ]);

    expect($exitCode)->toBe(0);
    $output = $tester->getDisplay();
    $data = json_decode($output, true);

    expect($data['packages'])->toHaveCount(1)
        ->and($data['packages'][0]['name'])->toBe('major/pkg')
        ->and($data['packages'][0]['semver'])->toBe('MAJOR');
});

test('it outputs markdown format correctly', function () {
    /** @var ComposerRunner&MockInterface $runner */
    $runner = Mockery::mock(ComposerRunner::class);
    $runner->shouldReceive('getOutdatedPackages')
        ->once()
        ->andReturn([
            new OutdatedPackage(
                name: 'spodnet/replay',
                currentVersion: 'v0.1.0',
                latestVersion: 'v0.2.0',
                source: 'https://github.com/Spodnet/laravel-http-client-replay'
            ),
        ]);

    /** @var ChangelogResolver&MockInterface $resolver */
    $resolver = Mockery::mock(ChangelogResolver::class);
    $resolver->shouldReceive('fetchReleaseNotes')
        ->andReturn('### Added\n- TTL feature');

    $command = new OutdatedChangesCommand(
        composerRunner: $runner,
        changelogResolver: $resolver,
        terminalRenderer: new TerminalRenderer
    );

    $tester = new CommandTester($command);
    $exitCode = $tester->execute([
        '--format' => 'markdown',
    ]);

    expect($exitCode)->toBe(0);
    $output = $tester->getDisplay();
    expect($output)->toContain('# 📦 Outdated Composer Dependencies')
        ->and($output)->toContain('spodnet/replay')
        ->and($output)->toContain('https://github.com/Spodnet/laravel-http-client-replay/compare/v0.1.0...v0.2.0')
        ->and($output)->toContain('### Added');
});

test('it filters by package name argument', function () {
    /** @var ComposerRunner&MockInterface $runner */
    $runner = Mockery::mock(ComposerRunner::class);
    $runner->shouldReceive('getOutdatedPackages')
        ->once()
        ->andReturn([
            new OutdatedPackage(name: 'guzzlehttp/guzzle', currentVersion: '7.0.0', latestVersion: '8.0.0'),
            new OutdatedPackage(name: 'brick/math', currentVersion: '0.19.0', latestVersion: '1.0.0'),
        ]);

    /** @var ChangelogResolver&MockInterface $resolver */
    $resolver = Mockery::mock(ChangelogResolver::class);
    $resolver->shouldReceive('fetchReleaseNotes')->andReturn(null);

    $command = new OutdatedChangesCommand(
        composerRunner: $runner,
        changelogResolver: $resolver,
        terminalRenderer: new TerminalRenderer
    );

    $tester = new CommandTester($command);
    $exitCode = $tester->execute([
        'package' => 'guzzle',
        '--format' => 'json',
    ]);

    expect($exitCode)->toBe(0);
    $data = json_decode($tester->getDisplay(), true);
    expect($data['packages'])->toHaveCount(1)
        ->and($data['packages'][0]['name'])->toBe('guzzlehttp/guzzle');
});
