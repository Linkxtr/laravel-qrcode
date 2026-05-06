# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - Unreleased

### Added

- Static mapping for DataType resolution via `DataTypeResolver::MAP`.
- Safe payload decoding and strict property initialization for Ethereum.
- Deterministic UID generation for Calendar Events.

### Changed

- **Breaking:** `MergerInterface` no longer enforces a `__construct` signature.
- **Breaking:** Fluent methods on the Generator now return a cloned instance (Immutable Builder) to prevent state leakage.
- Unified configuration colors to use single comma-separated strings instead of split RGBA env vars.

## [2.0.0]

### Added

- Telegram data type support.
- WhatsApp data type support.
- CalendarEvent data type support.
- Ethereum data type support.
- MeCard and vCard data type support.
- Image merge support for WebP, SVG and EPS image formats.
- GD Backend support for PNG and WebP generation (useful when Imagick is not available).
- **Artisan CLI Command:** Added `php artisan qr:generate` with interactive prompts and strict option parsing.
- **Blade Component:** Added `<x-qrcode />` for direct, clean view integration.
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
