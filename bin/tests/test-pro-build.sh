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

summary
