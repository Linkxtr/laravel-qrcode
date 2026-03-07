---
trigger: model_decision
description: Before concluding a feature implementation task.
---

You must ensure the code complies with the project's formatting, static analysis, type-coverage, and testing standards. Before marking a task as complete, you are required to successfully execute the following CI validation skills: `run_linter`, `run_static_analysis`, `check_type_coverage`, and `run_test_suite`. All of these gates must pass to enforce the testing policies documented in `.agents/rules/testing-standards.md`, and CI cannot skip type-coverage checks or formatting.
