# Contributing to Laravel QR Code Generator

Thank you for considering contributing to Laravel QR Code Generator! We appreciate your help in making this package better for everyone.

## 🎯 Before You Start

### Code of Conduct

Please read and follow our [Code of Conduct](CODE_OF_CONDUCT.md) to ensure a welcoming environment for all.

### Support Questions

Please use GitHub Issues only for bug reports and feature requests. For support questions, use:

- [GitHub Discussions](https://github.com/linkxtr/laravel-qrcode/discussions)

## 🚀 Getting Started

### Development Setup

1. **Fork the Repository**

   ```bash
   git clone https://github.com/linkxtr/laravel-qrcode.git
   cd laravel-qrcode
   ```

2. **Install Dependencies**

   ```bash
   composer install
   ```

3. **Set Up Testing Environment**

   ```bash
   # Create a test Laravel application
   composer create-project laravel/laravel test-app
   cd test-app

   # Link your local package
   composer config repositories.local path ../laravel-qrcode
   composer require linkxtr/laravel-qrcode:@dev
   ```

### Running Tests

```bash
# Run the test suite
composer test

# Run with coverage
composer test-coverage

# Run specific test file
./vendor/bin/pest tests/QrCodeTest.php

# Run with specific PHP version
php8.2 ./vendor/bin/pest
```

## 💡 How to Contribute

### Reporting Bugs

**Before reporting a bug:**

- Check if the issue already exists in [GitHub Issues](https://github.com/linkxtr/laravel-qrcode/issues)
- Test with the latest version of the package

**Bug report template:**

```markdown
## Description

Clear and concise description of the bug.

## Steps to Reproduce

1.
2.
3.

## Expected Behavior

What you expected to happen.

## Actual Behavior

What actually happened.

## Environment

- PHP Version:
- Laravel Version:
- Package Version:
- Server: [Apache/Nginx/Other]

## Additional Context

Screenshots, error logs, etc.
```

### Suggesting Features

**Feature request template:**

```markdown
## Problem Statement

What problem are you trying to solve?

## Proposed Solution

Describe the solution you'd like.

## Alternatives Considered

Describe alternatives you've considered.

## Additional Context

Add any other context about the feature request.
```

### Branching Strategy

- **`main`**: Active development for Version 2.x. Target this branch for new features and non-critical bug fixes.
- **`1.x`**: LTS/Maintenance for Version 1.x. Target this branch ONLY for critical bug fixes and security updates for V1.

### Pull Requests

1. **Fork the Repository**
2. **Commit Your Changes**
3. **Open a Pull Request**
   - If fixing a bug in V1, base your PR on `1.x`.
   - If adding a feature or fixing V2, base your PR on `main`.

## 📝 Development Guidelines

### Code Style

We use PHP CS Fixer to maintain code style:

```bash
# Fix code style issues
composer lint
```

**Key Standards:**

- PSR-12 coding standard
- Type hints for all method parameters and return types
- Strict types declaration
- Meaningful variable and method names

### Testing Standards

**Unit Tests:**

- Place tests in `tests/` directory
- Test both success and failure scenarios
- Use descriptive test method names

```php
<?php

use Linkxtr\QrCode\QrCode;

it('generates basic qr code', function (){
    $result = (new QrCode)->format('svg')->generate('Test Content');

    expect($result)->toBeString()->toContain('<svg');
});

it('handles size parameter correctly', function() {
    // Test implementation
});
```

### Documentation

**When adding new features:**

- Update README.md with usage examples
- Update type definitions if applicable

## 🏗️ Project Structure

```
laravel-qrcode/
├── src/
│   ├── Facades/
│   │   └── QrCode.php
│   ├── QrCodeServiceProvider.php
│   └── QrCode.php
└── tests/
    ├── QrCodeTest.php
    └── Datatypes/
        └── EmailTest.php

```

### Adding New Formats

1. **Extend the format method:**

   ```php
   public function format(string $format): self
   {
       if (!in_array($format, ['svg', 'png', 'eps', 'webp'])) {
           throw new InvalidArgumentException("Format {$format} is not supported.");
       }

       $this->format = $format;
       return $this;
   }
   ```

2. **Update the generator to handle new format**

## 🐛 Common Issues & Solutions

### Testing Issues

**Problem:** Tests failing due to missing dependencies
**Solution:**

```bash
composer update
./vendor/bin/pest --check-version
```

### Development Issues

**Problem:** Can't test locally in Laravel app
**Solution:** Use Composer path repository

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../laravel-qrcode"
    }
  ]
}
```

## 📋 Pull Request Checklist

Before submitting a PR, ensure:

- [ ] Tests are added/updated and all pass
- [ ] PHPStan analysis passes
- [ ] Documentation is updated
- [ ] Commit messages follow [Conventional Commits](https://www.conventionalcommits.org/)
- [ ] PR description includes context and related issues
- [ ] Branch is up to date with `main`

### Commit Message Convention

```
feat: add new data type for vCard
fix: resolve color encoding issue
docs: update installation instructions
test: add coverage for error correction
refactor: simplify merge method
chore: update dependencies
```

## 🏆 Recognition

All contributors will be:

- Listed in the README.md contributors section
- Mentioned in release notes for their contributions
- Celebrated in our GitHub discussions

## ❓ Need Help?

- Join our [GitHub Discussions](https://github.com/linkxtr/laravel-qrcode/discussions)
- Check existing [Issues](https://github.com/linkxtr/laravel-qrcode/issues)
- Email maintainers at [khaledsadek286@gmail.com](mailto:khaledsadek286@gmail.com)

## 📄 License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to Laravel QR Code Generator! 🎉

<div align="center">
  
Made with ❤️ by [Khaled Sadek](https://github.com/khaled-sadek)

</div>
