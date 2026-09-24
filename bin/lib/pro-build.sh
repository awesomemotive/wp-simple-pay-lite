# Helpers for pulling the Lite build artifact out of the Pro repo.
# Sourced, not executed.
#
# See docs/superpowers/specs/2026-09-24-lite-sync-from-pro-design.md

PRO_REPO="${PRO_REPO:-awesomemotive/wp-simple-pay-pro}"
PRO_WORKFLOW="${PRO_WORKFLOW:-build-and-export.yml}"

pb_die() {
	printf 'error: %s\n' "$*" >&2
	return 1
}

# Accepts X.Y.Z and X.Y.Z.N, nothing else. A stray 'v' or space would
# otherwise become an artifact name that never matches anything.
pb_validate_version() {
	printf '%s' "${1-}" | grep -qE '^[0-9]+\.[0-9]+\.[0-9]+(\.[0-9]+)?$' && return 0
	pb_die "version '${1-}' is not X.Y.Z or X.Y.Z.N"
}

# npm rejects four-part versions, so X.Y.Z.N becomes X.Y.Z+N. The +N is
# semver build metadata. Three-part versions pass through untouched.
pb_npm_version() {
	printf '%s\n' "$1" | sed -E 's/^([0-9]+\.[0-9]+\.[0-9]+)\.([0-9]+)$/\1+\2/'
}
