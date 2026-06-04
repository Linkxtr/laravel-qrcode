# Laravel QR Code Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![Total Downloads](https://img.shields.io/packagist/dt/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![PHP Version](https://img.shields.io/packagist/php-v/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![Laravel Version](https://img.shields.io/badge/Laravel-11+-FF2D20?style=flat-square&logo=laravel)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![License](https://img.shields.io/packagist/l/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)

A clean, fluent, and modern QR code generator for Laravel. Generate SVG, PNG, WebP, and EPS codes via a fluent Facade, a native Blade component, or an interactive Artisan CLI.

**[📚 Read the Full Documentation](https://laravel-qrcode.mintlify.app)**

---

## ⚡ Quickstart

Install the package via Composer:

```bash
composer require linkxtr/laravel-qrcode
```

## The Fluent Facade

```php
use Linkxtr\QrCode\Facades\QrCode;

$qr = QrCode::size(400)
    ->format('png')
    ->color(30, 64, 175)
    ->errorCorrection('H')
    ->merge(public_path('logo.png'), 0.25)
    ->generate('[https://example.com](https://example.com)');
```

## The Blade Component

Drop QR codes directly into your views with zero PHP logic. It automatically handles accessibility (aria-label) and escaping:

```blade
<x-qr-code
    data="[https://example.com](https://example.com)"
    size="300"
    color="#1E40AF"
    margin="2"
/>
```

## Rich Data Types

Built-in helpers for standardized payloads:

```php
QrCode::WiFi([
    'ssid' => 'OfficeNetwork',
    'encryption' => 'WPA2',
    'password' => 'secret'
]);

QrCode::Email('hello@example.com', 'Say Hi!');
QrCode::BTC('bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh', 0.005);
```

## 📖 Documentation

For full installation instructions, detailed customization options (gradients, CMYK, custom eye shapes), and advanced macro usage, please visit the official documentation:

👉 [laravel-qrcode.mintlify.app](https://laravel-qrcode.mintlify.app)

## 🚀 Upgrading to V3

If you are upgrading from v1.x or v2.x, please refer to our [Upgrade Guide](https://laravel-qrcode.mintlify.app/advanced/upgrade-guide) for a complete list of breaking changes and migration steps.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
