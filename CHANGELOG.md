# Change Log

You can see the changes made via the [commit log](https://github.com/themehybrid/hybrid-log/commits/main) for the latest release.

## [1.0.0-beta.6] - 2026-08-05

### Added

- Pest test suite covering `Logger`, `LogManager`, the context `Repository`, and log configuration parsing.
- Development dependencies on `pestphp/pest`, `alvarodelera/pest-wp-plugin`, `themehybrid/hybrid-tools`, and `themehybrid/hybrid-contracts`, plus a `composer test` script.

### Fixed

- `Hybrid\Log\Context\Repository` called `value()` and `tap()` unqualified, which resolved to the global namespace instead of `Hybrid\Tools`. This made `get()`, `getHidden()`, `pull()`, `pullHidden()`, `remember()`, `rememberHidden()`, `increment()`, and `decrement()` fatal on every call.

### Removed

- Duplicate, unreachable declaration of the `logs()` helper in `functions-helpers.php`. Its return type has been moved onto the remaining declaration.

### Changed

- Corrected the `psr/log-implementation` constraint in `provide`, which used a caret range that carries no meaning there.
- Rewrote the README around installation, channel configuration, the `Log` and `Context` APIs, and the helper functions.

## [1.0.0-beta.5] - 2026-06-02

### Changed

- sync with https://github.com/illuminate/log/releases/tag/v12.58.0
- sync with https://github.com/illuminate/support/releases/tag/v12.58.0
- sync with https://github.com/laravel/framework/releases/tag/v12.58.0
- Update copyright date
- Requires PHP 8.2 as minimum version

## [1.0.0-beta.4] - 2024-08-21

### Changed

- Refactor: Ensure LogManager compatibility with Monolog v2 and adjust code for LogRecord array support.

## [1.0.0-beta.3] - 2024-08-02

### Changed

- sync with https://github.com/illuminate/log/releases/tag/v11.18.1
- sync with https://github.com/illuminate/support/releases/tag/v11.18.1
- sync with https://github.com/laravel/framework/releases/tag/v11.18.1
- Add composer sort-packages configuration
- Lint composer.json
- Lint php
- Update copyright date
- Added: Context support to the log package, enabling better contextual logging within the applications.

## [1.0.0-beta.2] - 2024-06-24

### Changed

- Fixed readme typo
- Instead of suggest, require `monolog/monolog`, `psr/log`
- Add few log helper functions
- cleanup chore

## [1.0.0-beta.1] - 2023-09-22

### Added

- Launch.  Everything's new!
