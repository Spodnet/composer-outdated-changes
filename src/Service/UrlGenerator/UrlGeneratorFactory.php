<?php

declare(strict_types=1);

namespace Spodnet\ComposerOutdatedChanges\Service\UrlGenerator;

class UrlGeneratorFactory
{
    /**
     * @var list<UrlGeneratorInterface>
     */
    private readonly array $generators;

    /**
     * @param  list<UrlGeneratorInterface>|null  $generators
     */
    public function __construct(?array $generators = null)
    {
        $this->generators = $generators ?? [
            new GithubUrlGenerator,
            new GitlabUrlGenerator,
            new BitbucketUrlGenerator,
        ];
    }

    public function getGeneratorForUrl(?string $sourceUrl): ?UrlGeneratorInterface
    {
        if ($sourceUrl === null || $sourceUrl === '') {
            return null;
        }

        foreach ($this->generators as $generator) {
            if ($generator->supports($sourceUrl)) {
                return $generator;
            }
        }

        return null;
    }
}
