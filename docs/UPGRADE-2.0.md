# Upgrade Guide to v2.0

This guide provides detailed instructions for upgrading from `linkxtr/laravel-qrcode` v1.x to v2.0, which includes upgrading the underlying `bacon/bacon-qr-code` package from v2.x to v3.x.

## Table of Contents

- [Upgrade Guide to v2.0](#upgrade-guide-to-v20)
  - [Table of Contents](#table-of-contents)
  - [System Requirements](#system-requirements)
  - [Breaking Changes](#breaking-changes)
    - [1. PHP Version Requirement](#1-php-version-requirement)
    - [2. Namespace Changes](#2-namespace-changes)
    - [3. Color System](#3-color-system)
  - [Upgrade Steps](#upgrade-steps)
    - [1. Update Dependencies](#1-update-dependencies)
    - [2. Update Your Code](#2-update-your-code)
  - [Support](#support)
  - [Rollback](#rollback)

## System Requirements

- PHP 8.2 or higher
- Laravel 11.0+
- GD extension (existing requirement)
- Composer 2.0 or higher (recommended)

## Breaking Changes

### 1. PHP Version Requirement

Minimum PHP version has been increased to 8.2 to match `bacon/bacon-qr-code` v3 requirements.

### 2. Namespace Changes

Several classes have been reorganized. The most significant changes are:

- `BaconQrCode\Writer` → `BaconQrCode\Writer\Writer`
- `BaconQrCode\Common\ErrorCorrectionLevel` has been updated with new constants

### 3. Color System

- The color methods (`color()` and `backgroundColor()`) now support an optional alpha channel parameter for transparency
- Color handling has been updated for better type safety
- Alpha channel values use the range 0-127, where 0 is fully opaque and 127 is fully transparent

### 4. Dropped Compatibility

- Compatibility with `simplesoftwareio/simple-qrcode` has been dropped.
- The `QrCode` facade is no longer a drop-in replacement for `simplesoftwareio`. Users migrating from `simplesoftwareio` may need to update their code to match the new API.

## Upgrade Steps

### 1. Update Dependencies

Update your `composer.json` to require the new version:

```bash
composer require linkxtr/laravel-qrcode:^2.0
```

### 2. Update Your Code

#### A. Color Methods

The `color` and `backgroundColor` methods now strictly require integer values (0-255). If you were passing strings or other types, please update them.

```php
// Old (if applicable) or Invalid
QrCode::color('255', '0', '0');

// New
QrCode::color(255, 0, 0);
```

#### B. Handling Return Types

Version 2.0 ensures stricter return types. Ensure your code expects `Linkxtr\LaravelQrCode\Generator` instances when chaining methods, and the correct string/response format when calling `generate()`.

#### C. Review Custom Logic

If you extended any classes from the package, check for namespace changes, specifically around `BaconQrCode` as the underlying library upgraded from v2 to v3.

- `BaconQrCode\Writer` is now `BaconQrCode\Writer\Writer`.

## Support

If you encounter any issues during the upgrade, please:

1. Check the [GitHub issues](https://github.com/linkxtr/laravel-qrcode/issues) for known problems
2. Open a new issue if your problem isn't listed
3. Include your PHP version, Laravel version, and any error messages

## Rollback

If you need to rollback:

```bash
composer require linkxtr/laravel-qrcode:^1.0
```

Remember to clear your configuration cache after rolling back:

```bash
php artisan config:clear
php artisan view:clear
```
