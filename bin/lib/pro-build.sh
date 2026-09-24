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

# Echoes "<run_id> <head_sha> <head_branch>" for the newest successful
# build-and-export run on release/<version> or hotfix/<version>.
#
# PB_V is passed through the environment because gh's --jq takes no --arg.
pb_resolve_run() {
	local version="$1" out
	out=$(PB_V="$version" gh run list \
		--repo "$PRO_REPO" \
		--workflow "$PRO_WORKFLOW" \
		--limit 100 \
		--json databaseId,headBranch,headSha,conclusion \
		--jq '[ .[]
			| select(.conclusion == "success")
			| select(.headBranch == "release/" + $ENV.PB_V
			      or .headBranch == "hotfix/" + $ENV.PB_V)
			] | .[0] | select(. != null)
			  | "\(.databaseId) \(.headSha) \(.headBranch)"') || {
		pb_die "could not list $PRO_WORKFLOW runs on $PRO_REPO"
		return 1
	}

	if [ -z "$out" ]; then
		pb_die "no successful $PRO_WORKFLOW run on release/$version or hotfix/$version. Dispatch the build on Pro, or pass --run <id>."
		return 1
	fi

	printf '%s\n' "$out"
}

# Echoes the current tip SHA of a branch on the Pro repo, or nothing when
# the branch is gone.
pb_branch_tip() {
	gh api "repos/$PRO_REPO/git/ref/heads/$1" --jq '.object.sha' 2>/dev/null
}

# Refuses a build whose branch has moved since it ran, so a release never
# ships a tree that predates the last commit on its release branch.
pb_assert_fresh() {
	local run_sha="$1" branch="$2" tip
	tip=$(pb_branch_tip "$branch")

	if [ -z "$tip" ]; then
		printf 'note: %s no longer exists on %s, skipping the staleness check\n' \
			"$branch" "$PRO_REPO" >&2
		return 0
	fi

	[ "$tip" = "$run_sha" ] && return 0

	pb_die "build is stale: $branch is now at $(printf '%s' "$tip" | cut -c1-7) but the run built $(printf '%s' "$run_sha" | cut -c1-7). Redispatch the build on Pro, or pass --run <id> to use it anyway."
}
