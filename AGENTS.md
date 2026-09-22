# AGENTS.md - Developer & Agent Quality & Engineering Standards

Welcome! This document outlines engineering practices, architectural guidelines, quality benchmarks, and verification workflows for `spodnet/composer-outdated-changes`.

---

## 1. Project Standards

### Strict Typing & Standards
- PHP 8.4+ with `declare(strict_types=1);` in every PHP file.
- Clean object-oriented architecture adhering to PSR-12 / PER-CS.
- Use explicit type-hints on all method arguments, return types, and properties.

### Code Quality & Static Analysis
- **Linting & Formatting**: Laravel Pint (`composer lint`, `composer lint:check`).
- **Static Analysis**: PHPStan at Level 8 (`composer analyse`).
- **Testing**: Pest PHP (`composer test`).
- **Zero Debug Artifacts**: Never leave `dd()`, `dump()`, `ray()`, or `var_dump()` in committed code.

### Dependency Principles
- Keep runtime dependencies minimal: `symfony/console`, `symfony/process`, and `nunomaduro/termwind`.
- Resolve repository and version info locally from Composer metadata (`composer outdated`, `vendor/composer/installed.json`) before falling back to external HTTP calls.
- Support offline / fast execution via `--no-changelog`.

---

## 2. Standard Commands

```bash
# Run tests
composer test

# Format code
composer lint

# Check code formatting without modifying
composer lint:check

# Run PHPStan static analysis
composer analyse

# Run full suite
composer check
```
