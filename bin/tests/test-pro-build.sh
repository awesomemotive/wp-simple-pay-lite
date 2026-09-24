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

summary
