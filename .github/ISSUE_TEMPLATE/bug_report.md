---
name: Bug Report
about: Create a report to help us improve the package
title: "[BUG] "
labels: bug
assignees: ""
---

**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Code snippet to reproduce the behavior:

```php
QrCode::size(300)->color(255, 0, 0, 50)->phoneNumber('...');
```

**Expected behavior**
A clear and concise description of what you expected to happen.

**Environment:**

- PHP Version: [e.g. 8.2]
- Laravel Version: [e.g. 12.0]
- Package Version: [e.g. 3.0.0]
- Image Driver: [Imagick / GD / None]
- **QR Rendering Config** (Optional - Please fill this out if your issue relates to visual output, rendering, or colors):
  - `color`: [R, G, B, Alpha] (0-255 or 0-100)
  - `background_color`: [R, G, B, Alpha] (0-255 or 0-100)
  - `alpha` values (foreground/background): [e.g., 100 for solid, 50 for 50% transparent]
  - `color_alpha` (foreground alpha): [0-100]
  - `background_color_alpha` (background alpha): [0-100]
  - _Note: Alpha values range from 0 (transparent) to 100 (opaque)._
