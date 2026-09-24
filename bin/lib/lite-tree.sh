# Operations on the Lite working tree. Sourced, not executed.
# Requires bin/lib/pro-build.sh for pb_npm_version.
#
# See docs/superpowers/specs/2026-09-24-lite-sync-from-pro-design.md

# Directories the Pro build owns outright. rsync --delete is confined to
# these, so a file dropped in Pro disappears here and nothing Lite-only is
# ever at risk.
LT_DIRS="data includes lib src views"

# Loose files the build overwrites.
LT_FILES="readme.txt stripe-checkout.php uninstall.php license.txt"

# Copied as a single file, not as an rsync of languages/, so Crowdin .po
# and .mo files landing later are not deleted.
LT_POT="languages/stripe.pot"

# Echoes porcelain status for everything the sync writes, so uncommitted
# work cannot be silently overwritten.
lt_dirty() {
	local repo="$1"
	# shellcheck disable=SC2086
	git -C "$repo" status --porcelain -- $LT_DIRS $LT_FILES "$LT_POT" package.json
}

# Echoes the paths that applying <payload> would delete from <repo>.
lt_deletions() {
	local payload="$1" repo="$2" d
	for d in $LT_DIRS; do
		rsync -a --delete --dry-run --itemize-changes \
			"$payload/$d/" "$repo/$d/" 2>/dev/null \
			| sed -n "s|^\*deleting  *|$d/|p"
	done
}

# Makes <repo> match <payload> across the sync set. vendor/ is untouched.
lt_apply() {
	local payload="$1" repo="$2" d f
	for d in $LT_DIRS; do
		rsync -a --delete "$payload/$d/" "$repo/$d/"
	done
	for f in $LT_FILES; do
		cp "$payload/$f" "$repo/$f"
	done
	cp "$payload/$LT_POT" "$repo/$LT_POT"
}

# The build artifact cannot carry package.json, because Lite's gruntfile
# distFiles excludes it. Stamping it here is what closes the gap that left
# package.json at 4.17.1 while the rest of the tree said 4.17.3.
lt_stamp_package_json() {
	local repo="$1" version="$2" npm_version
	npm_version=$(pb_npm_version "$version")
	( cd "$repo" && npm pkg set version="$npm_version" >/dev/null )
}

# All four version locations must agree before a release is committed.
lt_assert_versions() {
	local repo="$1" version="$2" npm_version header define stable pkg fail=0
	npm_version=$(pb_npm_version "$version")

	header=$(sed -n 's/^ \* Version: \(.*\)$/\1/p' "$repo/stripe-checkout.php")
	define=$(sed -n "s/.*SIMPLE_PAY_VERSION', '\([^']*\)'.*/\1/p" "$repo/stripe-checkout.php")
	stable=$(sed -n 's/^Stable tag: \(.*\)$/\1/p' "$repo/readme.txt")
	pkg=$(node -p "require('$repo/package.json').version")

	if [ "$header" != "$version" ]; then
		printf 'error: stripe-checkout.php header says %s, expected %s\n' \
			"$header" "$version" >&2
		fail=1
	fi
	if [ "$define" != "$version" ]; then
		printf 'error: SIMPLE_PAY_VERSION says %s, expected %s\n' \
			"$define" "$version" >&2
		fail=1
	fi
	if [ "$stable" != "$version" ]; then
		printf 'error: readme.txt stable tag says %s, expected %s\n' \
			"$stable" "$version" >&2
		fail=1
	fi
	if [ "$pkg" != "$npm_version" ]; then
		printf 'error: package.json version says %s, expected %s\n' \
			"$pkg" "$npm_version" >&2
		fail=1
	fi

	return "$fail"
}
