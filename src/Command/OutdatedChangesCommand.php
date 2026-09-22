<?php

declare(strict_types=1);

namespace Spodnet\ComposerOutdatedChanges\Command;

use Spodnet\ComposerOutdatedChanges\Service\ChangelogResolver;
use Spodnet\ComposerOutdatedChanges\Service\ComposerRunner;
use Spodnet\ComposerOutdatedChanges\ValueObject\SemverType;
use Spodnet\ComposerOutdatedChanges\View\TerminalRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function is_string;

class OutdatedChangesCommand extends Command
{
    public function __construct(
        private readonly ComposerRunner $composerRunner = new ComposerRunner,
        private readonly ChangelogResolver $changelogResolver = new ChangelogResolver,
        private readonly TerminalRenderer $terminalRenderer = new TerminalRenderer,
    ) {
        parent::__construct('outdated-changes');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Inspect outdated Composer packages with inline changelogs, SemVer tags, and compare links.')
            ->addArgument('package', InputArgument::OPTIONAL, 'Filter by specific package name')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to project directory containing composer.json', (string) getcwd())
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Include transitive sub-dependencies (default: direct only)')
            ->addOption('major-only', 'm', InputOption::VALUE_NONE, 'Only display packages with major breaking version bumps')
            ->addOption('minor-only', null, InputOption::VALUE_NONE, 'Only display packages with minor version bumps')
            ->addOption('patch-only', null, InputOption::VALUE_NONE, 'Only display packages with patch version bumps')
            ->addOption('full', null, InputOption::VALUE_NONE, 'Display full, unabbreviated release notes')
            ->addOption('no-changelog', null, InputOption::VALUE_NONE, 'Skip fetching inline changelog text, output only compare links')
            ->addOption('filter', null, InputOption::VALUE_REQUIRED, 'Filter packages matching substring or pattern')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (term, json, markdown)', 'term');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $targetPath = (string) $input->getOption('path');
        $directOnly = ! (bool) $input->getOption('all');
        $skipChangelog = (bool) $input->getOption('no-changelog');
        $format = strtolower((string) $input->getOption('format'));

        $outdatedPackages = $this->composerRunner->getOutdatedPackages($directOnly, $targetPath);

        // Apply filters
        $filterArg = $input->getArgument('package');
        $filterOpt = $input->getOption('filter');
        $filterQuery = is_string($filterArg) && $filterArg !== '' ? $filterArg : ($filterOpt ? (string) $filterOpt : null);

        if ($filterQuery !== null) {
            $outdatedPackages = array_values(array_filter(
                $outdatedPackages,
                static fn ($pkg): bool => str_contains(strtolower($pkg->getName()), strtolower($filterQuery))
            ));
        }

        if ($input->getOption('major-only')) {
            $outdatedPackages = array_values(array_filter(
                $outdatedPackages,
                static fn ($pkg): bool => $pkg->getSemverType() === SemverType::MAJOR
            ));
        } elseif ($input->getOption('minor-only')) {
            $outdatedPackages = array_values(array_filter(
                $outdatedPackages,
                static fn ($pkg): bool => $pkg->getSemverType() === SemverType::MINOR
            ));
        } elseif ($input->getOption('patch-only')) {
            $outdatedPackages = array_values(array_filter(
                $outdatedPackages,
                static fn ($pkg): bool => $pkg->getSemverType() === SemverType::PATCH
            ));
        }

        // Fetch release notes if not skipped
        /** @var array<string, string|null> $releaseNotesMap */
        $releaseNotesMap = [];
        if (! $skipChangelog && ! empty($outdatedPackages)) {
            foreach ($outdatedPackages as $pkg) {
                $releaseNotesMap[$pkg->getName()] = $this->changelogResolver->fetchReleaseNotes($pkg);
            }
        }

        // Output formatting
        if ($format === 'json') {
            $data = $this->terminalRenderer->renderJson($outdatedPackages, $releaseNotesMap);
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $output->writeln($json !== false ? $json : '{}');

            return Command::SUCCESS;
        }

        if ($format === 'markdown') {
            $md = $this->terminalRenderer->renderMarkdown($outdatedPackages, $releaseNotesMap, $skipChangelog);
            $output->write($md);

            return Command::SUCCESS;
        }

        $this->terminalRenderer->renderTerminal($outdatedPackages, $releaseNotesMap, $skipChangelog);

        return Command::SUCCESS;
    }
}
