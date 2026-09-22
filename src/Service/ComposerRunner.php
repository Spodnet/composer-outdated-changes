<?php

declare(strict_types=1);

namespace Spodnet\ComposerOutdatedChanges\Service;

use Spodnet\ComposerOutdatedChanges\Model\OutdatedPackage;
use Symfony\Component\Process\Process;

use function is_array;
use function is_string;
use function sprintf;

class ComposerRunner
{
    public function __construct(
        private readonly ?string $defaultWorkingDirectory = null,
    ) {}

    /**
     * @return list<OutdatedPackage>
     */
    public function getOutdatedPackages(bool $directOnly = true, ?string $workingDirectory = null): array
    {
        $cwd = $workingDirectory ?? $this->defaultWorkingDirectory ?? (string) getcwd();

        $command = ['composer', 'outdated', '--format=json'];
        if ($directOnly) {
            $command[] = '--direct';
        }

        $process = new Process($command, $cwd);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(
                sprintf('Failed to execute composer outdated in [%s]: %s', $cwd, $process->getErrorOutput())
            );
        }

        $output = trim($process->getOutput());
        if ($output === '') {
            return [];
        }

        /** @var mixed $data */
        $data = json_decode($output, true);
        if (! is_array($data) || ! isset($data['installed']) || ! is_array($data['installed'])) {
            return [];
        }

        $packages = [];
        foreach ($data['installed'] as $item) {
            $name = (string) ($item['name'] ?? '');
            $source = isset($item['source']) && is_string($item['source']) ? $item['source'] : null;

            // If source is missing or incomplete, inspect local installed.json
            if ($source === null || $source === '') {
                $source = $this->resolveSourceFromInstalledJson($name, $cwd);
            }

            $packages[] = new OutdatedPackage(
                name: $name,
                currentVersion: (string) ($item['version'] ?? ''),
                latestVersion: (string) ($item['latest'] ?? ''),
                description: (string) ($item['description'] ?? ''),
                status: (string) ($item['latest-status'] ?? ''),
                source: $source,
                homepage: isset($item['homepage']) && is_string($item['homepage']) ? $item['homepage'] : null,
                isDirect: (bool) ($item['direct-dependency'] ?? true),
                abandoned: $item['abandoned'] ?? false,
                releaseDate: isset($item['release-date']) && is_string($item['release-date']) ? $item['release-date'] : null,
                latestReleaseDate: isset($item['latest-release-date']) && is_string($item['latest-release-date']) ? $item['latest-release-date'] : null,
            );
        }

        return $packages;
    }

    /**
     * Resolves repository source from local vendor/composer/installed.json without network calls.
     */
    public function resolveSourceFromInstalledJson(string $packageName, string $workingDir): ?string
    {
        $installedFile = rtrim($workingDir, '/').'/vendor/composer/installed.json';
        if (! file_exists($installedFile)) {
            return null;
        }

        $content = file_get_contents($installedFile);
        if ($content === false) {
            return null;
        }

        /** @var mixed $data */
        $data = json_decode($content, true);
        if (! is_array($data)) {
            return null;
        }

        /** @var list<array<string, mixed>> $packages */
        $packages = isset($data['packages']) && is_array($data['packages']) ? $data['packages'] : $data;

        foreach ($packages as $pkg) {
            if (($pkg['name'] ?? null) === $packageName) {
                if (isset($pkg['source']['url']) && is_string($pkg['source']['url'])) {
                    return $pkg['source']['url'];
                }
                if (isset($pkg['support']['source']) && is_string($pkg['support']['source'])) {
                    return $pkg['support']['source'];
                }
                if (isset($pkg['homepage']) && is_string($pkg['homepage'])) {
                    return $pkg['homepage'];
                }
            }
        }

        return null;
    }
}
