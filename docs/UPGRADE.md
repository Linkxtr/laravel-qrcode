# Upgrade Guide

## 3.3.x → 3.4.0: `cache_ttl` is now in seconds

`cache_ttl` (and the `QR_CODE_CACHE_TTL` environment variable) used to be documented as **minutes**. It is now **seconds**, matching what Laravel's cache repository actually expects, and the default moved from `1440` to `86400` (24 hours).

- **You never set it?** Nothing to do — the effective default is still 24 hours.
- **You did set it?** Convert your value, otherwise your cache now expires 60× sooner.

```dotenv
# Before (3.3.x): 1440 = 24 hours
QR_CODE_CACHE_TTL=1440

# After (3.4.0+): 86400 = 24 hours
QR_CODE_CACHE_TTL=86400
```

If you pass a TTL at runtime, `cache()` accepts a `DateInterval` or `DateTimeInterface` and is unaffected by this change:

```php
QrCode::cache(now()->addDay())->generate('https://example.com');
```

Also in 3.4.0:

- The Blade component now inherits `size`, `margin`, and `format` from `config/qrcode.php` instead of hardcoding its own defaults, so `<x-qr-code />` picks up your published configuration (and the configured quiet zone).
- Inline SVG output from `QrCodeResult::toHtml()` and the Blade component no longer includes the `<?xml ... ?>` prolog, which previously produced broken markup when PHP's `short_open_tag` was enabled.
- `QrCodeResult::toResponse()` now sends `ETag` and `Cache-Control: private, max-age=86400`, and answers conditional requests with `304 Not Modified`.
- A new `cache_store` option / `QR_CODE_CACHE_STORE` variable lets QR cache entries live in a dedicated cache store.

## Migrating to V3

The full upgrade guide for migrating to V3 has been moved to our official documentation site.

👉 **[Read the V3 Upgrade Guide Here](https://laravel-qrcode.mintlify.app/advanced/upgrade-guide)**
