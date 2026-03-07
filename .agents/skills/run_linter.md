---
name: run_linter
description: Automatically refactors and formats the PHP code to match project standards using Rector and Laravel Pint.
command: composer test:refactor && composer test:lint
---

You must run this skill before finalizing any code modifications. This ensures that legacy PHP structures are upgraded (via Rector) and that the code adheres strictly to the Laravel Pint styling rules defined in the project.
