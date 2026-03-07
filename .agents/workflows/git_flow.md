---
description: Applying Git Flow approach
---

1- Prepare: make sure you have the latest updates by running `git pull` on the `main` branch
2- Feature init: create and check out a new branch with a proper name with prefix `feature/`, `hotfix/`, `release/` or `docs/`
3- Feature development: run /feature_development
4- Commit: check the dirty files and add commits with proper message
5- Push and publish: run `gpsup` to publish and push the branch
6- Open a PR: run `gh pr create --base main` to open a PR
