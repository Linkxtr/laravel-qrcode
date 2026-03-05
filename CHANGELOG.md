# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0]

### Added

- Telegram data type support.
- WhatsApp data type support.
- CalendarEvent data type support.
- MeCard and vCard data type support.
- Image merge support for WebP, SVG and EPS image formats.
- GD Backend support for PNG and WebP generation (useful when Imagick is not available).

### Changed

- Minimum Laravel version is now 11.0.
- Minimum PHP version is now 8.2.
- Renamed the parameter in `Image::setImageResource` from `$image` to `$gdImage` (breaking change for named arguments).
- Fixed spelling of `outerRed`, `outerGreen`, `outerBlue` in `Generator::eyeColor` (breaking change for named arguments).

### Removed

- Support for Laravel 10.
- Compatibility layer with `simplesoftwareio/simple-qrcode`.
