# Release Notes

## [Unreleased](https://github.com/Spodnet/composer-outdated-changes/compare/v0.1.0...main)

## [v0.1.0](https://github.com/Spodnet/composer-outdated-changes/releases/tag/v0.1.0) - 2026-09-22

Initial release of **composer-outdated-changes** (`spodnet/composer-outdated-changes`): inspect outdated Composer dependencies with inline changelogs, SemVer categorization, and compare links before updating.

### Added

- Inspect outdated Composer dependencies before updating.
- SemVer bump classification (`[MAJOR]`, `[MINOR]`, `[PATCH]`).
- Compare and Release URLs with OSC 8 clickable terminal links.
- Inline changelogs and release notes fetching via GitHub REST API with automatic URL and markdown parsing.
- Termwind-powered rich terminal report.
- Zero-install execution via CPX (`cpx spodnet/composer-outdated-changes`), project dev-dependency, or global binary.
