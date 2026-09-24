#!/usr/bin/env bash
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
. "$here/helpers.sh"

script="$here/../release-zip.sh"

work=$(mktemp -d "${TMPDIR:-/tmp}/wpsp-zip.XXXXXX")
trap 'rm -rf "$work"' EXIT

export GH_STUB_DIR="$work/stub"
export PATH="$here/stubs:$PATH"
mkdir -p "$GH_STUB_DIR/payload"
: >"$GH_STUB_DIR/calls.log"

payload="$GH_STUB_DIR/payload/stripe"
mkdir -p "$payload"/{data,includes,lib,src,views,languages}
for d in data includes lib views src; do printf 'x\n' >"$payload/$d/.keep"; done
cat >"$payload/stripe-checkout.php" <<'PHP'
<?php
/**
 * Version: 4.17.4
 */
	define( 'SIMPLE_PAY_VERSION', '4.17.4' );
PHP
printf '=== x ===\nStable tag: 4.17.4\n' >"$payload/readme.txt"
printf 'msgstr ""\n"Project-Id-Version: WP Simple Pay Lite 4.17.4\\n"\n' >"$payload/languages/stripe.pot"
printf 'uninstall\n' >"$payload/uninstall.php"
printf 'license\n' >"$payload/license.txt"

cat >"$GH_STUB_DIR/run-list.json" <<'JSON'
[{"databaseId": 900, "headBranch": "release/4.17.4", "headSha": "tipsha0000000000000000000000000000000", "conclusion": "success"}]
JSON
printf '{"object":{"sha":"tipsha0000000000000000000000000000000"}}\n' >"$GH_STUB_DIR/ref.json"
printf '{"artifacts":[{"name":"stripe-4.17.4","expired":false}]}\n' >"$GH_STUB_DIR/artifacts.json"

mkdir -p "$work/repo"
zip_path=$(LITE_REPO="$work/repo" bash "$script" 4.17.4 2>/dev/null | tail -1)

assert_eq "zip lands at the expected path" "$work/repo/build/stripe-4.17.4.zip" "$zip_path"
assert_ok "zip exists" test -f "$zip_path"
assert_contains "zip root is stripe/" "stripe/stripe-checkout.php" "$(unzip -Z1 "$zip_path")"

summary
