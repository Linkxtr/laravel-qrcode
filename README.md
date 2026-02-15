# Laravel QR Code Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![Total Downloads](https://img.shields.io/packagist/dt/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![PHP Version](https://img.shields.io/packagist/php-v/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![License](https://img.shields.io/packagist/l/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)

A simple and easy-to-use QR Code generator for Laravel, based on the `bacon/bacon-qr-code` library.

**Note:** This is version `2.x`.

- **Version 2.x** is the current stable release, requiring PHP 8.2+ and Laravel 11+.
- **Version 1.x** is the LTS/Maintenance version. If you need **Laravel 10** support or PHP 8.1, please use [version 1.x](https://github.com/Linkxtr/laravel-qrcode/tree/1.x).
- Version 2.x drops compatibility with `simplesoftwareio/simple-qrcode` to provide a more streamlined API.
- 📚 **Upgrading?** Check out the [Upgrade Guide](docs/UPGRADE-2.0.md).

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher
- `ext-imagick` extension (optional, but recommended for better performance). If `imagick` is not available, the package will automatically fallback to using `gd` for PNG and WebP generation.

## 📦 Installation

```bash
composer require linkxtr/laravel-qrcode
```

The package uses Laravel's package auto-discovery, so the service provider and facade are registered automatically.

## 🚀 Quick Start

### Basic Usage in Blade Templates

```blade
<!-- Display QR code -->
{!! QrCode::generate('Hello World!'); !!}

<!-- Generate and save to file -->
{!! QrCode::generate('Hello World!', public_path('qrcode.svg')); !!}
```

### In Controllers

```php
use Linkxtr\LaravelQrCode\Facades\QrCode;

public function generate()
{
    // Return as SVG response
    return QrCode::generate('QR Code Content');

    // Or save to file
    QrCode::generate('Content', storage_path('app/qrcodes/qr.svg'));

    // Or get as string
    $svg = QrCode::generate('Content');
}
```

## ✨ Features

### 🎨 Enhanced Customization

```php
// Colors and styling
QrCode::size(300)
    ->color(255, 0, 0)
    ->backgroundColor(255, 255, 255)
    ->style('dot')
    ->eye('circle')
    ->generate('Styled QR Code');

// Error correction
QrCode::errorCorrection('H')->generate('Important Data');

// Merge images
QrCode::merge('path/to/logo.png')->generate('With Logo');
```

### 📱 Multiple Data Types

```php
// URLs
QrCode::generate('https://example.com');

// Emails
QrCode::email('to@example.com', 'Subject', 'Body');

// Phone numbers
QrCode::phoneNumber('+1234567890');

// SMS
QrCode::SMS('+1234567890', 'Message body');

// WiFi
QrCode::wiFi([
    'ssid' => 'Network',
    'encryption' => 'WPA',
    'password' => 'Password'
]);

// Geolocation
QrCode::geo(37.7749, -122.4194);

// BTC
QrCode::btc(['btcaddress', 0.0034, ['label' => 'label', 'message' => 'message', 'returnAddress' => 'https://www.returnaddress.com']]);

// vCard
QrCode::vCard([
    'name' => 'John Doe',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890',
    'company' => 'Tech Corp',
    'title' => 'Developer',
    'url' => 'https://example.com'
]);

// Calendar Event
use Carbon\Carbon;

QrCode::calendar([
    'summary' => 'Laracon US',
    'description' => 'The official Laravel conference.',
    'location' => 'New York, NY',
    'start' => Carbon::create(2024, 8, 27, 9, 0, 0),
    'end' => Carbon::create(2024, 8, 28, 17, 0, 0),
]);

// WhatsApp
QrCode::WhatsApp(['number' => '+1234567890', 'message' => 'Hello from Laravel!']);

// Telegram
QrCode::telegram('username');
```

## 🔧 Advanced Usage

### All Available Methods

```php
// Size and format
QrCode::size(250)->format('png')->generate('Content');

// Colors with RGB
QrCode::color(255, 0, 0)->generate('Red QR');

// Background color
QrCode::backgroundColor(255, 255, 0)->generate('Yellow background');

// Margin
QrCode::margin(2)->generate('With margin');

// Encoding
QrCode::encoding('UTF-8')->generate('Unicode content');

// Gradient colors
QrCode::gradient(0, 0, 255, 255, 0, 0, 'vertical')->generate('Gradient');
```

### Error Correction Levels

- `L` - 7% of data bytes can be restored
- `M` - 15% of data bytes can be restored
- `Q` - 25% of data bytes can be restored
- `H` - 30% of data bytes can be restored (default)

```php
QrCode::errorCorrection('H')->generate('High error correction');
```

### Image Merging

Image merging is supported for PNG, WebP, and SVG formats.

```php
// Merge with logo
QrCode::format('png')->merge('path/to/logo.png', 0.3, true)->generate('With Logo');

// Merge with SVG
QrCode::format('svg')->merge('path/to/logo.png', 0.3, true)->generate('With Logo');
```

**Note:** Image merge is not supported for EPS format.

## 💡 Common Examples

### Generate QR for Website

```php
QrCode::size(200)
    ->generate('https://your-website.com');
```

### QR Code with Logo

```php
QrCode::size(300)
    ->merge(public_path('logo.png'), 0.3, true)
    ->generate('QR with logo');
```

### Colorful QR Code

```php
QrCode::size(300)
    ->color(58, 94, 255)
    ->backgroundColor(255, 255, 255)
    ->generate('Colorful QR');
```

### WiFi QR Code

```php
QrCode::wiFi([
    'ssid' => 'MyWiFi',
    'encryption' => 'WPA',
    'password' => 'my-password'
]);
```

### Styled QR Code

```php
QrCode::size(250)
    ->color(255, 0, 0)
    ->style('dot')
    ->eye('circle')
    ->margin(1)
    ->generate('Styled QR Code');
```

## 🤝 Contributing & 🗺️ Roadmap

### Version 2 Roadmap

We're actively working on Version 2 with these planned features:

- [x] vCard data type
- [x] Calendar event data type
- [x] WebP format support
- [x] Telegram data type support
- [x] WhatsApp data type support
- [ ] Animated QR codes
- [ ] Bitcoin payment QR codes
- [ ] Extended customization options

### Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Request Features

Have an idea? [Open an issue](https://github.com/linkxtr/laravel-qrcode/issues) with your feature request!

## 🐛 Troubleshooting

### Common Issues

**QR code not displaying in blade:**

```blade
<!-- Make sure to use unescaped output -->
{!! QrCode::generate('Content') !!}  ✅
{{ QrCode::generate('Content') }}     ❌
```

**File permission errors:**

```php
// Ensure directory exists and is writable
QrCode::generate('Content', storage_path('app/qrcodes/qr.svg'));
```

**Large QR codes:**

```php
// For large content, use higher error correction
QrCode::size(400)
    ->errorCorrection('H')
    ->generate('Very long content...');
```

## 📚 API Reference

### Core Methods

- `generate($text, $filename = null)` - Generate QR code
- `size($size)` - Set size in pixels
- `color($red, $green, $blue, $alpha = null)` - Set QR color (alpha 0-127)
- `backgroundColor($red, $green, $blue, $alpha = null)` - Set background color (alpha 0-127)
- `style($style)` - Set style (dot, square, round)
- `eye($style)` - Set eye style (circle, square)
- `gradient($startRed, $startGreen, $startBlue, $endRed, $endGreen, $endBlue, $type)` - Set gradient color
- `format($format)` - Set format (svg, png, eps, webp)
- `margin($margin)` - Set margin size
- `errorCorrection($level)` - Set error correction level (L, M, Q, H)
- `encoding($encoding)` - Set character encoding
- `merge($image, $percentage, $absolute)` - Merge image/logo

### Data Type Methods

- `email($to, $subject, $body)` - Generate email QR
- `phoneNumber($phone)` - Generate phone QR
- `SMS($phone, $message)` - Generate SMS QR
- `geo($lat, $lng)` - Generate location QR
- `wiFi($config)` - Generate WiFi QR
- `btc($config)` - Generate BTC QR
- `vCard($config)` - Generate vCard QR
- `calendar($config)` - Generate Calendar Event QR
- `WhatsApp($params)` - Generate WhatsApp QR (array with phone and optional message)
- `telegram($username)` - Generate Telegram QR

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Based on the original work by [`simplesoftwareio/simple-qrcode`](https://github.com/simplesoftwareio/simple-qrcode)
- Built upon [`bacon/bacon-qr-code`](https://github.com/Bacon/BaconQrCode)
- Maintained by [khaled-sadek](https://github.com/khaled-sadek) and [contributors](../../contributors)

---

<div align="center">
  
**Need help?** [Open an issue](https://github.com/linkxtr/laravel-qrcode/issues) • **Found a bug?** [Report it](https://github.com/linkxtr/laravel-qrcode/issues)

_⭐ If you find this repository useful, please consider starring it._

</div>
