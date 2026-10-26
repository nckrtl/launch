# Dependency policy

Launch uses Composer (`composer.lock`) and Bun (`bun.lock`). Commit both lockfiles. Do not add npm, Yarn, or pnpm lockfiles.

## Routine updates wait seven days

- Composer: `minimum-release-age` in `vet.json` is 7 days.
- Bun: `install.minimumReleaseAge` in `bunfig.toml` is `604800` seconds.

Those settings apply only when a version is resolved. `composer install` and `bun install --frozen-lockfile` use the lockfiles as they are. `vet.json` has no trust baseline, so Vet prints a notice on install. You can ignore it.

`package.json` `overrides` and `resolutions` pin patched transitive versions. Remove a pin when the upstream range includes the fix.

## Security fixes ship at once

A known advisory does not wait seven days. Link it in the pull request. If the fixed release is younger than seven days, add only that package to `minimum-release-age-exclude` in `vet.json`, or to `install.minimumReleaseAgeExcludes` in `bunfig.toml`. Remove the exclusion when the release is seven days old. Do not silence an advisory that has no fix. Do not run `audit fix`.

`hardimpactdev/maintainer` v0.1.0 was released on 2026-10-26 at 05:49:59 UTC, so it is excluded from the Composer age check. **Remove `hardimpactdev/maintainer` from `minimum-release-age-exclude` in `vet.json` on 2026-11-02, after 05:49:59 UTC.** Remove the key too if its list is empty. Keep `minimum-release-age: 7`.

## Commands

`scripts/dependencies` is the standard wrapper for [`hardimpactdev/maintainer`](https://github.com/hardimpactdev/maintainer). It installs Composer dependencies without scripts when `vendor/bin/maintainer` is missing, then forwards every argument. The package README defines `routine`, `detect`, and `package`.

`composer check` is the project check. `composer audit:dependencies` stays separate from that check.
