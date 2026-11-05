# Upgrade Guide

## 3.3.x → 3.4.0: `cache_ttl` is documented in seconds

Both versions hand the integer straight to Laravel's cache repository, which has always read it as **seconds**. What 3.4.0 changed is the documentation and the shipped default:

- 3.3.x documented the value as minutes and defaulted to `1440` — which was really 24 **minutes**.
- 3.4.0 documents seconds and defaults to `86400` — 24 **hours**.

Your configured value never changed meaning; only the default did.

```dotenv
# 1440 has always meant 24 minutes, in both versions
QR_CODE_CACHE_TTL=1440

# If you wanted a day, this is the value you always wanted
QR_CODE_CACHE_TTL=86400
```

What to do depends on how you configured it:

- **You set `QR_CODE_CACHE_TTL` (or passed a TTL yourself):** keep it. It still means seconds, exactly as before. If you picked `1440` believing it was a day, it has always been 24 minutes — raise it to `86400` now.
- **You never set it and never published the config file:** the effective default grows from 24 minutes to 24 hours. No action needed unless you want the shorter lifetime, in which case set it explicitly.
- **You published `config/qrcode.php`:** your copy still contains `env('QR_CODE_CACHE_TTL', 1440)` and therefore keeps the old 24-minute default. Set the environment variable, edit the published value to `86400`, or re-publish with `--force` and reapply your edits to adopt the new default.

If you pass a TTL at runtime, `cache()` also accepts a `DateInterval` or `DateTimeInterface`, which is unaffected by any of this:

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
