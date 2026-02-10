# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0]

### Added

- MeCard and vCard data type support.
- Image merge support for WebP and SVG formats.
- GD Backend support for PNG and WebP generation (useful when Imagick is not available).

### Changed

- Minimum Laravel version is now 11.0.
- Minimum PHP version is now 8.2.

### Removed

- Support for Laravel 10.
- Compatibility layer with `simplesoftwareio/simple-qrcode`.
