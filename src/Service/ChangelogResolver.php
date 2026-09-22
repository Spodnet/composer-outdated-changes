<?php

declare(strict_types=1);

namespace Spodnet\ComposerOutdatedChanges\Service;

use Spodnet\ComposerOutdatedChanges\Model\OutdatedPackage;
use Spodnet\ComposerOutdatedChanges\Service\UrlGenerator\GithubUrlGenerator;

use function is_array;
use function is_string;
use function sprintf;

class ChangelogResolver
{
    /**
     * @param  (\Closure(string): (string|null))|null  $httpFetcher
     */
    public function __construct(
        private readonly GithubUrlGenerator $githubUrlGenerator = new GithubUrlGenerator,
        private readonly ?\Closure $httpFetcher = null,
    ) {}

    /**
     * Fetches inline release notes for the target release tag from GitHub without requiring an API key.
     */
    public function fetchReleaseNotes(OutdatedPackage $package): ?string
    {
        $source = $package->getSource();
        if ($source === null || ! $this->githubUrlGenerator->supports($source)) {
            return null;
        }

        $repoInfo = $this->githubUrlGenerator->extractOwnerAndRepo($source);
        if ($repoInfo === null) {
            return null;
        }

        $tag = $package->getLatestVersion();
        $owner = $repoInfo['owner'];
        $repo = $repoInfo['repo'];

        // Try exact tag first
        $notes = $this->fetchGitHubReleaseBody($owner, $repo, $tag);
        if ($notes !== null) {
            return $notes;
        }

        // Try alternating "v" prefix if not found
        if (str_starts_with($tag, 'v') || str_starts_with($tag, 'V')) {
            $altTag = substr($tag, 1);
        } else {
            $altTag = 'v'.$tag;
        }

        return $this->fetchGitHubReleaseBody($owner, $repo, $altTag);
    }

    private function fetchGitHubReleaseBody(string $owner, string $repo, string $tag): ?string
    {
        $url = sprintf('https://api.github.com/repos/%s/%s/releases/tags/%s', $owner, $repo, rawurlencode($tag));

        $json = $this->requestUrl($url);
        if ($json === null) {
            return null;
        }

        /** @var mixed $data */
        $data = json_decode($json, true);
        if (! is_array($data) || ! isset($data['body']) || ! is_string($data['body'])) {
            return null;
        }

        $body = trim($data['body']);

        return $body !== '' ? $body : null;
    }

    private function requestUrl(string $url): ?string
    {
        if ($this->httpFetcher !== null) {
            return ($this->httpFetcher)($url);
        }

        $headers = [
            'User-Agent: composer-outdated-changes',
            'Accept: application/vnd.github.v3+json',
        ];

        // Optional token support from environment
        $token = getenv('GITHUB_TOKEN') ?: getenv('GH_TOKEN');
        if ($token !== false && $token !== '') {
            $headers[] = 'Authorization: Bearer '.$token;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => 3,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return null;
        }

        // Check HTTP response code
        $headers = http_get_last_response_headers();
        if ($headers !== null && isset($headers[0]) && ! str_contains($headers[0], '200')) {
            return null;
        }

        return $response;
    }
}
