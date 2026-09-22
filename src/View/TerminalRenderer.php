<?php

declare(strict_types=1);

namespace Spodnet\ComposerOutdatedChanges\View;

use Spodnet\ComposerOutdatedChanges\Model\OutdatedPackage;
use Spodnet\ComposerOutdatedChanges\Service\UrlGenerator\UrlGeneratorFactory;
use Spodnet\ComposerOutdatedChanges\ValueObject\SemverType;

use function count;
use function sprintf;
use function Termwind\render;

class TerminalRenderer
{
    public function __construct(
        private readonly UrlGeneratorFactory $urlGeneratorFactory = new UrlGeneratorFactory,
    ) {}

    /**
     * @param  list<OutdatedPackage>  $packages
     * @param  array<string, string|null>  $releaseNotesMap
     */
    public function renderTerminal(array $packages, array $releaseNotesMap = [], bool $skipChangelog = false): void
    {
        if (empty($packages)) {
            render(<<<'HTML'
                <div class="my-1 p-1 bg-emerald-800 text-white font-bold">
                    ✨ All dependencies are up to date!
                </div>
            HTML);

            return;
        }

        $majorCount = 0;
        $minorCount = 0;
        $patchCount = 0;

        foreach ($packages as $pkg) {
            match ($pkg->getSemverType()) {
                SemverType::MAJOR => $majorCount++,
                SemverType::MINOR => $minorCount++,
                SemverType::PATCH => $patchCount++,
                SemverType::UNKNOWN => null,
            };
        }

        $total = count($packages);

        render(<<<HTML
            <div class="mt-1">
                <div class="px-1 py-0.5 bg-slate-800 text-white font-bold flex justify-between">
                    <span>📦 Composer Outdated Changes</span>
                    <span class="text-slate-400 font-normal">{$total} package(s) outdated</span>
                </div>
                <div class="my-1 flex space-x-2">
                    <span class="px-1.5 py-0.5 bg-red-700 text-white font-bold">{$majorCount} Major</span>
                    <span class="px-1.5 py-0.5 bg-amber-600 text-black font-bold">{$minorCount} Minor</span>
                    <span class="px-1.5 py-0.5 bg-emerald-700 text-white font-bold">{$patchCount} Patch</span>
                </div>
            </div>
        HTML);

        foreach ($packages as $index => $pkg) {
            $semver = $pkg->getSemverType();
            $badgeClass = $semver->termwindBadgeClass();
            $generator = $this->urlGeneratorFactory->getGeneratorForUrl($pkg->getSource());

            $compareUrl = $generator?->generateCompareUrl($pkg->getSource(), $pkg->getCurrentVersion(), $pkg->getLatestVersion());
            $releaseUrl = $generator?->generateReleaseUrl($pkg->getSource(), $pkg->getLatestVersion());
            $notes = $releaseNotesMap[$pkg->getName()] ?? null;

            $depType = $pkg->isDirect()
                ? '<span class="text-slate-400 font-normal">[direct]</span>'
                : '<span class="text-slate-500 font-normal">[transitive]</span>';

            $abandonedBadge = '';
            if ($pkg->isAbandoned()) {
                $replacement = $pkg->getAbandonedReplacement();
                $text = $replacement ? "ABANDONED: use {$replacement}" : 'ABANDONED';
                $abandonedBadge = sprintf('<span class="bg-red-800 text-red-100 px-1 font-bold mr-1">%s</span>', htmlspecialchars($text, ENT_QUOTES));
            }

            $descriptionHtml = '';
            if ($pkg->getDescription() !== '') {
                $descriptionHtml = sprintf('<div class="text-slate-400 italic mb-1">%s</div>', htmlspecialchars($pkg->getDescription(), ENT_QUOTES));
            }

            $linksHtml = '';
            if ($compareUrl !== null || $releaseUrl !== null) {
                $lines = [];
                if ($compareUrl !== null) {
                    $lines[] = sprintf('<div>Diff: <a href="%s" class="text-cyan-400 underline font-bold">%s</a></div>', $compareUrl, $compareUrl);
                }
                if ($releaseUrl !== null) {
                    $lines[] = sprintf('<div>Release: <a href="%s" class="text-blue-400 underline font-bold">%s</a></div>', $releaseUrl, $releaseUrl);
                }
                $linksHtml = sprintf('<div class="my-1 text-slate-300">%s</div>', implode('', $lines));
            }

            $changelogHtml = '';
            if (! $skipChangelog && $notes !== null && trim($notes) !== '') {
                $changelogHtml = $this->renderNotesSummary($notes);
            }

            $pkgName = htmlspecialchars($pkg->getName(), ENT_QUOTES);
            $curVer = htmlspecialchars($pkg->getCurrentVersion(), ENT_QUOTES);
            $latVer = htmlspecialchars($pkg->getLatestVersion(), ENT_QUOTES);

            if ($index > 0) {
                render('<hr class="text-slate-800 my-1"/>');
            }

            render(<<<HTML
                <div class="my-1">
                    <div class="flex space-x-2 mb-0.5">
                        <span class="{$badgeClass}">{$semver->label()}</span>
                        <span class="font-bold text-white">{$pkgName}</span>
                        <span class="text-slate-400">{$curVer}</span>
                        <span class="text-slate-600 font-bold">→</span>
                        <span class="text-emerald-400 font-bold">{$latVer}</span>
                        {$depType}
                        {$abandonedBadge}
                    </div>
                    {$descriptionHtml}
                    {$linksHtml}
                    {$changelogHtml}
                </div>
            HTML);
        }
    }

    private function renderNotesSummary(string $rawNotes): string
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($rawNotes)) ?: [];
        $renderedLines = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '<!--')) {
                continue;
            }

            // Headers
            if (preg_match('/^#{1,4}\s+(.+)$/', $line, $matches)) {
                $headerText = htmlspecialchars($matches[1], ENT_QUOTES);
                $renderedLines[] = sprintf('<div class="font-bold text-white mt-1 mb-0.5">%s</div>', $headerText);

                continue;
            }

            // Bullet points
            if (preg_match('/^[\*\-]\s+(.+)$/', $line, $matches)) {
                $lineContent = $this->parseLinksAndFormatting($matches[1]);
                $renderedLines[] = sprintf('<div class="text-slate-300 ml-1">&bull; %s</div>', $lineContent);

                continue;
            }

            $lineContent = $this->parseLinksAndFormatting($line);
            $renderedLines[] = sprintf('<div class="text-slate-300">%s</div>', $lineContent);
        }

        if (empty($renderedLines)) {
            return '';
        }

        return sprintf(
            '<div class="my-1 pl-2"><div class="font-bold text-slate-400 mb-0.5">Release Notes:</div>%s</div>',
            implode('', $renderedLines)
        );
    }

    private function parseLinksAndFormatting(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES);

        // Convert markdown links [title](url)
        $escaped = (string) preg_replace(
            '/\[([^\]]+)\]\((https?:\/\/[^\s\)\'\"]+)\)/',
            '<a href="$2" class="text-cyan-400 underline font-bold">$1</a>',
            $escaped
        );

        // Convert raw URLs
        $escaped = (string) preg_replace_callback(
            '/(?<!href=")(https?:\/\/[^\s\)\'\"<]+)/',
            static function (array $m): string {
                return sprintf('<a href="%s" class="text-cyan-400 underline font-bold">%s</a>', $m[1], $m[1]);
            },
            $escaped
        );

        // Convert inline code `code`
        $escaped = (string) preg_replace(
            '/`([^`]+)`/',
            '<span class="text-emerald-400 font-bold">$1</span>',
            $escaped
        );

        return $escaped;
    }

    /**
     * @param  list<OutdatedPackage>  $packages
     * @param  array<string, string|null>  $releaseNotesMap
     * @return array<string, mixed>
     */
    public function renderJson(array $packages, array $releaseNotesMap = []): array
    {
        $data = [
            'summary' => [
                'total' => count($packages),
                'major' => 0,
                'minor' => 0,
                'patch' => 0,
            ],
            'packages' => [],
        ];

        foreach ($packages as $pkg) {
            $semver = $pkg->getSemverType();
            match ($semver) {
                SemverType::MAJOR => $data['summary']['major']++,
                SemverType::MINOR => $data['summary']['minor']++,
                SemverType::PATCH => $data['summary']['patch']++,
                SemverType::UNKNOWN => null,
            };

            $generator = $this->urlGeneratorFactory->getGeneratorForUrl($pkg->getSource());

            $data['packages'][] = [
                'name' => $pkg->getName(),
                'current_version' => $pkg->getCurrentVersion(),
                'latest_version' => $pkg->getLatestVersion(),
                'semver' => $semver->value,
                'direct' => $pkg->isDirect(),
                'abandoned' => $pkg->isAbandoned(),
                'abandoned_replacement' => $pkg->getAbandonedReplacement(),
                'compare_url' => $generator?->generateCompareUrl($pkg->getSource(), $pkg->getCurrentVersion(), $pkg->getLatestVersion()),
                'release_url' => $generator?->generateReleaseUrl($pkg->getSource(), $pkg->getLatestVersion()),
                'release_notes' => $releaseNotesMap[$pkg->getName()] ?? null,
            ];
        }

        return $data;
    }

    /**
     * @param  list<OutdatedPackage>  $packages
     * @param  array<string, string|null>  $releaseNotesMap
     */
    public function renderMarkdown(array $packages, array $releaseNotesMap = [], bool $skipChangelog = false): string
    {
        if (empty($packages)) {
            return "✨ **All dependencies are up to date!**\n";
        }

        $lines = [];
        $lines[] = '# 📦 Outdated Composer Dependencies';
        $lines[] = '';
        $lines[] = '| Package | Current | Latest | Type | Compare |';
        $lines[] = '| :--- | :--- | :--- | :---: | :--- |';

        foreach ($packages as $pkg) {
            $semver = $pkg->getSemverType();
            $generator = $this->urlGeneratorFactory->getGeneratorForUrl($pkg->getSource());
            $compareUrl = $generator?->generateCompareUrl($pkg->getSource(), $pkg->getCurrentVersion(), $pkg->getLatestVersion());
            $compareLink = $compareUrl ? sprintf('[Diff](%s)', $compareUrl) : '-';

            $lines[] = sprintf(
                '| **`%s`** | `%s` | `%s` | `%s` | %s |',
                $pkg->getName(),
                $pkg->getCurrentVersion(),
                $pkg->getLatestVersion(),
                $semver->value,
                $compareLink
            );
        }

        if (! $skipChangelog) {
            $lines[] = '';
            $lines[] = '## 📝 Release Notes';

            foreach ($packages as $pkg) {
                $notes = $releaseNotesMap[$pkg->getName()] ?? null;
                if ($notes !== null && trim($notes) !== '') {
                    $lines[] = '';
                    $lines[] = sprintf('### `%s` (%s)', $pkg->getName(), $pkg->getLatestVersion());
                    $lines[] = '';
                    $lines[] = trim($notes);
                }
            }
        }

        return implode("\n", $lines)."\n";
    }
}
