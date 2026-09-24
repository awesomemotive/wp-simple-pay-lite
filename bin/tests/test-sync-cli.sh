#!/usr/bin/env bash
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
. "$here/helpers.sh"

script="$here/../sync-from-pro.sh"

work=$(mktemp -d "${TMPDIR:-/tmp}/wpsp-cli.XXXXXX")
trap 'rm -rf "$work"' EXIT

export GH_STUB_DIR="$work/stub"
export PATH="$here/stubs:$PATH"
mkdir -p "$GH_STUB_DIR/payload"
: >"$GH_STUB_DIR/calls.log"

payload="$GH_STUB_DIR/payload/stripe"
mkdir -p "$payload"/{data,includes,lib,src,views,languages}
for d in data includes lib views; do printf 'x\n' >"$payload/$d/.keep"; done
printf 'from pro\n' >"$payload/src/App.php"
cat >"$payload/stripe-checkout.php" <<'PHP'
<?php
/**
 * Version: 4.17.4
 */
	define( 'SIMPLE_PAY_VERSION', '4.17.4' );
PHP
printf '=== x ===\nStable tag: 4.17.4\n\n== Changelog ==\n\n= Stripe Payment Forms 4.17.4 - September 24, 2026 =\n\n* New: A thing.\n' >"$payload/readme.txt"
printf 'msgstr ""\n"Project-Id-Version: WP Simple Pay Lite 4.17.4\\n"\n' >"$payload/languages/stripe.pot"
printf 'uninstall\n' >"$payload/uninstall.php"
printf 'license\n' >"$payload/license.txt"

cat >"$GH_STUB_DIR/run-list.json" <<'JSON'
[{"databaseId": 900, "headBranch": "release/4.17.4", "headSha": "tipsha0000000000000000000000000000000", "conclusion": "success"}]
JSON
printf '{"object":{"sha":"tipsha0000000000000000000000000000000"}}\n' >"$GH_STUB_DIR/ref.json"
printf '{"artifacts":[{"name":"stripe-4.17.4","expired":false}]}\n' >"$GH_STUB_DIR/artifacts.json"

# Builds a fresh git repo standing in for the Lite checkout.
make_repo() {
	local repo="$1" d
	mkdir -p "$repo"/{data,includes,lib,src,views,languages}
	for d in data includes lib views; do printf 'x\n' >"$repo/$d/.keep"; done
	printf 'stale\n' >"$repo/src/App.php"
	printf 'lite only\n' >"$repo/CONTRIBUTING.md"
	printf 'pot old\n' >"$repo/languages/stripe.pot"
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
	git -C "$repo" init -q
	git -C "$repo" add -A
	git -C "$repo" -c user.email=t@t -c user.name=t commit -qm init
}

# Usage errors exit 2.
LITE_REPO="$work/unused" bash "$script" >/dev/null 2>&1
assert_eq "no version exits 2" "2" "$?"
LITE_REPO="$work/unused" bash "$script" v4.17.4 >/dev/null 2>&1
assert_eq "bad version exits 1" "1" "$?"

# Dry run writes nothing.
make_repo "$work/dry"
out=$(LITE_REPO="$work/dry" bash "$script" 4.17.4 --dry-run 2>&1)
assert_eq "dry run leaves the tree alone" "stale" "$(cat "$work/dry/src/App.php")"
assert_contains "dry run mentions the run" "900" "$out"

# A real sync applies, stamps, and asserts.
make_repo "$work/real"
LITE_REPO="$work/real" bash "$script" 4.17.4 >/dev/null 2>&1
assert_eq "sync exit status" "0" "$?"
assert_eq "sync applied src"  "from pro" "$(cat "$work/real/src/App.php")"
assert_eq "sync stamped package.json" "4.17.4" \
	"$(node -p "require('$work/real/package.json').version")"
assert_eq "sync copied readme" "Stable tag: 4.17.4" "$(sed -n '2p' "$work/real/readme.txt")"
assert_eq "sync kept Lite-only files" "lite only" "$(cat "$work/real/CONTRIBUTING.md")"

# A dirty sync set is refused unless forced.
make_repo "$work/dirty"
printf 'uncommitted\n' >>"$work/dirty/src/App.php"
LITE_REPO="$work/dirty" bash "$script" 4.17.4 >/dev/null 2>&1
assert_eq "dirty tree is refused" "1" "$?"
assert_contains "dirty tree still has the edit" "uncommitted" "$(cat "$work/dirty/src/App.php")"
LITE_REPO="$work/dirty" bash "$script" 4.17.4 --force >/dev/null 2>&1
assert_eq "force overrides the dirty check" "0" "$?"

# A stale build is refused, and --run bypasses the check.
make_repo "$work/stale"
printf '{"object":{"sha":"movedsha00000000000000000000000000000"}}\n' >"$GH_STUB_DIR/ref.json"
LITE_REPO="$work/stale" bash "$script" 4.17.4 >/dev/null 2>&1
assert_eq "stale build is refused" "1" "$?"
LITE_REPO="$work/stale" bash "$script" 4.17.4 --run 900 >/dev/null 2>&1
assert_eq "--run bypasses staleness" "0" "$?"

summary
