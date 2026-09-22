<?php

declare(strict_types=1);

namespace Spodnet\ComposerOutdatedChanges\Service\UrlGenerator;

interface UrlGeneratorInterface
{
    public function supports(?string $sourceUrl): bool;

    public function generateCompareUrl(?string $sourceUrl, string $fromVersion, string $toVersion): ?string;

    public function generateReleaseUrl(?string $sourceUrl, string $toVersion): ?string;
}
