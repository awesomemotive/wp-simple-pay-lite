#!/usr/bin/env bash
#
# Sync the Lite working tree from the Lite build artifact that Pro's CI
# produced for a version. The build lives in Pro; this only consumes it,
# so the committed tree is the tree that was built and tested.
#
# See docs/superpowers/specs/2026-09-24-lite-sync-from-pro-design.md
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
repo="${LITE_REPO:-$(cd "$here/.." && pwd)}"

# shellcheck source=bin/lib/pro-build.sh
. "$here/lib/pro-build.sh"
# shellcheck source=bin/lib/lite-tree.sh
. "$here/lib/lite-tree.sh"

usage() {
	cat >&2 <<'USAGE'
usage: bin/sync-from-pro.sh <version> [--run ID] [--dry-run] [--force]
                            [--allow-deletions]

  <version>          X.Y.Z or X.Y.Z.N, for example 4.17.4
  --run ID           use this Pro run instead of resolving one, and skip
                     the staleness check
  --dry-run          print what would change, then exit without writing
  --force            apply even when the sync set has uncommitted changes
  --allow-deletions  apply even though the sync removes files. Review the
                     reported paths first: a path that looks Lite-only
                     means the build is wrong, not that you should proceed
USAGE
	exit 2
}

version=""
run_id=""
dry_run=0
force=0
allow_deletions=0

while [ $# -gt 0 ]; do
	case "$1" in
		--run)
			run_id="${2-}"
			[ -n "$run_id" ] || usage
			shift 2
			;;
		--dry-run) dry_run=1; shift ;;
		--force) force=1; shift ;;
		--allow-deletions) allow_deletions=1; shift ;;
		-h|--help) usage ;;
		-*) printf 'error: unknown option %s\n' "$1" >&2; usage ;;
		*)
			[ -z "$version" ] || usage
			version="$1"
			shift
			;;
	esac
done

[ -n "$version" ] || usage
pb_validate_version "$version"

if [ "$force" -eq 0 ] && [ "$dry_run" -eq 0 ]; then
	dirty=$(lt_dirty "$repo")
	if [ -n "$dirty" ]; then
		printf 'error: uncommitted changes inside the sync set:\n%s\n' "$dirty" >&2
		printf 'commit or stash them, or re-run with --force.\n' >&2
		exit 1
	fi
fi

if [ -n "$run_id" ]; then
	printf 'using Pro run %s, staleness check skipped\n' "$run_id"
else
	resolved=$(pb_resolve_run "$version")
	read -r run_id run_sha run_branch <<<"$resolved"
	printf 'resolved Pro run %s from %s at %s\n' \
		"$run_id" "$run_branch" "$(printf '%s' "$run_sha" | cut -c1-7)"
	pb_assert_fresh "$run_sha" "$run_branch"
fi

tmp=$(mktemp -d "${TMPDIR:-/tmp}/wpsp-lite-sync.XXXXXX")
trap 'rm -rf "$tmp"' EXIT

payload=$(pb_download "$run_id" "$version" "$tmp")
pb_validate_payload "$payload" "$version"
printf 'payload validated at %s\n' "$version"

deletions=$(lt_deletions "$payload" "$repo")
if [ -n "$deletions" ]; then
	printf '\nthis sync DELETES %d path(s):\n' \
		"$(printf '%s\n' "$deletions" | wc -l | tr -d ' ')"
	printf '%s\n' "$deletions"
fi

if [ "$dry_run" -eq 1 ]; then
	for d in $LT_DIRS; do
		printf '\n=== %s ===\n' "$d"
		rsync -a --delete --dry-run --itemize-changes "$payload/$d/" "$repo/$d/"
	done
	for f in $LT_FILES $LT_POT; do
		if cmp -s "$payload/$f" "$repo/$f"; then
			printf 'unchanged    %s\n' "$f"
		else
			printf 'would update %s\n' "$f"
		fi
	done
	printf '\ndry run, nothing written\n'
	exit 0
fi

# Deletions are the one change the sync cannot self-check: dropping a file
# is correct when Pro removed it and wrong when the path is Lite-only, and
# only a person can tell those apart. So it is a gate in code, not advice.
if [ -n "$deletions" ] && [ "$allow_deletions" -eq 0 ]; then
	printf '\nerror: refusing to apply a sync that deletes files.\n' >&2
	printf 'review the paths above. If Pro really dropped them, re-run with --allow-deletions.\n' >&2
	exit 1
fi

lt_apply "$payload" "$repo"
lt_stamp_package_json "$repo" "$version"
lt_assert_versions "$repo" "$version"

printf '\nsynced from Pro run %s\n' "$run_id"
git -C "$repo" status --short
