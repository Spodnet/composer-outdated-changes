<?php

declare(strict_types=1);

namespace Spodnet\ComposerOutdatedChanges\Service\UrlGenerator;

use function sprintf;

class GithubUrlGenerator implements UrlGeneratorInterface
{
    public function supports(?string $sourceUrl): bool
    {
        if ($sourceUrl === null || $sourceUrl === '') {
            return false;
        }

        return str_contains($sourceUrl, 'github.com');
    }

    /**
     * @return array{owner: string, repo: string}|null
     */
    public function extractOwnerAndRepo(?string $sourceUrl): ?array
    {
        if (! $this->supports($sourceUrl) || $sourceUrl === null) {
            return null;
        }

        // Handle SSH format: git@github.com:owner/repo.git
        if (preg_match('#git@github\.com:([^/]+)/([^/\.]+)(?:\.git)?#', $sourceUrl, $matches)) {
            return [
                'owner' => $matches[1],
                'repo' => $matches[2],
            ];
        }

        // Handle HTTP(S) format: https://github.com/owner/repo(...)
        if (preg_match('#https?://(?:www\.)?github\.com/([^/]+)/([^/\.]+)(?:\.git|/.*)?#', $sourceUrl, $matches)) {
            return [
                'owner' => $matches[1],
                'repo' => $matches[2],
            ];
        }

        return null;
    }

    public function generateCompareUrl(?string $sourceUrl, string $fromVersion, string $toVersion): ?string
    {
        $repoInfo = $this->extractOwnerAndRepo($sourceUrl);
        if ($repoInfo === null) {
            return null;
        }

        return sprintf(
            'https://github.com/%s/%s/compare/%s...%s',
            $repoInfo['owner'],
            $repoInfo['repo'],
            $fromVersion,
            $toVersion
        );
    }

    public function generateReleaseUrl(?string $sourceUrl, string $toVersion): ?string
    {
        $repoInfo = $this->extractOwnerAndRepo($sourceUrl);
        if ($repoInfo === null) {
            return null;
        }

        return sprintf(
            'https://github.com/%s/%s/releases/tag/%s',
            $repoInfo['owner'],
            $repoInfo['repo'],
            $toVersion
        );
    }
}
