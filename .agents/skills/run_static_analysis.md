---
name: run_static_analysis
description: Executes PHPStan to find type errors, undefined methods, and bugs without running the code.
command: composer test:types
---

You must use this skill after modifying any core logic, adding new Data Types, or updating method signatures. If PHPStan reports any errors, you are required to autonomously fix the code and run this skill again until it passes cleanly. If errors persist after 3 attempts or cannot be automatically resolved, document the issues and request guidance.
