# Laravel QR Code Generator Roadmap

> **🚀 Active Development**: This roadmap outlines our plans for future versions. We welcome community feedback and contributions!

## Overview

This roadmap provides a high-level overview of the future direction of Laravel QR Code Generator. It's a living document that evolves based on community needs and technological changes.

## 🎯 Version Strategy

- **Version 1.x**: Maintains full backward compatibility with `simplesoftwareio/simple-qrcode`
- **Version 2.x**: Introduces new features and May include breaking changes for major improvements (when necessary)

---

## 🔥 Version 2.x - Enhanced Data Types & Modern Formats

### New Data Types

- [ ] **vCard Support**

  ```php
  QrCode::vCard([
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'phone' => '+1234567890',
      'company' => 'ACME Inc.',
      'title' => 'Developer',
      'url' => 'https://example.com'
  ]);
  ```

- [ ] **Calendar Events**

  ```php
  QrCode::calendarEvent([
      'summary' => 'Team Meeting',
      'description' => 'Weekly team sync',
      'location' => 'Conference Room A',
      'start' => '2024-06-01 10:00:00',
      'end' => '2024-06-01 11:00:00'
  ]);
  ```

- [ ] **Cryptocurrency Payments**

  ```php
  QrCode::bitcoin('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 0.001);
  QrCode::ethereum('0x742d35Cc6634C0532925a3b8D...');
  ```

- [ ] **MeCard Support**
  ```php
  QrCode::meCard('John Doe', '+1234567890', 'john@example.com');
  ```

### New Formats

- [ ] **WebP Format Support**

  ```php
  QrCode::format('webp')->generate('Content');
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

### Enhanced Core Features

- [ ] **Binary Data Support**
  ```php
  QrCode::binary($binaryData)->generate('binary_qr.png');
  ```

### Advanced Styling Options

- [ ] **Gradient Backgrounds**

  ```php
  QrCode::gradientBackground('#FF0000', '#0000FF', 'diagonal')
      ->generate('Content');
  ```

- [ ] **Custom Dot Shapes**

  ```php
  QrCode::dotStyle('star')
      ->generate('Content');
  ```

- [ ] **Logo Positioning & Scaling**

  ```php
  QrCode::merge('logo.png')
      ->logoPosition('center')
      ->logoSize(0.2) // 20% of QR code
      ->generate('Content');
  ```

- [ ] **Transparent Backgrounds**
  ```php
  QrCode::backgroundColor(null) // Transparent
      ->generate('Content');
  ```

### Template System

- [ ] **QR Code Templates**

  ```php
  QrCode::template('modern')
      ->generate('Content');

  QrCode::template('corporate')
      ->generate('Content');
  ```

- [ ] **Custom Template Creation**
  ```php
  QrCode::createTemplate('my-template', function($qr) {
      return $qr->style('dot')
          ->color(58, 94, 255)
          ->eye('circle');
  });
  ```

### Performance Improvements

- [ ] **Caching System**

  ```php
  QrCode::cache(3600) // Cache for 1 hour
      ->generate('Content');
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
  // QR is generated only when needed
  ```

### Developer Experience

- [ ] **Better IDE Support**

  - Enhanced PHPDoc annotations
  - IDE helper file generation
  - Laravel Idea compatibility

- [ ] **Configuration File**

  ```php
  // config/qrcode.php
  return [
      'default_size' => 200,
      'default_format' => 'svg',
      'default_error_correction' => 'H',
      'cache' => [
          'enabled' => true,
          'duration' => 3600,
      ],
  ];
  ```

- [ ] **Artisan Commands**
  ```bash
  php artisan qr-code:generate "Content" --size=300 --format=png
  php artisan qr-code:batch-csv qr_codes.csv
  ```

### Testing & Quality

- [ ] **Increased Test Coverage** (95%+)
- [ ] **Performance Benchmarking**
- [ ] **Security Auditing**

### Modern PHP Features

- [ ] **PHP 8.2+ Minimum Requirement**
- [ ] **Native Enums**

  ```php
  QrCode::format(Format::WebP);
  QrCode::errorCorrection(ErrorCorrection::High);
  ```

- [ ] **Constructor Property Promotion**
- [ ] **Readonly Properties**

### New Architecture

- [ ] **Renderer Abstraction**

  ```php
  QrCode::renderWith(CustomRenderer::class)
      ->generate('Content');
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

- [ ] **Plugin System**
  ```php
  QrCode::extend('customType', function($data) {
      // Custom QR code type
  });
  ```

### Advanced Features

- [ ] **Dynamic QR Codes**

  ```php
  QrCode::dynamic()
      ->url('https://example.com/track/{id}')
      ->generate();
  ```

- [ ] **QR Code Analytics**

  ```php
  QrCode::analytics(true)
      ->generate('Trackable Content');
  ```

- [ ] **Multi-language Support**
- [ ] **Accessibility Features**

---

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

- 🔴 **High Priority**: vCard and calendar event support
- 🟡 **Medium Priority**: WebP format and styling improvements
- 🟢 **Low Priority**: Artisan commands and developer tools

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

Last updated: Dec 2025

</div>
