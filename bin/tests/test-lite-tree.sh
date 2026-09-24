#!/usr/bin/env bash
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
. "$here/helpers.sh"
. "$here/../lib/pro-build.sh"
. "$here/../lib/lite-tree.sh"

work=$(mktemp -d "${TMPDIR:-/tmp}/wpsp-tree.XXXXXX")
trap 'rm -rf "$work"' EXIT

# A payload carrying one new file, one changed file, and no copy of the
# repo's Lite-only extras.
payload="$work/payload/stripe"
mkdir -p "$payload"/{data,includes,lib,src,views,languages,vendor}
printf '<?php // autoload\n' >"$payload/vendor/autoload.php"
printf 'placeholder\n' >"$payload/data/.keep"
printf 'placeholder\n' >"$payload/includes/.keep"
printf 'placeholder\n' >"$payload/lib/.keep"
printf 'placeholder\n' >"$payload/views/.keep"
printf 'new from pro\n' >"$payload/src/Added.php"
printf 'changed by pro\n' >"$payload/src/Changed.php"
cat >"$payload/stripe-checkout.php" <<'PHP'
<?php
/**
 * Version: 4.17.4
 */
	define( 'SIMPLE_PAY_VERSION', '4.17.4' );
PHP
printf '=== x ===\nStable tag: 4.17.4\n' >"$payload/readme.txt"
printf 'pot 4.17.4\n' >"$payload/languages/stripe.pot"
printf 'uninstall\n' >"$payload/uninstall.php"
printf 'license\n' >"$payload/license.txt"

# A repo with an old version everywhere, a file Pro dropped, a Lite-only
# file inside a synced directory, an untracked vendor/, and a Crowdin .po
# beside the pot.
repo="$work/repo"
mkdir -p "$repo"/{data,includes,lib,src,views,languages,vendor,wordpress_org_assets}
printf 'placeholder\n' >"$repo/data/.keep"
printf 'placeholder\n' >"$repo/includes/.keep"
printf 'placeholder\n' >"$repo/lib/.keep"
printf 'placeholder\n' >"$repo/views/.keep"
printf 'stale\n' >"$repo/src/Changed.php"
printf 'pro dropped this\n' >"$repo/src/Removed.php"
printf 'lite only\n' >"$repo/src/LiteOnly.php"
printf 'do not touch\n' >"$repo/vendor/autoload.php"
printf 'crowdin\n' >"$repo/languages/pt_BR.po"
printf 'pot 4.17.3\n' >"$repo/languages/stripe.pot"
printf 'contributing\n' >"$repo/CONTRIBUTING.md"
printf '=== x ===\nStable tag: 4.17.3\n' >"$repo/readme.txt"
cat >"$repo/stripe-checkout.php" <<'PHP'
<?php
/**
 * Version: 4.17.3
 */
	define( 'SIMPLE_PAY_VERSION', '4.17.3' );
PHP
printf '{\n  "name": "wp-simple-pay-lite",\n  "version": "4.17.1"\n}\n' >"$repo/package.json"
printf 'uninstall\n' >"$repo/uninstall.php"
printf 'license\n' >"$repo/license.txt"

# Deletions are reported before anything is applied.
dels=$(lt_deletions "$payload" "$repo")
assert_contains "reports the dropped file" "src/Removed.php" "$dels"
assert_contains "reports the Lite-only file" "src/LiteOnly.php" "$dels"

lt_apply "$payload" "$repo"

assert_eq "adds a new file"   "new from pro"   "$(cat "$repo/src/Added.php")"
assert_eq "updates a changed file" "changed by pro" "$(cat "$repo/src/Changed.php")"
assert_fails "removes a dropped file" test -f "$repo/src/Removed.php"
assert_fails "removes a Lite-only file in a synced dir" test -f "$repo/src/LiteOnly.php"
assert_eq "leaves vendor alone" "do not touch" "$(cat "$repo/vendor/autoload.php")"
assert_eq "leaves Lite-only root files alone" "contributing" "$(cat "$repo/CONTRIBUTING.md")"
assert_ok "leaves wordpress_org_assets alone" test -d "$repo/wordpress_org_assets"
assert_eq "copies the pot" "pot 4.17.4" "$(cat "$repo/languages/stripe.pot")"
assert_eq "keeps a Crowdin po" "crowdin" "$(cat "$repo/languages/pt_BR.po")"
assert_eq "copies readme.txt" "Stable tag: 4.17.4" "$(sed -n '2p' "$repo/readme.txt")"

# Versions do not yet agree, because package.json has not been stamped.
assert_fails "assert catches an unstamped package.json" \
	lt_assert_versions "$repo" 4.17.4
pkg_msg=$(lt_assert_versions "$repo" 4.17.4 2>&1 || true)
assert_contains "assert names package.json" "package.json" "$pkg_msg"

lt_stamp_package_json "$repo" 4.17.4
assert_eq "stamps package.json" "4.17.4" \
	"$(node -p "require('$repo/package.json').version")"
assert_ok "versions agree after stamping" lt_assert_versions "$repo" 4.17.4

# Four-part versions use +N in package.json only.
lt_stamp_package_json "$repo" 4.17.0.1
assert_eq "stamps a four-part version" "4.17.0+1" \
	"$(node -p "require('$repo/package.json').version")"
lt_stamp_package_json "$repo" 4.17.4

# Applying twice changes nothing.
before=$(find "$repo" -type f -exec shasum {} \; | sort | shasum)
lt_apply "$payload" "$repo"
after=$(find "$repo" -type f -exec shasum {} \; | sort | shasum)
assert_eq "apply is idempotent" "$before" "$after"

# --- deletions must not fail silently -------------------------------------

# Finding 4: an rsync error during enumeration previously returned 0 with
# that directory's deletions missing, under-reporting the one signal that is
# supposed to be able to stop a release.
# An unreadable source is what openrsync actually errors on (rc 23); a
# destination that is a file it tolerates during --dry-run.
broken="$work/broken-payload"
mkdir -p "$broken"/{includes,lib,src,views}
assert_fails "deletion enumeration fails loudly" lt_deletions "$broken" "$repo"
broke_msg=$(lt_deletions "$broken" "$repo" 2>&1 || true)
assert_contains "names the directory it could not read" "data/" "$broke_msg"

# --- the payload must match the tree it is zipped from --------------------

# Finding 1: the release zip is built from a freshly resolved run, so
# without this the published bytes can be a build that was never synced.
assert_ok "matches right after a sync" lt_assert_matches "$payload" "$repo"

printf 'drifted\n' >"$repo/src/Changed.php"
assert_fails "catches a drifted file" lt_assert_matches "$payload" "$repo"
drift_msg=$(lt_assert_matches "$payload" "$repo" 2>&1 || true)
assert_contains "drift message names the file" "src/Changed.php" "$drift_msg"

printf 'changed by pro\n' >"$repo/src/Changed.php"
printf 'extra\n' >"$repo/src/Extra.php"
assert_fails "catches an extra file" lt_assert_matches "$payload" "$repo"
rm -f "$repo/src/Extra.php"
assert_ok "matches again once the tree is restored" lt_assert_matches "$payload" "$repo"

# A Crowdin .po in the tree is outside the sync set, so it must not count as
# drift.
printf 'crowdin\n' >"$repo/languages/de_DE.po"
assert_ok "a Crowdin po is not drift" lt_assert_matches "$payload" "$repo"
rm -f "$repo/languages/de_DE.po"

summary
