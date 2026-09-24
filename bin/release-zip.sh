#!/usr/bin/env bash
#
# Rezip the Lite build artifact for a version into build/stripe-<v>.zip,
# for attaching to a GitHub Release. These are the bytes the Pro build
# produced, not a local rebuild.
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
usage: bin/release-zip.sh <version> [--run ID] [--allow-tree-mismatch]

  <version>              X.Y.Z or X.Y.Z.N, for example 4.17.4
  --run ID               use this Pro run instead of resolving one, and
                         skip the staleness check
  --allow-tree-mismatch  zip a build that does not match this working
                         tree. Only for rebuilding an older release's zip
                         from a checkout that has moved on
USAGE
	exit 2
}

version=""
run_id=""
allow_mismatch=0

while [ $# -gt 0 ]; do
	case "$1" in
		--run)
			run_id="${2-}"
			[ -n "$run_id" ] || usage
			shift 2
			;;
		--allow-tree-mismatch) allow_mismatch=1; shift ;;
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

if [ -z "$run_id" ]; then
	resolved=$(pb_resolve_run "$version")
	read -r run_id run_sha run_branch <<<"$resolved"
	pb_assert_fresh "$run_sha" "$run_branch"
fi

tmp=$(mktemp -d "${TMPDIR:-/tmp}/wpsp-lite-zip.XXXXXX")
trap 'rm -rf "$tmp"' EXIT

payload=$(pb_download "$run_id" "$version" "$tmp")
pb_validate_payload "$payload" "$version"

# This script resolves a run of its own, which can be a redispatch built
# after the sync. Every version string would still agree, so comparing
# contents is the only thing that keeps the published zip and the reviewed
# tree from diverging.
if [ "$allow_mismatch" -eq 0 ]; then
	if ! lt_assert_matches "$payload" "$repo"; then
		printf 'pass --run <id> to pick the build this tree was synced from, or --allow-tree-mismatch to override.\n' >&2
		exit 1
	fi
else
	printf 'warning: not checking the payload against the working tree\n' >&2
fi

mkdir -p "$repo/build"
out="$repo/build/stripe-$version.zip"
rm -f "$out"
( cd "$(dirname "$payload")" && zip -q -r -X "$out" stripe )

printf 'built from Pro run %s\n' "$run_id" >&2
printf '%s\n' "$out"
