# Dependency policy

Launch uses Composer (`composer.lock`) and Bun (`bun.lock`). Commit both lockfiles. Do not add npm, Yarn or pnpm lockfiles.

## Routine updates wait seven days

- Composer: the `laravel/vet` plugin skips releases younger than 7 days during resolution (`minimum-release-age` in `vet.json`).
- Bun: `bunfig.toml` sets `install.minimumReleaseAge = 604800` (7 days).

`vet.json` holds no trust baseline, so Vet prints a notice on install; you can ignore it. Both settings apply only when a version is resolved. `composer install` and `bun install --frozen-lockfile` use the lockfiles as they are.

Update only the packages you need (`composer update vendor/package --with-dependencies`, `bun update package`). Review the lockfile diff and run `composer check`.

`package.json` `overrides` and `resolutions` pin patched transitive versions (`vitest`, `@vitest/mocker`, `valibot`). Remove a pin when the upstream range includes the fix.

## Security fixes ship at once

A fix for a known CVE or GHSA advisory does not wait seven days.

1. Link the advisory in the pull request.
2. If the fixed release is younger than seven days, add only its exact package name to `minimum-release-age-exclude` in `vet.json`, or to `install.minimumReleaseAgeExcludes` in `bunfig.toml`.
3. Run `composer audit:dependencies` and `composer check`, and get a review.
4. Remove the exclusion when the release is seven days old.

Do not silence an advisory that has no fix. Record it in the pull request with the advisory link and the reason the risk is acceptable.

## Audits

```sh
composer audit:dependencies   # composer audit --locked, then bun audit
```

The audits are separate from `composer check`, so a new upstream advisory does not block unrelated work.
