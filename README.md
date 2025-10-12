# Laravel QR Code

[![Latest Version on Packagist](https://img.shields.io/packagist/v/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/linkxtr/laravel-qrcode/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/linkxtr/laravel-qrcode/actions?query=workflow%3Atests+branch%3Amain)
[![Compatibility Test](https://github.com/linkxtr/laravel-qrcode/actions/workflows/compatibility-test.yml/badge.svg)](https://github.com/linkxtr/laravel-qrcode/actions/workflows/compatibility-test.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![License](https://img.shields.io/packagist/l/linkxtr/laravel-qrcode?style=flat-square)](LICENSE.md)

A clean, modern, and easy-to-use QR code generator for Laravel applications. This package provides a simple and intuitive API for generating QR codes in various formats with support for Laravel 10, 11, and 12, built on top of the reliable [Bacon/BaconQrCode](https://github.com/Bacon/BaconQrCode) library.

## 🚀 Simple QrCode Compatibility

This package is designed to be a drop-in replacement for `simplesoftwareio/simple-qrcode` with the following benefits:

- ✅ **Fully compatible** with existing Simple QrCode method signatures
- 🚀 **Faster performance** with modern PHP 8.2+ optimizations
- 🛠 **Strict type safety** with PHP 8.2+ features
- 📦 **Smaller footprint** with minimal dependencies

### Migration Guide

If you're migrating from `simplesoftwareio/simple-qrcode`, simply replace the namespace in your code:

```diff
- use SimpleSoftwareIO\QrCode\Facades\QrCode;
+ use Linkxtr\QrCode\Facades\QrCode;
```

All your existing QR code generation code should work without any changes. We maintain compatibility with all the commonly used methods from Simple QrCode.

## Features

- 🔥 **Modern PHP 8.2+** with strict types and modern syntax
- 🎨 **Multiple output formats**: PNG, SVG, EPS, and more
- 🖌️ **Customizable appearance**: Colors, size, margins, and more
- 🔄 **Built-in caching** for improved performance
- 🔒 **Support for different encodings**: UTF-8, ISO-8859-1, etc.
- 📱 **Responsive by default** with SVG output
- 🧪 **100% test coverage** with Pest PHP
- 📦 **Laravel 10, 11 & 12** compatibility
- 🔍 **IDE-friendly** with proper type hints
- 🔄 **Simple QrCode compatible** - Drop-in replacement for `simplesoftwareio/simple-qrcode`
- 🚀 **Continuous compatibility testing** to ensure ongoing compatibility with Simple QrCode

## Requirements

- PHP 8.2 or higher
- Laravel 10, 11, or 12
- GD Library or Imagick extension for image manipulation

## Installation

You can install the package via Composer. This will automatically install the required `bacon/bacon-qr-code` package:

```bash
composer require linkxtr/laravel-qrcode
```

## Basic Usage

### Generating QR Codes

```php
use Linkxtr\QrCode\Facades\QrCode;

// Generate a simple QR code
$qrCode = QrCode::generate('https://example.com');

// Generate QR code with custom size and margin
$qrCode = QrCode::size(300)
    ->margin(10)
    ->generate('https://example.com');
```

### Available Methods

```php
// Set QR code size (in pixels)
QrCode::size(250);

// Set QR code margin (in pixels)
QrCode::margin(10);

// Set foreground color
QrCode::color(255, 0, 0); // RGB

// Set background color
QrCode::backgroundColor(255, 255, 255, 0); // RGB with alpha

// Set error correction level (L, M, Q, H)
QrCode::errorCorrection('H');

// Set encoding
QrCode::encoding('UTF-8');

// Generate a data URL
$dataUrl = QrCode::format('png')->generate('https://example.com');
```

### Blade Directive

```blade
<!-- Generate QR code in a view -->
<div class="qr-code">
    {!! QrCode::size(200)->generate('https://example.com') !!}
</div>
```

## Advanced Usage

### Custom Logo Overlay

```php
$qrCode = QrCode::format('png')
    ->size(300)
    ->merge(public_path('logo.png'), 0.3, true)
    ->generate('https://example.com');
```

### Response in Controller

```php
use Linkxtr\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Response;

public function qrCode()
{
    $png = QrCode::format('png')
        ->size(200)
        ->generate('QR Code with custom data');
        
    return response($png)->header('Content-type', 'image/png');
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email security@your-email.com instead of using the issue tracker.

## Credits

- [Khaled Sadek](https://github.com/khaledsadek)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
