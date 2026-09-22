# 📦 composer-outdated-changes

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spodnet/composer-outdated-changes.svg?style=flat-square)](https://packagist.org/packages/spodnet/composer-outdated-changes)
[![Quality Control Pipeline](https://github.com/Spodnet/composer-outdated-changes/actions/workflows/quality.yml/badge.svg)](https://github.com/Spodnet/composer-outdated-changes/actions/workflows/quality.yml)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.3-blue.svg?style=flat-square)](https://php.net)

Inspect outdated Composer dependencies with **inline release notes**, **SemVer impact categorization** (`[MAJOR]`, `[MINOR]`, `[PATCH]`), and **compare diff links** before updating your project.

Powered by [Termwind](https://github.com/nunomaduro/termwind) for stunning terminal output.

---

## The Problem & The Solution

- **`composer outdated`** tells you *which* packages are outdated, but gives no context on breaking changes or what actually changed.
- **`pyrech/composer-changelogs`** only displays changelogs *after* `composer update` has already run.
- **`composer-outdated-changes`** runs **before** you update:
  1. Inspects outdated packages locally (`composer outdated --format=json`).
  2. Categorizes version bumps according to Semantic Versioning (`MAJOR`, `MINOR`, `PATCH`).
  3. Formats exact repository comparison links (`/compare/v1.0.0...v2.0.0`) for GitHub, GitLab, and Bitbucket.
  4. Automatically pulls inline release notes from GitHub's REST API without requiring an API key.
  5. Renders a Tailwind-styled terminal card report, with optional Markdown and JSON outputs for CI/PR comments.

---

## Terminal Preview

```text
📦 Composer Outdated Changes                      3 package(s) outdated
[ 1 Major ] [ 1 Minor ] [ 1 Patch ]

MAJOR  guzzlehttp/psr7  2.13.1 → 3.1.0  [transitive]
PSR-7 message implementation that also provides common utility methods
Diff: https://github.com/guzzle/psr7/compare/2.13.1...3.1.0 • Release: https://github.com/guzzle/psr7/releases/tag/3.1.0
Release Notes:
  ### Added
  - Add Utils::redactUriForMessage() and Utils::redactUriStringForMessage()
  ### Changed
  - Omit rejected header values and sensitive URI components

MINOR  spodnet/laravel-http-client-replay  v0.1.0 → v0.2.0  [direct]
Creates a replayable log of http requests through the http client in laravel
Diff: https://github.com/Spodnet/laravel-http-client-replay/compare/v0.1.0...v0.2.0 • Release: https://github.com/Spodnet/laravel-http-client-replay/releases/tag/v0.2.0
Release Notes:
  ### Added
  - Auto-expiring cassettes (TTL) support on read with Laravel Cache-aligned APIs:
    - Configure TTL globally, per-driver, or per-URL scope pattern
    - Request-level TTL overrides via Http::withOptions(['replay_ttl' => 3600])
```

---

## Installation

### Option 1: Zero-Install via CPX (Recommended)

Run instantly without modifying your `composer.json`:

```bash
cpx spodnet/composer-outdated-changes
```

### Option 2: As a Dev-Dependency

```bash
composer require --dev spodnet/composer-outdated-changes

# Run binary
./vendor/bin/composer-outdated-changes
```

### Option 3: Global Installation

```bash
composer global require spodnet/composer-outdated-changes

composer-outdated-changes
```

---

## CLI Options & Usage

```bash
# Check direct dependencies (default)
composer-outdated-changes

# Include transitive / sub-dependencies
composer-outdated-changes --all
composer-outdated-changes -a

# Filter by SemVer impact level
composer-outdated-changes --major-only   # Only breaking changes
composer-outdated-changes --minor-only   # Minor feature updates
composer-outdated-changes --patch-only   # Bug fixes & patches

# Target a different directory / repository
composer-outdated-changes --path=/path/to/another-project

# 100% Offline Mode (Instant, skips HTTP release note fetching)
composer-outdated-changes --no-changelog

# Filter by package name
composer-outdated-changes guzzle
composer-outdated-changes --filter=symfony

# Alternative output formats
composer-outdated-changes --format=markdown   # Great for GitHub Actions PR comments
composer-outdated-changes --format=json       # Great for scripts and automation
```

---

## Zero-Network Discovery & API Limits

1. **Repository URLs**: Discovered 100% locally from `composer outdated --format=json` and `vendor/composer/installed.json` without any network calls.
2. **Compare & Release URLs**: Formatted offline via platform-specific generators (`GitHub`, `GitLab`, `Bitbucket`).
3. **Release Notes**: Fetched directly from GitHub's public REST API. **No API key is required** (allows 60 unauthenticated requests/hour). If a `GITHUB_TOKEN` is present in your environment, it is automatically used to lift the limit to 5,000 req/hr.
4. **Offline Guarantee**: When `--no-changelog` is specified, zero external HTTP requests are made.

---

## Quality & Development Standards

Built in adherence to [Spodnet/quality-control](https://github.com/Spodnet/quality-control):
- **PHP 8.3+** with `declare(strict_types=1);`
- **Pint** code formatting (`composer lint`)
- **PHPStan** static analysis at Level 8 (`composer analyse`)
- **Pest PHP** test suite (`composer test`)

```bash
# Run tests
composer test

# Check code formatting
composer lint:check

# Run PHPStan
composer analyse

# Run all checks
composer check
```

---

## License

MIT License. See [LICENSE](LICENSE) for details.
