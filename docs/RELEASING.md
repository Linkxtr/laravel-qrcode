# Release Guide

This guide describes how to release new versions of the package. We use a manual release process triggered by GitHub Releases to ensure control over when updates are pushed to Packagist.

## Prerequisites

1. **Packagist Auto-Update Disabled**:

   - Go to your package on [Packagist](https://packagist.org/packages/linkxtr/laravel-qrcode).
   - Click "Edit".
   - Ensure "Auto-update" is **disabled** (or simply don't set up the webhook/service integration on GitHub settings > Webhooks unless you want it manual).
   - _Note_: If you have previously set up the Packagist service hook in GitHub, go to Settings > Webhooks and remove/disable the "Packagist" webhook.

2. **GitHub Secrets**:
   - The repository must have the following secrets configured for the automated action to work:
     - `PACKAGIST_USERNAME`: Your Packagist username.
     - `PACKAGIST_TOKEN`: Your Packagist API token (Profile -> Show API Token).

## Creating a Release

When you are ready to release a new version (e.g., `2.0.1`):

1. **Draft a New Release on GitHub**:

   - Go to the [Releases page](https://github.com/linkxtr/laravel-qrcode/releases).
   - Click "Draft a new release".

2. **Tag the Version**:

   - Create a new tag (e.g., `v2.0.1`).
   - ensure the target is the correct branch (`main` for v2.x, `1.x` for v1.x patches).

3. **Release Notes**:

   - Click "Generate release notes" or write your own summary of changes.

4. **Publish**:
   - Click "Publish release".

## How it Works

1. Publishing the release triggers the `.github/workflows/release.yml` workflow.
2. This workflow sends a POST request to Packagist to update the package.
3. Packagist picks up the new tag and lists the new version.

## Manual Update (Fallback)

If the GitHub Action fails, you can manually update the package on Packagist:

1. Log in to [Packagist](https://packagist.org/).
2. Go to your package page.
3. Click the "Update" button.
