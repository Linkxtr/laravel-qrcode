# Laravel QR Code Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![Total Downloads](https://img.shields.io/packagist/dt/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![PHP Version](https://img.shields.io/packagist/php-v/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![Laravel Version](https://img.shields.io/badge/Laravel-12%20%7C%2013-FF2D20?style=flat-square&logo=laravel)](https://packagist.org/packages/linkxtr/laravel-qrcode)
[![Tests](https://img.shields.io/github/actions/workflow/status/Linkxtr/laravel-qrcode/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Linkxtr/laravel-qrcode/actions/workflows/tests.yml)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen?style=flat-square)](https://github.com/Linkxtr/laravel-qrcode)
[![PHPStan Level Max](https://img.shields.io/badge/PHPStan-level%20max-blue?style=flat-square)](https://github.com/Linkxtr/laravel-qrcode)
[![Scannability: Decode-Verified](https://img.shields.io/badge/Scannability-Decode--Verified-success?style=flat-square&logo=checkmarx&logoColor=white)](https://github.com/Linkxtr/laravel-qrcode)
[![License](https://img.shields.io/packagist/l/linkxtr/laravel-qrcode.svg?style=flat-square)](https://packagist.org/packages/linkxtr/laravel-qrcode)

**`linkxtr/laravel-qrcode`** is the actively maintained, modern QR code generator engineered specifically for **Laravel 12 & 13** (and PHP 8.2–8.5). Built on the vector-native `bacon/bacon-qr-code` v3 engine, it pairs clean Laravel ergonomics with uncompromising engineering rigor—backed by an automated test suite that decodes and validates every generated QR code to prove real-world scannability.

**[📚 Read the Full Documentation](https://laravel-qrcode.mintlify.app)**

---

## 🎨 Visual Showcase Gallery

Every QR code below was generated directly by `linkxtr/laravel-qrcode`. The test suite decodes generated output back to its original payload on every CI run, so "it scans" is an assertion, not a hope.

| Style | Scannable Preview | 3-Line Fluent Snippet |
| :--- | :---: | :--- |
| **1. Classic Brand Color**<br>Crisp brand foreground | <img src="art/showcase-classic.svg" width="135" alt="Classic Brand QR Code"> | <pre><code>QrCode::size(300)<br>    -&gt;color(30, 64, 175)<br>    -&gt;generate(&#39;https://linkxtr.com&#39;);</code></pre> |
| **2. Dot Modules + Circle Eyes**<br>Modern rounded aesthetic | <img src="art/showcase-dots.svg" width="135" alt="Dot Modules with Circle Eyes"> | <pre><code>QrCode::size(300)<br>    -&gt;style(&#39;dot&#39;)-&gt;eye(&#39;circle&#39;)<br>    -&gt;color(17, 24, 39)<br>    -&gt;generate(&#39;https://linkxtr.com&#39;);</code></pre> |
| **3. Diagonal Gradient**<br>Vibrant two-tone transition | <img src="art/showcase-gradient.svg" width="135" alt="Diagonal Gradient QR Code"> | <pre><code>QrCode::size(300)<br>    -&gt;gradient([99, 102, 241], [236, 72, 153], &#39;diagonal&#39;)<br>    -&gt;eye(&#39;circle&#39;)<br>    -&gt;generate(&#39;https://linkxtr.com&#39;);</code></pre> |
| **4. Multi-Color Custom Eyes**<br>Distinct corner branding | <img src="art/showcase-custom-eyes.svg" width="135" alt="Multi-Color Custom Eyes"> | <pre><code>QrCode::size(300)<br>    -&gt;color(31, 41, 55)<br>    -&gt;eyeColor(0, [239, 68, 68], [153, 27, 27])<br>    -&gt;eyeColor(1, [16, 185, 129], [6, 95, 70])<br>    -&gt;generate(&#39;https://linkxtr.com&#39;);</code></pre> |
| **5. Center Logo Merge**<br>Embedded logo with high EC | <img src="art/showcase-logo.svg" width="135" alt="Center Logo Merge QR Code"> | <pre><code>QrCode::size(300)<br>    -&gt;errorCorrection(&#39;H&#39;)<br>    -&gt;merge(public_path(&#39;images/logo.png&#39;), 0.25)<br>    -&gt;generate(&#39;https://linkxtr.com&#39;);</code></pre> |

> `merge()` expects a **raster** logo — point it at a PNG (or JPEG) that exists in your application; `public_path('images/logo.png')` is illustrative. An **SVG logo throws `ImageMergeException: Invalid image provided for merge`** when the output format is SVG, which is the default. Keep `errorCorrection('H')` so the payload survives the overlay.

---

## ⚖️ Why `linkxtr/laravel-qrcode`?

| Feature | `linkxtr/laravel-qrcode` | `simplesoftwareio/simple-qrcode` | Raw `endroid/qr-code` |
| :--- | :---: | :---: | :---: |
| **Active Maintenance** | ✅ **Actively Maintained** (v3.4.0, Oct 2026) | ⚠️ No release since Feb 2021 | ✅ Active generic library |
| **Laravel Compatibility** | ✅ **Tested on Laravel 12 & 13** (CI matrix) | ⚠️ No declared Laravel constraint; no CI | ⚠️ Requires manual wiring |
| **PHP Support** | ✅ **PHP 8.2 – 8.5** (CI matrix) | ⚠️ `>=7.2 \| ^8.0`, no CI matrix | ⚠️ Varies by version (latest 6.x needs 8.5) |
| **Underlying Engine** | `bacon/bacon-qr-code` ^3 | `bacon/bacon-qr-code` ^2 | `bacon/bacon-qr-code` ^3 + GD-based writer |
| **Immutable Fluent Builder** | ✅ Cloned instances (no leak) | ❌ Mutable state leakage | ⚠️ Partial builder pattern |
| **Laravel Facade** | ✅ `QrCode::...` (Drop-in familiar) | ✅ Legacy facade | ❌ None |
| **Native Blade Component** | ✅ `<x-qr-code />` with accessible SVG | ❌ None | ❌ None |
| **Artisan CLI Generator** | ✅ `php artisan qr:generate` | ❌ None | ❌ None |
| **Laravel Cache Integration** | ✅ Built-in (`cache()`, TTL, stores) | ❌ None | ❌ None |
| **Rich Data Types** | ✅ 12 built-in payloads | ⚠️ 6 basic payloads | ❌ None built-in |
| **Automated Scannability** | ✅ Every QR code decoded in CI | ❌ None | ❌ None |
| **Quality & Coverage** | ✅ **100% Coverage & PHPStan Max** | ❌ No static analysis or test matrix | ⚠️ Variable |

> **Coming from `simple-qrcode`?** The fluent surface will feel familiar (`QrCode::size()->generate()`), but v3 does not ship a compatibility layer, so a handful of method names differ. The [migration guide](https://laravel-qrcode.mintlify.app/advanced/migrating-from-simple-qrcode) walks through each difference — it takes about ten minutes.
>
> Comparison data verified against Packagist on 2026-11-05.

---

## ⚡ Quickstart

Install the package via Composer:

```bash
composer require linkxtr/laravel-qrcode
```

Optionally publish the configuration file:

```bash
php artisan vendor:publish --tag=qrcode-config
```

---

## 🛠️ Usage

### 1. The Fluent Facade

```php
use Linkxtr\QrCode\Facades\QrCode;

$qr = QrCode::size(400)
    ->format('svg')
    ->color(30, 64, 175)
    ->errorCorrection('H')
    ->merge(public_path('logo.png'), 0.25)
    ->generate('https://example.com');
```

### 2. The Blade Component

Drop accessible QR codes directly into Blade views with zero PHP logic:

```blade
<x-qr-code
    data="https://example.com"
    size="300"
    color="#1E40AF"
    margin="2"
/>
```

The component automatically renders compliant SVG markup, inherits default settings from `config/qrcode.php`, and applies accessibility attributes (`role="img"`, `aria-label`).

### 3. Interactive Artisan CLI

Generate QR codes straight from your terminal with interactive prompts:

```bash
php artisan qr:generate
```

Or pass flags directly:

```bash
php artisan qr:generate "https://example.com" --output="public/qr.svg" --format=svg --size=400
```

### 4. High-Performance Caching

Cache expensive QR code rendering seamlessly using Laravel's cache manager:

```php
// Cache for the configured default TTL
QrCode::size(400)->cache()->generate('https://example.com');

// Cache for a custom TTL (in seconds) or DateInterval
QrCode::size(400)->cache(3600)->generate('https://example.com');
```

### 5. Rich Data Types

Built-in helpers format payloads according to international standards:

```php
// Wi-Fi network configuration
QrCode::WiFi([
    'ssid' => 'OfficeNetwork',
    'encryption' => 'WPA2',
    'password' => 'secret-key',
]);

// Contact card (vCard / MeCard)
QrCode::VCard(
    name: 'Omar Fathy',
    phone: '+1234567890',
    email: 'omar@example.com',
    company: 'ApricotCake',
);

// Cryptocurrency payment
QrCode::BTC('bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh', 0.005);
QrCode::Ethereum('0x71C7656EC7ab88b098defB751B7401B5f6d8976F', 0.5);

// Direct messaging & communication
QrCode::Email('hello@example.com', 'Say Hi!');
QrCode::Telegram('username');
QrCode::WhatsApp('1234567890', 'Hello from Laravel!');
QrCode::SMS('1234567890', 'Verification Code');
```

---

## 📖 Documentation

For full installation guides, advanced customization (radial/diagonal gradients, CMYK color spaces, custom eye shapes), and macro registration, visit the official documentation:

👉 **[laravel-qrcode.mintlify.app](https://laravel-qrcode.mintlify.app)**

---

## 🚀 Upgrading to V3

If you are upgrading from `simplesoftwareio/simple-qrcode` or `linkxtr/laravel-qrcode` v1.x/v2.x, please refer to our **[Upgrade Guide](https://laravel-qrcode.mintlify.app/advanced/upgrade-guide)** for breaking changes and migration steps.

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
