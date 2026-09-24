#!/usr/bin/env bash
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
. "$here/helpers.sh"
. "$here/../lib/pro-build.sh"

# pb_validate_version
assert_ok    "accepts X.Y.Z"          pb_validate_version 4.17.4
assert_ok    "accepts X.Y.Z.N"        pb_validate_version 4.17.0.1
assert_fails "rejects two-part"       pb_validate_version 4.17
assert_fails "rejects leading v"      pb_validate_version v4.17.4
assert_fails "rejects trailing space" pb_validate_version "4.17.4 "
assert_fails "rejects non-numeric"    pb_validate_version 4.17.x
assert_fails "rejects empty"          pb_validate_version ""

# pb_npm_version
assert_eq "three-part unchanged" "4.17.4"   "$(pb_npm_version 4.17.4)"
assert_eq "four-part uses +"     "4.17.0+1" "$(pb_npm_version 4.17.0.1)"

# --- run resolution -------------------------------------------------------

stub_dir=$(mktemp -d "${TMPDIR:-/tmp}/wpsp-gh-stub.XXXXXX")
trap 'rm -rf "$stub_dir"' EXIT
export GH_STUB_DIR="$stub_dir"
export PATH="$here/stubs:$PATH"
: >"$stub_dir/calls.log"

# Newest first, the way `gh run list` returns them. Two successful runs sit
# on release/4.17.4 (a redispatch); the newer must win.
cat >"$stub_dir/run-list.json" <<'JSON'
[
  {"databaseId": 900, "headBranch": "release/4.17.4", "headSha": "newsha00000000000000000000000000000000", "conclusion": "success"},
  {"databaseId": 800, "headBranch": "release/4.17.4", "headSha": "oldsha00000000000000000000000000000000", "conclusion": "success"},
  {"databaseId": 700, "headBranch": "release/4.17.4", "headSha": "failsha0000000000000000000000000000000", "conclusion": "failure"},
  {"databaseId": 600, "headBranch": "some/other-branch", "headSha": "othersha000000000000000000000000000000", "conclusion": "success"}
]
JSON

assert_eq "resolves newest successful run" \
	"900 newsha00000000000000000000000000000000 release/4.17.4" \
	"$(pb_resolve_run 4.17.4)"

assert_fails "fails when no run matches" pb_resolve_run 9.9.9

cat >"$stub_dir/run-list.json" <<'JSON'
[
  {"databaseId": 950, "headBranch": "hotfix/4.17.5", "headSha": "hotsha00000000000000000000000000000000", "conclusion": "success"}
]
JSON

assert_eq "resolves a hotfix branch" \
	"950 hotsha00000000000000000000000000000000 hotfix/4.17.5" \
	"$(pb_resolve_run 4.17.5)"

# --- staleness ------------------------------------------------------------

printf '{"object":{"sha":"tipsha0000000000000000000000000000000"}}\n' >"$stub_dir/ref.json"

assert_ok "fresh when tip matches the run" \
	pb_assert_fresh tipsha0000000000000000000000000000000 release/4.17.4

assert_fails "stale when the branch moved" \
	pb_assert_fresh oldsha00000000000000000000000000000000 release/4.17.4

stale_msg=$(pb_assert_fresh oldsha00000000000000000000000000000000 release/4.17.4 2>&1 || true)
assert_contains "stale message names --run" "--run" "$stale_msg"

rm -f "$stub_dir/ref.json"
assert_ok "missing branch is not fatal" \
	pb_assert_fresh oldsha00000000000000000000000000000000 release/gone

# --- download -------------------------------------------------------------

# Builds a minimal but structurally correct Lite payload at $1 for version $2.
make_payload() {
	local root="$1" version="$2" d
	for d in data includes lib src views languages; do
		mkdir -p "$root/$d"
		printf 'placeholder\n' >"$root/$d/.keep"
	done
	cat >"$root/stripe-checkout.php" <<PHP
<?php
/**
 * Plugin Name: WP Simple Pay Lite
 * Version: $version
 */
	define( 'SIMPLE_PAY_VERSION', '$version' );
PHP
	cat >"$root/readme.txt" <<TXT
=== Stripe Payment Forms ===
Stable tag: $version

== Changelog ==

= Stripe Payment Forms $version - September 24, 2026 =

* New: A thing.
* Fix: Another thing.

= Stripe Payment Forms 4.17.3 - June 16, 2026 =

* New: An older thing.
TXT
	printf 'msgstr ""\n"Project-Id-Version: WP Simple Pay Lite %s\\n"\n' "$version" \
		>"$root/languages/stripe.pot"
	printf 'uninstall\n' >"$root/uninstall.php"
	printf 'license\n' >"$root/license.txt"
}

mkdir -p "$stub_dir/payload"
make_payload "$stub_dir/payload/stripe" 4.17.4

printf '{"artifacts":[{"name":"stripe-4.17.4","expired":false}]}\n' >"$stub_dir/artifacts.json"

dl=$(mktemp -d "${TMPDIR:-/tmp}/wpsp-dl.XXXXXX")
payload=$(pb_download 900 4.17.4 "$dl")
assert_eq "download echoes the payload root" "$dl/stripe" "$payload"
assert_ok "download produced stripe-checkout.php" test -f "$payload/stripe-checkout.php"

printf '{"artifacts":[{"name":"stripe-4.17.4","expired":true}]}\n' >"$stub_dir/artifacts.json"
assert_fails "refuses an expired artifact" pb_download 900 4.17.4 "$dl/expired"

printf '{"artifacts":[{"name":"stripe-9.9.9","expired":false}]}\n' >"$stub_dir/artifacts.json"
assert_fails "refuses a missing artifact" pb_download 900 4.17.4 "$dl/missing"

# --- payload validation ---------------------------------------------------

assert_ok "accepts a good payload" pb_validate_payload "$payload" 4.17.4

# A --run pointing at a build for another version must be caught here.
assert_fails "rejects a version mismatch" pb_validate_payload "$payload" 4.17.5

bad=$(mktemp -d "${TMPDIR:-/tmp}/wpsp-bad.XXXXXX")

make_payload "$bad/pro" 4.17.4
mkdir -p "$bad/pro/includes/pro"
assert_fails "rejects a Pro tree" pb_validate_payload "$bad/pro" 4.17.4

make_payload "$bad/nosrc" 4.17.4
rm -rf "$bad/nosrc/src"
assert_fails "rejects a missing directory" pb_validate_payload "$bad/nosrc" 4.17.4

make_payload "$bad/emptysrc" 4.17.4
rm -f "$bad/emptysrc/src/.keep"
assert_fails "rejects an empty directory" pb_validate_payload "$bad/emptysrc" 4.17.4

make_payload "$bad/badpot" 4.17.4
printf 'msgstr ""\n"Project-Id-Version: WP Simple Pay Lite 4.17.3\\n"\n' \
	>"$bad/badpot/languages/stripe.pot"
assert_fails "rejects a stale pot" pb_validate_payload "$bad/badpot" 4.17.4

make_payload "$bad/badtag" 4.17.4
sed -i.bak 's/^Stable tag: .*/Stable tag: 4.17.3/' "$bad/badtag/readme.txt"
assert_fails "rejects a stale stable tag" pb_validate_payload "$bad/badtag" 4.17.4

make_payload "$bad/baddefine" 4.17.4
sed -i.bak "s/SIMPLE_PAY_VERSION', '4.17.4'/SIMPLE_PAY_VERSION', '4.17.3'/" \
	"$bad/baddefine/stripe-checkout.php"
assert_fails "rejects a stale define" pb_validate_payload "$bad/baddefine" 4.17.4

# --- changelog extraction -------------------------------------------------

block=$(pb_changelog_block "$payload/readme.txt" 4.17.4)
assert_contains "block has the heading" "= Stripe Payment Forms 4.17.4 -" "$block"
assert_contains "block has an entry" "* New: A thing." "$block"
case "$block" in
	*"4.17.3"*) fail "block stops at the next release" "it included 4.17.3" ;;
	*) ok "block stops at the next release" ;;
esac

rm -rf "$dl" "$bad"

summary
