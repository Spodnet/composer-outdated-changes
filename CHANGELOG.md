# Release Notes

## [Unreleased](https://github.com/Spodnet/composer-outdated-changes/compare/v0.2.1...main)

## [v0.2.1](https://github.com/Spodnet/composer-outdated-changes/compare/v0.2.0...v0.2.1) - 2026-09-22

### Fixed

- Added `phpunit.xml` configuration for Pest and PHPUnit test suite execution in CI.
- Updated GitHub Actions Quality Control workflow matrix to run on PHP 8.4+.
- Added `.phpunit.cache/` to `.gitignore`.

## [v0.2.0](https://github.com/Spodnet/composer-outdated-changes/compare/v0.1.0...v0.2.0) - 2026-09-22

### Added

- Interactive URL and markdown link parsing: raw URLs and markdown links (`[#123](url)`) in release notes are converted to clickable terminal hyperlinks (Cmd+Click).
- Placed `Release:` URL on a dedicated line directly underneath `Diff:`.
- Display full release notes without 4-line truncation.
- Automated `CHANGELOG.md` update workflow on release via GitHub Actions.

### Changed

- Bumped PHP requirement to PHP 8.4+.
- Upgraded dependencies to latest PHP 8.4-native releases: `pestphp/pest` v5.2, `symfony/console` & `symfony/process` v8.1, `phpstan` 2.1.

## [v0.1.0](https://github.com/Spodnet/composer-outdated-changes/releases/tag/v0.1.0) - 2026-09-22

Initial release of **composer-outdated-changes** (`spodnet/composer-outdated-changes`): inspect outdated Composer dependencies with inline changelogs, SemVer categorization, and compare links before updating.

### Added

- Inspect outdated Composer dependencies before updating.
- SemVer bump classification (`[MAJOR]`, `[MINOR]`, `[PATCH]`).
- Compare and Release URLs with OSC 8 clickable terminal links.
- Inline changelogs and release notes fetching via GitHub REST API with automatic URL and markdown parsing.
- Termwind-powered rich terminal report.
- Zero-install execution via CPX (`cpx spodnet/composer-outdated-changes`), project dev-dependency, or global binary.
