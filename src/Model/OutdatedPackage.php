<?php

declare(strict_types=1);

namespace Spodnet\ComposerOutdatedChanges\Model;

use Spodnet\ComposerOutdatedChanges\ValueObject\SemverType;

use function is_string;

class OutdatedPackage
{
    public function __construct(
        private readonly string $name,
        private readonly string $currentVersion,
        private readonly string $latestVersion,
        private readonly string $description = '',
        private readonly string $status = '',
        private readonly ?string $source = null,
        private readonly ?string $homepage = null,
        private readonly bool $isDirect = true,
        private readonly bool|string $abandoned = false,
        private readonly ?string $releaseDate = null,
        private readonly ?string $latestReleaseDate = null,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    public function getLatestVersion(): string
    {
        return $this->latestVersion;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getHomepage(): ?string
    {
        return $this->homepage;
    }

    public function isDirect(): bool
    {
        return $this->isDirect;
    }

    public function isAbandoned(): bool
    {
        return $this->abandoned !== false;
    }

    public function getAbandonedReplacement(): ?string
    {
        return is_string($this->abandoned) ? $this->abandoned : null;
    }

    public function getReleaseDate(): ?string
    {
        return $this->releaseDate;
    }

    public function getLatestReleaseDate(): ?string
    {
        return $this->latestReleaseDate;
    }

    public function getSemverType(): SemverType
    {
        $cleanCurrent = ltrim($this->currentVersion, 'vV');
        $cleanLatest = ltrim($this->latestVersion, 'vV');

        // Extract numeric semver components before any -dev or +build suffix
        $curCore = explode('-', explode('+', $cleanCurrent)[0])[0];
        $latCore = explode('-', explode('+', $cleanLatest)[0])[0];

        $curParts = explode('.', $curCore);
        $latParts = explode('.', $latCore);

        if (! is_numeric($curParts[0]) || ! is_numeric($latParts[0])) {
            return SemverType::UNKNOWN;
        }

        $curMajor = (int) $curParts[0];
        $latMajor = (int) $latParts[0];

        if ($curMajor !== $latMajor) {
            return SemverType::MAJOR;
        }

        $curMinor = isset($curParts[1]) && is_numeric($curParts[1]) ? (int) $curParts[1] : 0;
        $latMinor = isset($latParts[1]) && is_numeric($latParts[1]) ? (int) $latParts[1] : 0;

        if ($curMinor !== $latMinor) {
            return SemverType::MINOR;
        }

        $curPatch = isset($curParts[2]) && is_numeric($curParts[2]) ? (int) $curParts[2] : 0;
        $latPatch = isset($latParts[2]) && is_numeric($latParts[2]) ? (int) $latParts[2] : 0;

        if ($curPatch !== $latPatch) {
            return SemverType::PATCH;
        }

        return SemverType::PATCH;
    }
}
