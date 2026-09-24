# shellcheck shell=bash
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
#
# Fails rather than under-reporting: this list is the only thing that can
# stop a release from dropping files, so an rsync error here must be loud.
lt_deletions() {
	local payload="$1" repo="$2" d out
	for d in $LT_DIRS; do
		out=$(rsync -a --delete --dry-run --itemize-changes \
			"$payload/$d/" "$repo/$d/" 2>&1) || {
			printf 'error: could not enumerate deletions for %s/: %s\n' \
				"$d" "$out" >&2
			return 1
		}
		printf '%s\n' "$out" | sed -n "s|^\*deleting  *|$d/|p"
	done
	return 0
}

# Echoes "<sha>  <path>" for every file in the sync set, so two trees can be
# compared without walking anything the sync does not own. vendor/ is left
# out because it is gitignored here and only ever exists in the payload.
lt_sync_manifest() {
	local root="$1" d f
	for d in $LT_DIRS; do
		( cd "$root" && find "$d" -type f -exec shasum {} + ) 2>/dev/null
	done
	for f in $LT_FILES $LT_POT; do
		( cd "$root" && shasum "$f" ) 2>/dev/null
	done
}

# Refuses when <payload> is not the build the tree was synced from.
#
# bin/release-zip.sh resolves a run of its own, which can be a redispatch
# built after the sync. Every version string would still agree, so this
# content comparison is what keeps the published zip and the merged tree
# from diverging silently.
lt_assert_matches() {
	local payload="$1" repo="$2" a b
	a=$(lt_sync_manifest "$payload" | sort -k2)
	b=$(lt_sync_manifest "$repo" | sort -k2)

	[ "$a" = "$b" ] && return 0

	printf 'error: the build payload does not match this working tree.\n' >&2
	printf 'the zip would ship code that was never synced here.\n' >&2
	printf 'differing paths (< payload, > tree):\n' >&2
	diff <(printf '%s\n' "$a") <(printf '%s\n' "$b") \
		| sed -n 's/^[<>] *[0-9a-f]\{40\}  */  /p' | sort -u | head -40 >&2
	return 1
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
