Prepare the release for version `$ARGUMENTS`. Follow these steps exactly:

## Step 1: Validate & Checkout

- The version is `$ARGUMENTS`. If no version was provided, ask the user for one before proceeding.
- Run `git checkout release/$ARGUMENTS`. If the branch does not exist, stop and tell the user — do NOT create it.

## Step 2: Bump Version

Update the version string to `$ARGUMENTS` in these 4 locations:

1. **`stripe-checkout.php`** — Plugin header `Version:` line (near line 8)
2. **`stripe-checkout.php`** — `define( 'SIMPLE_PAY_VERSION', '...' );` (near line 57)
3. **`package.json`** — `"version": "..."` field (near line 5). **Important**: npm/semver rejects 4-part versions. If the version has 4 parts (e.g., `X.Y.Z.N`), convert to `X.Y.Z+N` for `package.json` only (the `+N` is semver build metadata).
4. **`readme.txt`** — `Stable tag: ...` (near line 6)

Use the Edit tool for each replacement. Match the existing value and replace with `$ARGUMENTS` (except `package.json` which uses the semver-compatible format if needed).

## Step 3: Update Changelog

1. **Ask the user to provide the changelog entries** for this release (the Pro build already has them).

2. If the provided changelog contains multiple version headings, **merge all entries into a single block** under the `$ARGUMENTS` version heading. Combine and deduplicate entries by category (New, Update, Fix, Dev, etc.).

3. Insert the new changelog block in `readme.txt` immediately after the `== Changelog ==` line (with a blank line before the first existing entry):
   ```
   = $ARGUMENTS - {Month Day, Year} =

   * New: ...
   * Update: ...
   * Fix: ...
   ```
   Use today's date formatted as `Month Day, Year` (e.g., `March 12, 2026`).

## Step 4: Summary

Show the user a summary of all changes made:
- Branch checked out
- Files with version bumps
- Changelog entries added
