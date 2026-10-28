# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.4.0] - 2026-10-27

### Added

- Cache store support allowing QR code cache entries to use a dedicated cache store via `cache_store` configuration option.
- `ETag` and `Cache-Control` HTTP headers in `QrCodeResult::toResponse()` for browser and CDN caching with conditional revalidation.
- Enriched color configuration exception messages providing actionable formatting guidance for invalid color strings and arrays.
- `QrCodeComponent` Blade component automatically inherits default `size`, `margin`, and `format` settings from `config/qrcode.php`.

### Changed

- Standardized `cache_ttl` default from minutes to seconds (`86400` seconds / 24 hours).
- Standardized cache key prefix with namespace `qrcode_v3_` to avoid key collisions.
- Automatically strip `<?xml ...?>` declaration from inline HTML SVGs in `QrCodeComponent` and `QrCodeResult::toHtml()` for standards-compliant HTML5 output.

### Fixed

- Robust type coercion and filtering for cache environment configuration values (`QR_CODE_CACHE_ENABLED` and `QR_CODE_CACHE_TTL`).

## [3.3.0] - 2026-10-19

### Added

- Built-in caching support via `cache()`, `withoutCache()`, and `CacheManager` with environment string coercion (#52).
- `QrCodeResult::saveToFile()` method for streamlined file persistence (#52).
- Official support for Laravel 12 and Laravel 13 (`^12.0 || ^13.0`) (#52).
- Serialization support for `Config` DTO across cached jobs and store drivers.
- Support for scientific notation and micro-float values (up to 18 decimal places) in `Ethereum` payloads.
- Deterministic and sanitized UID generation for `CalendarEvent` payloads.

### Changed

- Bumped CI dependencies (`actions/checkout@v7.0.1`, `shivammathur/setup-php@v2.40.0`, `actions/upload-artifact@v7.0.2`).
- Achieved 100% mutation testing coverage via Pest mutation testing.

## [3.2.0] - 2026-07-13

### Added

- Automated Dependabot configuration for GitHub Actions workflows (#47).

### Changed

- Refactored SVG manipulation and XML parsing in `SvgMerger` for enhanced reliability and performance (#48).
- Updated CI workflows with action pinning and automated code coverage reporting.
- Bumped `actions/cache` from 6.0.0 to 6.1.0 (#51).

## [3.1.0] - 2026-06-08

### Changed

- Refactored generator and renderer architecture based on v3 production feedback (#46).

### Fixed

- Hardened GitHub Actions workflow permissions and addressed code scanning alerts (#45).

## [3.0.0] - 2026-06-04

### Added

- Complete re-architecture of the rendering engine powered by `bacon/bacon-qr-code` v3 with unified renderer and merger factories (#44).
- Dynamic data type resolution via `DataTypeResolver` using filesystem discovery with in-memory static caching.
- Safe payload decoding and strict property initialization for Ethereum data type.
- Support for Laravel 12 and Laravel 13 (`^12.0 || ^13.0`).

### Changed

- Minimum Laravel version raised to 12.0.
- **Breaking:** Fluent methods on `Generator` now return a cloned instance (Immutable Builder) to prevent state leakage across invocations.
- **Breaking:** `MergerInterface` contract updated to remove mandatory `__construct` signature requirements.
- Unified configuration colors to use single comma-separated strings instead of split RGBA environment variables.

## [2.5.1] - 2026-05-25

### Fixed

- Allow vector QR formats (`svg`, `eps`) to be generated without requiring raster image extensions (`ext-gd`, `ext-imagick`) (#41).

## [2.5.0] - 2026-04-05

### Added

- Interactive Artisan CLI command `php artisan qr:generate` with strict option parsing and terminal previews (#38).
- Macroable `Generator` and `QrCode` facade allowing custom user macros (#37).
- Enhanced SVG accessibility on `<x-qr-code>` Blade component with automatic `role="img"`, `aria-label`, and `<title>` attributes (#36).
- Support for arbitrary Blade attributes and closure-based dynamic rendering in `<x-qr-code>` (#35).
- Advanced styling with custom module styles (`square`, `dot`, `round`), eye styles (`square`, `circle`, `pointy`), and gradients (`vertical`, `horizontal`, `diagonal`, `inverse_diagonal`, `radial`) (#34).
- CMYK and Gray color models alongside standard RGB/RGBA (#34).

## [2.4.0] - 2026-03-28

### Fixed

- `Generator` strictly respects default configuration values defined in `config/qrcode.php` (#30).

### Documentation

- Prepared v2.4.0 release notes and updated the roadmap (#33).

## [2.3.0] - 2026-03-18

### Added

- Initial support for Laravel 13 (`^13.0`) (#32).

### Changed

- Enforced Node 24 for CI environments.

## [2.2.0] - 2026-03-12

### Added

- Native Blade component `<x-qr-code />` for direct view integration (#28, #29).
- IDE auto-completion `@method` annotations on `Generator` and `QrCode` facade (#27).
- `ImagickMerger` for high-fidelity raster image compositing (#25).
- Dynamic Wi-Fi encryption parameter support (#24).
- Ethereum payment QR code support via `QrCode::Ethereum()` (#22).

### Changed

- Performance optimization in `Generator::__call` by removing `ReflectionClass` in favor of optimized parameter resolution (#26).
- Adopted automated Rector refactoring rules.

## [2.1.1] - 2026-03-02

### Changed

- Refactored package internal structure and namespace organization (#20).
- Updated README documentation and badges.

## [2.1.0] - 2026-02-12

### Added

- Telegram data type support via `QrCode::Telegram()` (#13).
- WhatsApp data type support via `QrCode::WhatsApp()` (#12).
- MeCard data type support via `QrCode::MeCard()` (#11).
- Calendar event data type support via `QrCode::CalendarEvent()` (#8).
- WebP format support for QR code output and image merging (#7, #8, #9).
- Image merge support for SVG and WebP images (#9, #10).
- Proper alpha transparency handling for GD backend image backgrounds.
- End-date validation ensuring `CalendarEvent` end date occurs after start date.

### Fixed

- GD backend renderer fixes and clean error handling (#19).
- Validation and null parameter exclusion for BTC, Email, Geo, and WiFi data types.
- Format `CalendarEvent` DTSTART and DTEND timestamps as UTC with `Z` suffix.

## [2.0.0] - 2025-12-30

### Added

- Ethereum data type support.
- GD Backend support for PNG and WebP generation (useful when Imagick is not available).
- **Artisan CLI Command:** Added `php artisan qr:generate` with interactive prompts and strict option parsing.
- **Strict Data Types:** All payloads (`PhoneNumber`, `WiFi`, `Email`, etc.) now strictly validate input formatting.
- **Macroable Facade:** Developers can now register custom macros on the `QrCode` facade.

### Changed

- Minimum Laravel version is now 11.0.
- Minimum PHP version is now 8.2.
- Renamed the parameter in `Image::setImageResource` from `$image` to `$gdImage` (breaking change for named arguments).
- Fixed spelling of `outerRed`, `outerGreen`, `outerBlue` in `Generator::eyeColor` (breaking change for named arguments).

### Removed

- Support for Laravel 10.
- Compatibility layer with `simplesoftwareio/simple-qrcode`.

## [1.0.1] - 2025-12-30

### Fixed

- Resolved Generator class binding in Laravel service container.

## [1.0.0] - 2025-10-06

### Added

- Initial release of `linkxtr/laravel-qrcode`.
- Fluent `QrCode` facade for generating SVG, PNG, and EPS QR codes.
- Simple QrCode compatibility layer for smooth transitions.

[3.4.0]: https://github.com/Linkxtr/laravel-qrcode/compare/v3.3.0...v3.4.0
[3.3.0]: https://github.com/Linkxtr/laravel-qrcode/compare/v3.2.0...v3.3.0
[3.2.0]: https://github.com/Linkxtr/laravel-qrcode/compare/v3.1.0...v3.2.0
[3.1.0]: https://github.com/Linkxtr/laravel-qrcode/compare/v3.0.0...v3.1.0
[3.0.0]: https://github.com/Linkxtr/laravel-qrcode/compare/v2.5.1...v3.0.0
[2.5.1]: https://github.com/Linkxtr/laravel-qrcode/compare/v2.5.0...v2.5.1
[2.5.0]: https://github.com/Linkxtr/laravel-qrcode/compare/v2.4.0...v2.5.0
[2.4.0]: https://github.com/Linkxtr/laravel-qrcode/compare/v2.3.0...v2.4.0
[2.3.0]: https://github.com/Linkxtr/laravel-qrcode/compare/v2.2.0...v2.3.0
[2.2.0]: https://github.com/Linkxtr/laravel-qrcode/compare/v2.1.1...v2.2.0
[2.1.1]: https://github.com/Linkxtr/laravel-qrcode/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/Linkxtr/laravel-qrcode/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/Linkxtr/laravel-qrcode/compare/v1.0.1...v2.0.0
[1.0.1]: https://github.com/Linkxtr/laravel-qrcode/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/Linkxtr/laravel-qrcode/releases/tag/v1.0.0
