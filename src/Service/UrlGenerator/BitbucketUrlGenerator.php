<?php

declare(strict_types=1);

namespace Spodnet\ComposerOutdatedChanges\Service\UrlGenerator;

use function sprintf;

class BitbucketUrlGenerator implements UrlGeneratorInterface
{
    public function supports(?string $sourceUrl): bool
    {
        if ($sourceUrl === null || $sourceUrl === '') {
            return false;
        }

        return str_contains($sourceUrl, 'bitbucket.org');
    }

    /**
     * @return array{owner: string, repo: string}|null
     */
    public function extractOwnerAndRepo(?string $sourceUrl): ?array
    {
        if (! $this->supports($sourceUrl) || $sourceUrl === null) {
            return null;
        }

        // Handle SSH format: git@bitbucket.org:owner/repo.git
        if (preg_match('#git@bitbucket\.org:([^/]+)/([^/\.]+)(?:\.git)?#', $sourceUrl, $matches)) {
            return [
                'owner' => $matches[1],
                'repo' => $matches[2],
            ];
        }

        // Handle HTTP(S) format: https://bitbucket.org/owner/repo(...)
        if (preg_match('#https?://(?:www\.)?bitbucket\.org/([^/]+)/([^/\.]+)(?:\.git|/.*)?#', $sourceUrl, $matches)) {
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
            'https://bitbucket.org/%s/%s/branches/compare/%s%%0D%s',
            $repoInfo['owner'],
            $repoInfo['repo'],
            $toVersion,
            $fromVersion
        );
    }

    public function generateReleaseUrl(?string $sourceUrl, string $toVersion): ?string
    {
        $repoInfo = $this->extractOwnerAndRepo($sourceUrl);
        if ($repoInfo === null) {
            return null;
        }

        return sprintf(
            'https://bitbucket.org/%s/%s/commits/tag/%s',
            $repoInfo['owner'],
            $repoInfo['repo'],
            $toVersion
        );
    }
}
