# Laravel QR Code Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![Total Downloads](https://img.shields.io/packagist/dt/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![PHP Version](https://img.shields.io/packagist/php-v/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![License](https://img.shields.io/packagist/l/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)

> **🚀 Maintained Alternative**: This package is a fully compatible, actively maintained alternative to [`simplesoftwareio/simple-qrcode`](https://github.com/simplesoftwareio/simple-qrcode) with additional features and better support.

A beautiful and simple QR code generator for Laravel. This package provides an expressive interface for generating QR codes in various formats with extensive customization options.

## 🔄 Migration from [simplesoftwareio/simple-qrcode](https://github.com/simplesoftwareio/simple-qrcode)

Switching from the original package is straightforward:

### Step 1: Update Composer
```bash
composer remove simplesoftwareio/simple-qrcode
composer require linkxtr/laravel-qrcode
```

### Step 2: Update Imports (if using in classes)

```php
// Replace:
use SimpleSoftwareIO\QrCode\Facades\QrCode;

// With:
use Linkxtr\LaravelQrCode\Facades\QrCode;
```

### ✅ That's it! All your existing code will work exactly the same.

**Why migrate?**
- ✅ **Active Maintenance**: Regular updates and bug fixes
- ✅ **Better Performance**: Updated dependencies
- ✅ **Enhanced Security**: Security patches applied
- ✅ **Future Features**: Roadmap includes new formats and data types
- ✅ **Community Support**: Active issue response and PR merging

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

### 🔄 Full Compatibility
- **Drop-in replacement** for `simplesoftwareio/simple-qrcode`
- **Same API** - no code changes required
- **All original methods supported**

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
```

### 🆕 Coming in Version 2
- 📅 Calendar events
- 👤 vCard contacts  
- 🎬 WebP and animated formats
- 🎯 More styling options

## 📊 Comparison with simplesoftwareio/simple-qrcode

| Feature | simplesoftwareio/simple-qrcode | linkxtr/laravel-qrcode |
|---------|--------------------------------|------------------------|
| Active Maintenance | ❌ | ✅ |
| Security Updates | ❌ | ✅ |
| Issue Response | ❌ | ✅ |
| PR Merging | ❌ | ✅ |
| API Compatibility | ✅ | ✅ |
| Performance | Standard | Improved |
| Future Features | None planned | Active roadmap |

> **Note**: This package maintains 100% API compatibility with the original.

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
```php
// Merge with logo
QrCode::merge('path/to/logo.png', 0.3, true)->generate('With Logo');
```

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
- [ ] vCard data type
- [ ] Calendar event data type  
- [ ] WebP format support
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
- `color($red, $green, $blue)` - Set QR color
- `backgroundColor($red, $green, $blue)` - Set background color
- `margin($margin)` - Set margin size
- `format($format)` - Set format (svg, png, eps)

### Data Type Methods
- `email($to, $subject, $body)` - Generate email QR
- `phoneNumber($phone)` - Generate phone QR
- `SMS($phone, $message)` - Generate SMS QR
- `geo($lat, $lng)` - Generate location QR
- `wiFi($config)` - Generate WiFi QR
- `btc($config)` - Generate BTC QR

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Based on the original work by [`simplesoftwareio/simple-qrcode`](https://github.com/simplesoftwareio/simple-qrcode)
- Built upon [`bacon/bacon-qr-code`](https://github.com/Bacon/BaconQrCode)
- Maintained by [khaled-sadek](https://github.com/khaled-sadek) and [contributors](../../contributors)

---

<div align="center">
  
**Need help?** [Open an issue](https://github.com/linkxtr/laravel-qrcode/issues) • **Found a bug?** [Report it](https://github.com/linkxtr/laravel-qrcode/issues)

*⭐ Don't forget to star this repository if you find it useful!*

</div>