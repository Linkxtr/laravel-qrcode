---
description: Feature Development workflow
---

1- Analyze & planning: Read the issue, analyze the affected files, and write a detailed plan for implementation
2- Ask me for confirmation on the plan before starting implementation.
3- TDD Approach: Follow the TDD approach in implementation: write Pest tests according to `testing-standards.md`, then write the PHP code following `strict-typing.md`.
4- Refactor & Style: Execute the `run_linter` skill to format the code.
5- Static Analysis: Execute the `run_static_analysis` and `check_type_coverage` skills. If errors occur, the agent must fix them autonomously.
6- Verify: Execute `run_test_suite`.
7- Review: Execute `code-review.md` and follow the feedback as you deem appropriate.
