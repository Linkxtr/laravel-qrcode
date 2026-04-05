# Laravel QR Code Generator Roadmap

> **🚀 Active Development**: This roadmap outlines our plans for future versions. We welcome community feedback and contributions!

## Overview

This roadmap provides a high-level overview of the future direction of Laravel QR Code Generator. It is a living document that evolves based on community needs and technological changes.

## 🎯 Version Strategy

- **Version 1.x**: Maintains full backward compatibility with `simplesoftwareio/simple-qrcode`
- **Version 2.x**: Introduces new features and may include breaking changes for major improvements (when necessary)

---

## 🔥 Version 2.x - Enhanced Data Types & Modern Formats

### New Data Types

- [ ] **Payments**
  - **EPC (SEPA Credit Transfer)**
    ```php
    QrCode::epc([
        'iban' => 'DE1234...',
        'bic' => 'GENO...',
        'name' => 'Recipient Name',
        'amount' => 50.00,
        'reference' => 'Invoice 123'
    ]);
    ```
  - **PayPal**
    ```php
    QrCode::paypal('user@example.com', 10.00, 'USD', 'Payment Description');
    ```
  - **UPI (Unified Payments Interface)**
    ```php
    QrCode::upi('payee@upi', 'Payee Name', 100.00, 'Note');
    ```

- [ ] **Utilities**
  - **App Store / Google Play**
    ```php
    QrCode::appStore('https://apps.apple.com/app/id...');
    QrCode::googlePlay('com.example.app');
    ```

### New Formats

- [ ] **Plain Text / ASCII Format**

  ```php
  QrCode::format('text')->generate('Content');
  ```

- [ ] **AVIF Format Support**

  ```php
  QrCode::format('avif')->generate('Content');
  ```

- [ ] **Animated QR Codes** (GIF/WebP)
  ```php
  QrCode::animated()
      ->addFrame('Frame 1 content')
      ->addFrame('Frame 2 content')
      ->generate('animated_qr.gif');
  ```

---

### 🎨 Advanced Styling & Frontend Polish

#### Advanced Styling Options

- [ ] **Smart Logo Mergers (Clearance Zones)**

  ```php
  QrCode::mergeWithClearance('logo.png')
      ->logoSize(0.2)
      ->generate('Content');
  // Automatically erases QR dots behind the transparent logo to improve scannability
  ```

- [ ] **Custom Dot Shapes**
  ```php
  QrCode::dotStyle('star')->generate('Content');
  ```
- [ ] **Logo Positioning**
  ```php
  QrCode::merge('logo.png')->logoPosition('center')->generate('Content');
  ```

#### Reusable Themes & Presets

- [ ] **Config-Driven Themes**
  ```php
  // Define themes in config/qrcode.php, then use them globally
  QrCode::theme('primary')->generate('Content');
  QrCode::theme('invoice')->generate('Content');
  ```
- [ ] **Custom Theme Creation (Runtime)**
  ```php
  QrCode::createTheme('modern', function($qr) {
    return $qr->style('dot')->color(58, 94, 255)->eye('circle');
  });
  ```

### Frontend & UI Integration

- [ ] ** Downloadable Blade Component**
  ```blade
  <x-qr-code data="Hello World" downloadable="my-code.svg" />
  ```

### ⚙️ Core Enhancements & Laravel Integration

#### Deep Laravel Integration

- [ ] **Automatic Route Resolution**
  ```php
  // Automatically generates a QR code pointing to a named route
  QrCode::route('invoice.show', $invoice)->generate();
  ```
- [ ] **Eloquent Model Resolution**
  ```php
  // Automatically extracts the model's routable URL or ID
  QrCode::model($user)->generate();
  ```

#### Performance Improvements

- [ ] **Native Laravel Caching**
  ```php
  // Hooks into Laravel's Cache store to prevent regenerating static codes
  QrCode::cache(ttl: 3600)->generate('Content');
  ```
- [ ] **Batch Generation**
  ```php
  QrCode::batch()
    ->add('QR1', 'Content 1')
    ->add('QR2', 'Content 2')
    ->generate('output_directory');
  ```
- [ ] **Lazy Generation**
  ```php
  $qr = QrCode::lazy()->generate('Content');
  // QR is generated only when the string is actively cast or requested
  ```

#### Enhanced Core Features

- [ ] **Forced QR Code Version (Size Matrix)**
  ```php
  QrCode::version(4)->generate('Content');
  ```
- [ ] **Binary Data Support**
  ```php
  QrCode::binary($binaryData)->generate('binary_qr.png');
  ```

---

### 🛠 Developer Experience & Architecture

#### Developer Experience

- [ ] **Artisan Bulk Command**
  ```bash
  php artisan qr:batch-csv qr_codes.csv
  ```

### New Architecture

- [ ] **Renderer Abstraction**

  ```php
  QrCode::renderWith(CustomRenderer::class)->generate('Content');
  ```

- [ ] **Event System**

  ```php
  QrCode::creating(function($content, $options) {
    // Modify before generation
  });

  QrCode::created(function($qrCode, $content) {
    // Post-generation hooks
  });
  ```

### Advanced Features

- [ ] **Dynamic QR Codes**

  ```php
  QrCode::dynamic()
    ->url('[https://example.com/track/](https://example.com/track/){id}')
    ->generate();
  ```

## 🔄 Maintenance & Compatibility

### Backward Compatibility Promise

- **Major versions** may contain breaking changes
- **Minor versions** maintain backward compatibility
- **Patch versions** contain only bug fixes
- **Migration guides** provided for all breaking changes

---

## 🤝 How to Contribute

### Getting Involved

1. **Test Pre-releases**: Help test beta versions
2. **Code Contributions**: Pick up issues labeled "good first issue"
3. **Documentation**: Improve docs and examples
4. **Feature Requests**: Use GitHub issues with the `enhancement` label

### Priority Areas for Contribution

- 🔴 **High Priority**: Payments integration (PayPal, EPC, UPI) and Cryptocurrency (Ethereum)
- 🟡 **Medium Priority**: Developer tools (Commands, Configs) and architectural improvements (Renderer Abstraction, Event System)
- 🟢 **Low Priority**: Advanced styling (Templates, Custom Dot Shapes) and animated QR codes

### Recognition

- Contributors listed in README.md
- Mention in release notes
- Featured in "Community Contributions" blog posts

---

## 💡 Feature Requests

Have an idea that's not on the roadmap? [Open a feature request](https://github.com/linkxtr/laravel-qrcode/issues/new?template=feature_request.md)!

We particularly welcome ideas for:

- New QR code data types
- Integration with other Laravel packages
- Performance improvements
- Developer experience enhancements

---

## 📬 Stay Updated

- **Watch** the GitHub repository for releases
- **Star** the repo to show your support
- **Join** [GitHub Discussions](https://github.com/linkxtr/laravel-qrcode/discussions) for updates

---

<div align="center">

**This roadmap is a living document and may change based on community feedback and technological developments.**

Last updated: April 2026

</div>
