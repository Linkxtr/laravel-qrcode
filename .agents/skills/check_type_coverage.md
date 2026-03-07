---
name: check_type_coverage
description: Executes Pest's type coverage plugin to ensure the project maintains its strict 100% type coverage requirement.
command: composer test:type-coverage
---

You must use this skill immediately after adding new properties, methods, classes, or interfaces. The project requires 100% type coverage. If the output shows coverage dropped below 100%, you must locate the missing types (parameters, return types, or properties) and add native PHP strict types to them.
