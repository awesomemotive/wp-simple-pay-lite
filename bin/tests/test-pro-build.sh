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
  {"databaseId": 900, "headBranch": "release/4.17.4", "headSha": "newsha00000000000000000000000000000000", "conclusion": "success", "createdAt": "2026-09-24T17:00:00Z"},
  {"databaseId": 800, "headBranch": "release/4.17.4", "headSha": "oldsha00000000000000000000000000000000", "conclusion": "success", "createdAt": "2026-09-24T15:00:00Z"},
  {"databaseId": 700, "headBranch": "release/4.17.4", "headSha": "failsha0000000000000000000000000000000", "conclusion": "failure", "createdAt": "2026-09-24T18:00:00Z"},
  {"databaseId": 600, "headBranch": "some/other-branch", "headSha": "othersha000000000000000000000000000000", "conclusion": "success", "createdAt": "2026-09-24T19:00:00Z"}
]
JSON

assert_eq "resolves newest successful run" \
	"900 newsha00000000000000000000000000000000 release/4.17.4" \
	"$(pb_resolve_run 4.17.4)"

assert_fails "fails when no run matches" pb_resolve_run 9.9.9

cat >"$stub_dir/run-list.json" <<'JSON'
[
  {"databaseId": 950, "headBranch": "hotfix/4.17.5", "headSha": "hotsha00000000000000000000000000000000", "conclusion": "success", "createdAt": "2026-09-24T12:00:00Z"}
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

# --- resolution asks for the right coordinates ----------------------------

# Finding 9: a --limit window drops legitimate release runs on a busy repo,
# so resolution must scope by branch instead of scanning recent runs.
: >"$stub_dir/calls.log"
pb_resolve_run 4.17.4 >/dev/null
calls=$(cat "$stub_dir/calls.log")
assert_contains "asks the Pro repo"     "--repo awesomemotive/wp-simple-pay-pro" "$calls"
assert_contains "asks the build workflow" "--workflow build-and-export.yml" "$calls"
assert_contains "scopes to the release branch" "--branch release/4.17.4" "$calls"
assert_contains "scopes to the hotfix branch"  "--branch hotfix/4.17.4"  "$calls"

# --- the staleness guard fails closed -------------------------------------

# Finding 3: a 503 must not read as "branch deleted", which would silently
# disable the guard the release depends on.
printf '{"object":{"sha":"tipsha0000000000000000000000000000000"}}\n' >"$stub_dir/ref.json"
: >"$stub_dir/api-fail"
assert_fails "a gh error does not skip the staleness check" \
	pb_assert_fresh tipsha0000000000000000000000000000000 release/4.17.4
err_msg=$(pb_assert_fresh tipsha0000000000000000000000000000000 release/4.17.4 2>&1 || true)
assert_contains "gh error message is not reassuring" "could not read" "$err_msg"
rm -f "$stub_dir/api-fail"
assert_ok "still fresh once gh recovers" \
	pb_assert_fresh tipsha0000000000000000000000000000000 release/4.17.4


# --- download -------------------------------------------------------------

# Builds a minimal but structurally correct Lite payload at $1 for version $2.
make_payload() {
	local root="$1" version="$2" d
	for d in data includes lib src views languages vendor; do
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
	printf '<?php // autoload\n' >"$root/vendor/autoload.php"
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

# --- download asks for the right artifact ---------------------------------

# Finding 8: nothing previously tested the artifact name, so a typo in it
# would have passed every assertion and failed only in production.
printf 'stripe-4.17.4\n' >"$stub_dir/artifact-name"
: >"$stub_dir/calls.log"
printf '{"artifacts":[{"name":"stripe-4.17.4","expired":false}]}\n' >"$stub_dir/artifacts.json"
named=$(mktemp -d "${TMPDIR:-/tmp}/wpsp-named.XXXXXX")
pb_download 900 4.17.4 "$named" >/dev/null
assert_contains "downloads stripe-<version>" "--name stripe-4.17.4" "$(cat "$stub_dir/calls.log")"
rm -rf "$named" "$stub_dir/artifact-name"

# --- payload completeness -------------------------------------------------

# Finding 2: vendor/ is in the shipped zip and stripe-checkout.php requires
# vendor/autoload.php, so an artifact without it fatals on activation.
make_payload "$bad/novendor" 4.17.4
rm -rf "$bad/novendor/vendor"
assert_fails "rejects a payload with no vendor" pb_validate_payload "$bad/novendor" 4.17.4

make_payload "$bad/noautoload" 4.17.4
rm -f "$bad/noautoload/vendor/autoload.php"
assert_fails "rejects a payload with no autoloader" pb_validate_payload "$bad/noautoload" 4.17.4

# Finding 5: these two are copied by lt_apply after the rsyncs, so a payload
# missing them fails halfway through and leaves a partial tree.
make_payload "$bad/nouninstall" 4.17.4
rm -f "$bad/nouninstall/uninstall.php"
assert_fails "rejects a payload with no uninstall.php" pb_validate_payload "$bad/nouninstall" 4.17.4

make_payload "$bad/nolicense" 4.17.4
rm -f "$bad/nolicense/license.txt"
assert_fails "rejects a payload with no license.txt" pb_validate_payload "$bad/nolicense" 4.17.4


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
