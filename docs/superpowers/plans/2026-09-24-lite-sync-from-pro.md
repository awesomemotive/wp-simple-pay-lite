# Lite Sync From Pro Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the hand-merge in Lite's release process with a script that applies Pro's Lite build artifact to the working tree, plus two slash commands that carry a release from branch to published GitHub Release.

**Architecture:** Two sourceable shell libraries hold all the logic (`bin/lib/pro-build.sh` for talking to Pro, `bin/lib/lite-tree.sh` for mutating this repo), and two thin CLIs wrap them (`bin/sync-from-pro.sh`, `bin/release-zip.sh`). Tests are plain shell scripts that put a fake `gh` on `PATH` and build throwaway fixture trees, so nothing touches the network and no test framework is added to a repo that ships to wordpress.org.

**Tech Stack:** bash, `gh` CLI, `rsync` (macOS ships openrsync, so stay within rsync 2.6.9 flags), `jq`, `zip`, `npm pkg set`, `node -p`.

**Spec:** `docs/superpowers/specs/2026-09-24-lite-sync-from-pro-design.md`

## Global Constraints

- Pro repo is `awesomemotive/wp-simple-pay-pro`, workflow `build-and-export.yml`, artifact name `stripe-<version>`, artifact root `stripe/`.
- Version format is `X.Y.Z` or `X.Y.Z.N` and nothing else.
- `package.json` takes `X.Y.Z+N` for a four-part `X.Y.Z.N`; the other three version locations take the literal four-part string.
- Synced directories: `data includes lib src views`. Synced loose files: `readme.txt stripe-checkout.php uninstall.php license.txt` and `languages/stripe.pot`.
- `vendor/` is never touched. Lite gitignores it.
- Exact strings in the tree: plugin header line is ` * Version: <v>`; the define is `define( 'SIMPLE_PAY_VERSION', '<v>' );`; readme has `Stable tag: <v>`; pot has `Project-Id-Version: WP Simple Pay Lite <v>`.
- Changelog headings carry a prefix: `= Stripe Payment Forms <v> - <Month D, YYYY> =`.
- No em dashes in any user-facing output or committed prose.
- Deviation from the spec's component list, for testability: the spec named three script files; this plan splits the repo-mutating functions into a fourth, `bin/lib/lite-tree.sh`, so tests can source them without executing a CLI's main body.

## Review Focus

1. A version with a leading `v` or surrounding whitespace (`v4.17.4`, `4.17.4 `) must be rejected up front, not turned into an artifact name that silently never matches. Tested in Task 1.
2. Two successful runs on the same release branch (a redispatch) must resolve to the newest, not an arbitrary one. Tested in Task 2.
3. `--run` pointing at a run whose artifact holds a different version must be caught by payload validation, not committed. Tested in Task 3.
4. A Lite-only file inside a synced directory is deleted by `rsync --delete`; deletions must be surfaced loudly enough to stop a release. Tested in Task 5.
5. A Crowdin `.po` alongside `stripe.pot` must survive the sync, which is why the pot is copied file-wise rather than rsynced. Tested in Task 5.

---

### Task 1: Test harness, branch, and version helpers

**Files:**
- Create: `bin/lib/pro-build.sh`
- Create: `bin/tests/helpers.sh`
- Create: `bin/tests/run.sh`
- Create: `bin/tests/test-pro-build.sh`

**Interfaces:**
- Consumes: nothing.
- Produces: `pb_die <msg...>`, `pb_validate_version <version>` (returns 0/1), `pb_npm_version <version>` (echoes the npm-safe form), and the env-overridable `PRO_REPO` / `PRO_WORKFLOW`. Test helpers: `assert_eq <label> <expected> <actual>`, `assert_ok <label> <cmd...>`, `assert_fails <label> <cmd...>`, `assert_contains <label> <needle> <haystack>`, `summary`.

- [ ] **Step 1: Branch off master**

```bash
git checkout -b crossi/add/lite-sync-from-pro
```

- [ ] **Step 2: Write the assertion helpers**

Create `bin/tests/helpers.sh`:

```bash
# Minimal assertion helpers for the release scripts' tests.
# Sourced by bin/tests/test-*.sh.

TESTS_RUN=0
TESTS_FAILED=0

ok() {
	TESTS_RUN=$((TESTS_RUN + 1))
	printf '  ok   %s\n' "$1"
}

fail() {
	TESTS_RUN=$((TESTS_RUN + 1))
	TESTS_FAILED=$((TESTS_FAILED + 1))
	printf '  FAIL %s\n' "$1"
	if [ $# -gt 1 ]; then
		printf '       %s\n' "$2"
	fi
}

assert_eq() {
	if [ "$2" = "$3" ]; then
		ok "$1"
	else
		fail "$1" "expected '$2', got '$3'"
	fi
}

assert_ok() {
	local label="$1"
	shift
	if "$@" >/dev/null 2>&1; then
		ok "$label"
	else
		fail "$label" "command failed: $*"
	fi
}

assert_fails() {
	local label="$1"
	shift
	if "$@" >/dev/null 2>&1; then
		fail "$label" "command unexpectedly succeeded: $*"
	else
		ok "$label"
	fi
}

assert_contains() {
	case "$3" in
		*"$2"*) ok "$1" ;;
		*) fail "$1" "'$2' not found in: $3" ;;
	esac
}

summary() {
	printf '\n%s: %d run, %d failed\n' "${0##*/}" "$TESTS_RUN" "$TESTS_FAILED"
	[ "$TESTS_FAILED" -eq 0 ]
}
```

- [ ] **Step 3: Write the test runner**

Create `bin/tests/run.sh`:

```bash
#!/usr/bin/env bash
# Runs every bin/tests/test-*.sh and exits non-zero if any file fails.
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
failed=0

for t in "$here"/test-*.sh; do
	printf '\n== %s\n' "${t##*/}"
	if ! bash "$t"; then
		failed=1
	fi
done

if [ "$failed" -ne 0 ]; then
	printf '\nSOME TESTS FAILED\n'
	exit 1
fi

printf '\nall tests passed\n'
```

- [ ] **Step 4: Write the failing tests for the version helpers**

Create `bin/tests/test-pro-build.sh`:

```bash
#!/usr/bin/env bash
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
. "$here/helpers.sh"
. "$here/../lib/pro-build.sh"

# pb_validate_version
assert_ok    "accepts X.Y.Z"            pb_validate_version 4.17.4
assert_ok    "accepts X.Y.Z.N"          pb_validate_version 4.17.0.1
assert_fails "rejects two-part"         pb_validate_version 4.17
assert_fails "rejects leading v"        pb_validate_version v4.17.4
assert_fails "rejects trailing space"   pb_validate_version "4.17.4 "
assert_fails "rejects non-numeric"      pb_validate_version 4.17.x
assert_fails "rejects empty"            pb_validate_version ""

# pb_npm_version
assert_eq "three-part unchanged" "4.17.4"   "$(pb_npm_version 4.17.4)"
assert_eq "four-part uses +"     "4.17.0+1" "$(pb_npm_version 4.17.0.1)"

summary
```

- [ ] **Step 5: Run the tests to verify they fail**

Run: `bash bin/tests/run.sh`
Expected: FAIL, because `bin/lib/pro-build.sh` does not exist yet.

- [ ] **Step 6: Write the helpers**

Create `bin/lib/pro-build.sh`:

```bash
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
```

- [ ] **Step 7: Run the tests to verify they pass**

Run: `bash bin/tests/run.sh`
Expected: PASS, 9 assertions.

- [ ] **Step 8: Commit**

```bash
git add bin/lib/pro-build.sh bin/tests/helpers.sh bin/tests/run.sh bin/tests/test-pro-build.sh
git commit -m "Add release script test harness and version helpers"
```

---

### Task 2: Run resolution and the staleness guard

**Files:**
- Modify: `bin/lib/pro-build.sh`
- Create: `bin/tests/stubs/gh`
- Modify: `bin/tests/test-pro-build.sh`

**Interfaces:**
- Consumes: `pb_die`, `PRO_REPO`, `PRO_WORKFLOW` from Task 1.
- Produces: `pb_resolve_run <version>` echoes `<run_id> <head_sha> <head_branch>`; `pb_branch_tip <branch>` echoes a SHA or nothing; `pb_assert_fresh <run_sha> <branch>` returns 0/1. The `gh` stub reads `$GH_STUB_DIR` and logs calls to `$GH_STUB_DIR/calls.log`.

- [ ] **Step 1: Write the fake gh**

Create `bin/tests/stubs/gh`:

```bash
#!/usr/bin/env bash
# Fake `gh` for the release-script tests. Answers from $GH_STUB_DIR:
#   run-list.json   for `gh run list`
#   artifacts.json  for `gh api .../artifacts`
#   ref.json        for `gh api .../git/ref/heads/...`
#   payload/stripe  copied out by `gh run download`
set -uo pipefail

[ -n "${GH_STUB_DIR:-}" ] || { echo "gh stub: GH_STUB_DIR unset" >&2; exit 1; }
printf '%s\n' "$*" >>"$GH_STUB_DIR/calls.log"

# Echoes the value of a named flag from the remaining arguments.
flag_value() {
	local want="$1"
	shift
	while [ $# -gt 0 ]; do
		if [ "$1" = "$want" ]; then
			printf '%s' "${2-}"
			return 0
		fi
		shift
	done
}

subcommand="${1:-} ${2:-}"

case "$subcommand" in
	"run list")
		expr=$(flag_value --jq "$@")
		jq -r "${expr:-.}" <"$GH_STUB_DIR/run-list.json"
		;;
	"run download")
		dest=$(flag_value --dir "$@")
		[ -n "$dest" ] || { echo "gh stub: run download without --dir" >&2; exit 1; }
		mkdir -p "$dest"
		cp -R "$GH_STUB_DIR/payload/stripe" "$dest/stripe"
		;;
	"api "*)
		path="$2"
		expr=$(flag_value --jq "$@")
		case "$path" in
			*/artifacts) src="$GH_STUB_DIR/artifacts.json" ;;
			*/git/ref/heads/*) src="$GH_STUB_DIR/ref.json" ;;
			*) echo "gh stub: unknown api path $path" >&2; exit 1 ;;
		esac
		[ -f "$src" ] || exit 1
		jq -r "${expr:-.}" <"$src"
		;;
	*)
		echo "gh stub: unhandled '$*'" >&2
		exit 1
		;;
esac
```

Make it executable: `chmod +x bin/tests/stubs/gh`

- [ ] **Step 2: Write the failing tests**

Append to `bin/tests/test-pro-build.sh`, before the `summary` call:

```bash
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

# hotfix/<version> resolves too.
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
```

- [ ] **Step 3: Run the tests to verify they fail**

Run: `bash bin/tests/test-pro-build.sh`
Expected: FAIL with `pb_resolve_run: command not found`.

- [ ] **Step 4: Implement run resolution**

Append to `bin/lib/pro-build.sh`:

```bash
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
```

- [ ] **Step 5: Run the tests to verify they pass**

Run: `bash bin/tests/run.sh`
Expected: PASS, 16 assertions.

- [ ] **Step 6: Commit**

```bash
git add bin/lib/pro-build.sh bin/tests/stubs/gh bin/tests/test-pro-build.sh
git commit -m "Resolve the Pro build run and refuse stale builds"
```

---

### Task 3: Download and payload validation

**Files:**
- Modify: `bin/lib/pro-build.sh`
- Modify: `bin/tests/test-pro-build.sh`

**Interfaces:**
- Consumes: `pb_die`, `PRO_REPO` from Task 1.
- Produces: `pb_download <run_id> <version> <dest>` echoes the payload root (`<dest>/stripe`); `pb_validate_payload <payload_root> <version>` returns 0/1; `PB_PAYLOAD_DIRS` lists the directories a payload must contain. Task 5 and Task 6 both call these.

- [ ] **Step 1: Write the failing tests**

Append to `bin/tests/test-pro-build.sh`, before `summary`:

```bash
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
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `bash bin/tests/test-pro-build.sh`
Expected: FAIL with `pb_download: command not found`.

- [ ] **Step 3: Implement download, validation, and changelog extraction**

Append to `bin/lib/pro-build.sh`:

```bash
PB_PAYLOAD_DIRS="data includes lib src views languages"

# Downloads artifact stripe-<version> from <run_id> into <dest> and echoes
# the payload root. The artifact is uploaded unzipped with a stripe/ root.
pb_download() {
	local run_id="$1" version="$2" dest="$3" name expired
	name="stripe-$version"

	expired=$(PB_NAME="$name" gh api \
		"repos/$PRO_REPO/actions/runs/$run_id/artifacts" \
		--jq '[.artifacts[] | select(.name == $ENV.PB_NAME)] | .[0].expired') || {
		pb_die "could not list artifacts for run $run_id on $PRO_REPO"
		return 1
	}

	case "$expired" in
		false) : ;;
		true)
			pb_die "artifact $name on run $run_id has expired. Redispatch the build on Pro."
			return 1
			;;
		*)
			pb_die "run $run_id has no artifact named $name."
			return 1
			;;
	esac

	mkdir -p "$dest"
	gh run download "$run_id" --repo "$PRO_REPO" --name "$name" --dir "$dest" >/dev/null || {
		pb_die "downloading $name from run $run_id failed"
		return 1
	}

	if [ ! -d "$dest/stripe" ]; then
		pb_die "artifact $name did not unpack to a stripe/ root"
		return 1
	fi

	printf '%s\n' "$dest/stripe"
}

# Rejects anything that is not the Lite tree at <version>, before the
# working tree is touched. This is what catches a --run pointing at the
# wrong build.
pb_validate_payload() {
	local p="$1" version="$2" d

	for d in $PB_PAYLOAD_DIRS; do
		if [ ! -d "$p/$d" ]; then
			pb_die "payload is missing $d/"
			return 1
		fi
		if [ -z "$(ls -A "$p/$d")" ]; then
			pb_die "payload directory $d/ is empty"
			return 1
		fi
	done

	if [ -d "$p/includes/pro" ]; then
		pb_die "payload contains includes/pro, so it is a Pro build rather than a Lite build"
		return 1
	fi

	if ! grep -qxF " * Version: $version" "$p/stripe-checkout.php"; then
		pb_die "payload stripe-checkout.php header is not 'Version: $version'"
		return 1
	fi

	if ! grep -qF "define( 'SIMPLE_PAY_VERSION', '$version' );" "$p/stripe-checkout.php"; then
		pb_die "payload SIMPLE_PAY_VERSION is not '$version'"
		return 1
	fi

	if ! grep -qxF "Stable tag: $version" "$p/readme.txt"; then
		pb_die "payload readme.txt stable tag is not '$version'"
		return 1
	fi

	if ! grep -qF "Project-Id-Version: WP Simple Pay Lite $version" "$p/languages/stripe.pot"; then
		pb_die "payload stripe.pot was generated against a different version than $version"
		return 1
	fi

	return 0
}

# Echoes the changelog block for <version> from a Lite readme.txt. Headings
# look like "= Stripe Payment Forms 4.17.4 - September 24, 2026 =", so the
# version is matched as a substring rather than anchored.
pb_changelog_block() {
	local readme="$1" version="$2"
	awk -v needle=" $version - " '
		substr($0, 1, 2) == "= " {
			if (inb) { exit }
			if (index($0, needle) > 0) { inb = 1; print; next }
			next
		}
		inb { print }
	' "$readme"
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `bash bin/tests/run.sh`
Expected: PASS, 30 assertions.

- [ ] **Step 5: Commit**

```bash
git add bin/lib/pro-build.sh bin/tests/test-pro-build.sh
git commit -m "Download and validate the Lite build payload"
```

---

### Task 4: Applying the tree, stamping, and asserting versions

**Files:**
- Create: `bin/lib/lite-tree.sh`
- Create: `bin/tests/test-lite-tree.sh`

**Interfaces:**
- Consumes: `pb_npm_version` from Task 1.
- Produces: `LT_DIRS`, `LT_FILES`, `LT_POT`; `lt_dirty <repo>` echoes porcelain status for the sync set; `lt_apply <payload> <repo>`; `lt_stamp_package_json <repo> <version>`; `lt_assert_versions <repo> <version>` returns 0/1; `lt_deletions <payload> <repo>` echoes paths `--delete` would remove.

- [ ] **Step 1: Write the failing tests**

Create `bin/tests/test-lite-tree.sh`:

```bash
#!/usr/bin/env bash
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
. "$here/helpers.sh"
. "$here/../lib/pro-build.sh"
. "$here/../lib/lite-tree.sh"

work=$(mktemp -d "${TMPDIR:-/tmp}/wpsp-tree.XXXXXX")
trap 'rm -rf "$work"' EXIT

# A payload carrying one new file, one changed file, and no copy of the
# repo's Lite-only extras.
payload="$work/payload/stripe"
mkdir -p "$payload"/{data,includes,lib,src,views,languages}
printf 'placeholder\n' >"$payload/data/.keep"
printf 'placeholder\n' >"$payload/includes/.keep"
printf 'placeholder\n' >"$payload/lib/.keep"
printf 'placeholder\n' >"$payload/views/.keep"
printf 'new from pro\n' >"$payload/src/Added.php"
printf 'changed by pro\n' >"$payload/src/Changed.php"
cat >"$payload/stripe-checkout.php" <<'PHP'
<?php
/**
 * Version: 4.17.4
 */
	define( 'SIMPLE_PAY_VERSION', '4.17.4' );
PHP
printf '=== x ===\nStable tag: 4.17.4\n' >"$payload/readme.txt"
printf 'pot 4.17.4\n' >"$payload/languages/stripe.pot"
printf 'uninstall\n' >"$payload/uninstall.php"
printf 'license\n' >"$payload/license.txt"

# A repo that has an old version everywhere, a file Pro dropped, a
# Lite-only file inside a synced directory, an untracked vendor/, and a
# Crowdin .po beside the pot.
repo="$work/repo"
mkdir -p "$repo"/{data,includes,lib,src,views,languages,vendor,wordpress_org_assets}
printf 'placeholder\n' >"$repo/data/.keep"
printf 'placeholder\n' >"$repo/includes/.keep"
printf 'placeholder\n' >"$repo/lib/.keep"
printf 'placeholder\n' >"$repo/views/.keep"
printf 'stale\n' >"$repo/src/Changed.php"
printf 'pro dropped this\n' >"$repo/src/Removed.php"
printf 'lite only\n' >"$repo/src/LiteOnly.php"
printf 'do not touch\n' >"$repo/vendor/autoload.php"
printf 'crowdin\n' >"$repo/languages/pt_BR.po"
printf 'pot 4.17.3\n' >"$repo/languages/stripe.pot"
printf 'contributing\n' >"$repo/CONTRIBUTING.md"
printf '=== x ===\nStable tag: 4.17.3\n' >"$repo/readme.txt"
cat >"$repo/stripe-checkout.php" <<'PHP'
<?php
/**
 * Version: 4.17.3
 */
	define( 'SIMPLE_PAY_VERSION', '4.17.3' );
PHP
printf '{\n  "name": "wp-simple-pay-lite",\n  "version": "4.17.1"\n}\n' >"$repo/package.json"
printf 'uninstall\n' >"$repo/uninstall.php"
printf 'license\n' >"$repo/license.txt"

# Deletions are reported before anything is applied.
dels=$(lt_deletions "$payload" "$repo")
assert_contains "reports the dropped file" "src/Removed.php" "$dels"
assert_contains "reports the Lite-only file" "src/LiteOnly.php" "$dels"

lt_apply "$payload" "$repo"

assert_eq "adds a new file"   "new from pro"   "$(cat "$repo/src/Added.php")"
assert_eq "updates a changed file" "changed by pro" "$(cat "$repo/src/Changed.php")"
assert_fails "removes a dropped file" test -f "$repo/src/Removed.php"
assert_fails "removes a Lite-only file in a synced dir" test -f "$repo/src/LiteOnly.php"
assert_eq "leaves vendor alone" "do not touch" "$(cat "$repo/vendor/autoload.php")"
assert_eq "leaves Lite-only root files alone" "contributing" "$(cat "$repo/CONTRIBUTING.md")"
assert_ok "leaves wordpress_org_assets alone" test -d "$repo/wordpress_org_assets"
assert_eq "copies the pot" "pot 4.17.4" "$(cat "$repo/languages/stripe.pot")"
assert_eq "keeps a Crowdin po" "crowdin" "$(cat "$repo/languages/pt_BR.po")"
assert_eq "copies readme.txt" "Stable tag: 4.17.4" "$(sed -n '2p' "$repo/readme.txt")"

# Versions do not yet agree, because package.json has not been stamped.
assert_fails "assert catches an unstamped package.json" \
	lt_assert_versions "$repo" 4.17.4
pkg_msg=$(lt_assert_versions "$repo" 4.17.4 2>&1 || true)
assert_contains "assert names package.json" "package.json" "$pkg_msg"

lt_stamp_package_json "$repo" 4.17.4
assert_eq "stamps package.json" "4.17.4" \
	"$(node -p "require('$repo/package.json').version")"
assert_ok "versions agree after stamping" lt_assert_versions "$repo" 4.17.4

# Four-part versions use +N in package.json only.
lt_stamp_package_json "$repo" 4.17.0.1
assert_eq "stamps a four-part version" "4.17.0+1" \
	"$(node -p "require('$repo/package.json').version")"

# Applying twice changes nothing.
before=$(find "$repo" -type f -exec shasum {} \; | sort | shasum)
lt_apply "$payload" "$repo"
after=$(find "$repo" -type f -exec shasum {} \; | sort | shasum)
assert_eq "apply is idempotent" "$before" "$after"

summary
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `bash bin/tests/test-lite-tree.sh`
Expected: FAIL, `bin/lib/lite-tree.sh` does not exist.

- [ ] **Step 3: Implement the tree operations**

Create `bin/lib/lite-tree.sh`:

```bash
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
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `bash bin/tests/run.sh`
Expected: PASS, both files, 48 assertions total.

- [ ] **Step 5: Commit**

```bash
git add bin/lib/lite-tree.sh bin/tests/test-lite-tree.sh
git commit -m "Apply the Pro payload to the Lite tree and assert versions"
```

---

### Task 5: The sync CLI

**Files:**
- Create: `bin/sync-from-pro.sh`
- Create: `bin/tests/test-sync-cli.sh`

**Interfaces:**
- Consumes: everything from Tasks 1 through 4.
- Produces: `bin/sync-from-pro.sh <version> [--run ID] [--dry-run] [--force]`, exit 0 on success, 2 on usage error, 1 otherwise. `LITE_REPO` overrides the target tree for tests.

- [ ] **Step 1: Write the failing tests**

Create `bin/tests/test-sync-cli.sh`:

```bash
#!/usr/bin/env bash
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
. "$here/helpers.sh"

script="$here/../sync-from-pro.sh"

work=$(mktemp -d "${TMPDIR:-/tmp}/wpsp-cli.XXXXXX")
trap 'rm -rf "$work"' EXIT

export GH_STUB_DIR="$work/stub"
export PATH="$here/stubs:$PATH"
mkdir -p "$GH_STUB_DIR/payload"
: >"$GH_STUB_DIR/calls.log"

payload="$GH_STUB_DIR/payload/stripe"
mkdir -p "$payload"/{data,includes,lib,src,views,languages}
for d in data includes lib views; do printf 'x\n' >"$payload/$d/.keep"; done
printf 'from pro\n' >"$payload/src/App.php"
cat >"$payload/stripe-checkout.php" <<'PHP'
<?php
/**
 * Version: 4.17.4
 */
	define( 'SIMPLE_PAY_VERSION', '4.17.4' );
PHP
printf '=== x ===\nStable tag: 4.17.4\n\n== Changelog ==\n\n= Stripe Payment Forms 4.17.4 - September 24, 2026 =\n\n* New: A thing.\n' >"$payload/readme.txt"
printf 'msgstr ""\n"Project-Id-Version: WP Simple Pay Lite 4.17.4\\n"\n' >"$payload/languages/stripe.pot"
printf 'uninstall\n' >"$payload/uninstall.php"
printf 'license\n' >"$payload/license.txt"

cat >"$GH_STUB_DIR/run-list.json" <<'JSON'
[{"databaseId": 900, "headBranch": "release/4.17.4", "headSha": "tipsha0000000000000000000000000000000", "conclusion": "success"}]
JSON
printf '{"object":{"sha":"tipsha0000000000000000000000000000000"}}\n' >"$GH_STUB_DIR/ref.json"
printf '{"artifacts":[{"name":"stripe-4.17.4","expired":false}]}\n' >"$GH_STUB_DIR/artifacts.json"

# Builds a fresh git repo standing in for the Lite checkout.
make_repo() {
	local repo="$1"
	mkdir -p "$repo"/{data,includes,lib,src,views,languages}
	for d in data includes lib views; do printf 'x\n' >"$repo/$d/.keep"; done
	printf 'stale\n' >"$repo/src/App.php"
	printf 'lite only\n' >"$repo/CONTRIBUTING.md"
	printf 'pot old\n' >"$repo/languages/stripe.pot"
	printf '=== x ===\nStable tag: 4.17.3\n' >"$repo/readme.txt"
	cat >"$repo/stripe-checkout.php" <<'PHP'
<?php
/**
 * Version: 4.17.3
 */
	define( 'SIMPLE_PAY_VERSION', '4.17.3' );
PHP
	printf '{\n  "name": "wp-simple-pay-lite",\n  "version": "4.17.1"\n}\n' >"$repo/package.json"
	printf 'uninstall\n' >"$repo/uninstall.php"
	printf 'license\n' >"$repo/license.txt"
	git -C "$repo" init -q
	git -C "$repo" add -A
	git -C "$repo" -c user.email=t@t -c user.name=t commit -qm init
}

# Usage errors exit 2.
LITE_REPO="$work/unused" bash "$script" >/dev/null 2>&1
assert_eq "no version exits 2" "2" "$?"
LITE_REPO="$work/unused" bash "$script" v4.17.4 >/dev/null 2>&1
assert_eq "bad version exits non-zero" "1" "$?"

# Dry run writes nothing.
make_repo "$work/dry"
out=$(LITE_REPO="$work/dry" bash "$script" 4.17.4 --dry-run 2>&1)
assert_eq "dry run leaves the tree alone" "stale" "$(cat "$work/dry/src/App.php")"
assert_contains "dry run mentions the run" "900" "$out"

# A real sync applies, stamps, and asserts.
make_repo "$work/real"
out=$(LITE_REPO="$work/real" bash "$script" 4.17.4 2>&1)
assert_eq "sync exit status" "0" "$?"
assert_eq "sync applied src"  "from pro" "$(cat "$work/real/src/App.php")"
assert_eq "sync stamped package.json" "4.17.4" \
	"$(node -p "require('$work/real/package.json').version")"
assert_eq "sync copied readme" "Stable tag: 4.17.4" "$(sed -n '2p' "$work/real/readme.txt")"
assert_eq "sync kept Lite-only files" "lite only" "$(cat "$work/real/CONTRIBUTING.md")"

# A dirty sync set is refused unless forced.
make_repo "$work/dirty"
printf 'uncommitted\n' >>"$work/dirty/src/App.php"
LITE_REPO="$work/dirty" bash "$script" 4.17.4 >/dev/null 2>&1
assert_eq "dirty tree is refused" "1" "$?"
assert_contains "dirty tree still has the edit" "uncommitted" "$(cat "$work/dirty/src/App.php")"
LITE_REPO="$work/dirty" bash "$script" 4.17.4 --force >/dev/null 2>&1
assert_eq "force overrides the dirty check" "0" "$?"

# A stale build is refused, and --run bypasses the check.
make_repo "$work/stale"
printf '{"object":{"sha":"movedsha00000000000000000000000000000"}}\n' >"$GH_STUB_DIR/ref.json"
LITE_REPO="$work/stale" bash "$script" 4.17.4 >/dev/null 2>&1
assert_eq "stale build is refused" "1" "$?"
LITE_REPO="$work/stale" bash "$script" 4.17.4 --run 900 >/dev/null 2>&1
assert_eq "--run bypasses staleness" "0" "$?"

summary
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `bash bin/tests/test-sync-cli.sh`
Expected: FAIL, `bin/sync-from-pro.sh` does not exist.

- [ ] **Step 3: Implement the CLI**

Create `bin/sync-from-pro.sh`:

```bash
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

  <version>   X.Y.Z or X.Y.Z.N, for example 4.17.4
  --run ID    use this Pro run instead of resolving one, and skip the
              staleness check
  --dry-run   print what would change, then exit without writing
  --force     apply even when the sync set has uncommitted changes
USAGE
	exit 2
}

version=""
run_id=""
dry_run=0
force=0

while [ $# -gt 0 ]; do
	case "$1" in
		--run)
			run_id="${2-}"
			[ -n "$run_id" ] || usage
			shift 2
			;;
		--dry-run) dry_run=1; shift ;;
		--force) force=1; shift ;;
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
	printf '\nthis sync DELETES %d path(s):\n' "$(printf '%s\n' "$deletions" | wc -l | tr -d ' ')"
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

lt_apply "$payload" "$repo"
lt_stamp_package_json "$repo" "$version"
lt_assert_versions "$repo" "$version"

printf '\nsynced from Pro run %s\n' "$run_id"
git -C "$repo" status --short
```

- [ ] **Step 4: Make it executable and run the tests**

```bash
chmod +x bin/sync-from-pro.sh bin/tests/run.sh
bash bin/tests/run.sh
```

Expected: PASS, all three test files.

- [ ] **Step 5: Commit**

```bash
git add bin/sync-from-pro.sh bin/tests/test-sync-cli.sh bin/tests/run.sh
git commit -m "Add bin/sync-from-pro.sh"
```

---

### Task 6: The release zip CLI

**Files:**
- Create: `bin/release-zip.sh`
- Create: `bin/tests/test-release-zip.sh`

**Interfaces:**
- Consumes: `pb_validate_version`, `pb_resolve_run`, `pb_assert_fresh`, `pb_download`, `pb_validate_payload`.
- Produces: `bin/release-zip.sh <version> [--run ID]`, which writes `build/stripe-<version>.zip` and echoes its path on the last line.

- [ ] **Step 1: Write the failing test**

Create `bin/tests/test-release-zip.sh`:

```bash
#!/usr/bin/env bash
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
. "$here/helpers.sh"

script="$here/../release-zip.sh"

work=$(mktemp -d "${TMPDIR:-/tmp}/wpsp-zip.XXXXXX")
trap 'rm -rf "$work"' EXIT

export GH_STUB_DIR="$work/stub"
export PATH="$here/stubs:$PATH"
mkdir -p "$GH_STUB_DIR/payload"
: >"$GH_STUB_DIR/calls.log"

payload="$GH_STUB_DIR/payload/stripe"
mkdir -p "$payload"/{data,includes,lib,src,views,languages}
for d in data includes lib views src; do printf 'x\n' >"$payload/$d/.keep"; done
cat >"$payload/stripe-checkout.php" <<'PHP'
<?php
/**
 * Version: 4.17.4
 */
	define( 'SIMPLE_PAY_VERSION', '4.17.4' );
PHP
printf '=== x ===\nStable tag: 4.17.4\n' >"$payload/readme.txt"
printf 'msgstr ""\n"Project-Id-Version: WP Simple Pay Lite 4.17.4\\n"\n' >"$payload/languages/stripe.pot"
printf 'uninstall\n' >"$payload/uninstall.php"
printf 'license\n' >"$payload/license.txt"

cat >"$GH_STUB_DIR/run-list.json" <<'JSON'
[{"databaseId": 900, "headBranch": "release/4.17.4", "headSha": "tipsha0000000000000000000000000000000", "conclusion": "success"}]
JSON
printf '{"object":{"sha":"tipsha0000000000000000000000000000000"}}\n' >"$GH_STUB_DIR/ref.json"
printf '{"artifacts":[{"name":"stripe-4.17.4","expired":false}]}\n' >"$GH_STUB_DIR/artifacts.json"

mkdir -p "$work/repo"
zip_path=$(LITE_REPO="$work/repo" bash "$script" 4.17.4 2>/dev/null | tail -1)

assert_eq "zip lands at the expected path" "$work/repo/build/stripe-4.17.4.zip" "$zip_path"
assert_ok "zip exists" test -f "$zip_path"
assert_contains "zip root is stripe/" "stripe/stripe-checkout.php" "$(unzip -Z1 "$zip_path")"

summary
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `bash bin/tests/test-release-zip.sh`
Expected: FAIL, `bin/release-zip.sh` does not exist.

- [ ] **Step 3: Implement the CLI**

Create `bin/release-zip.sh`:

```bash
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

usage() {
	cat >&2 <<'USAGE'
usage: bin/release-zip.sh <version> [--run ID]

  <version>   X.Y.Z or X.Y.Z.N, for example 4.17.4
  --run ID    use this Pro run instead of resolving one, and skip the
              staleness check
USAGE
	exit 2
}

version=""
run_id=""

while [ $# -gt 0 ]; do
	case "$1" in
		--run)
			run_id="${2-}"
			[ -n "$run_id" ] || usage
			shift 2
			;;
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

mkdir -p "$repo/build"
out="$repo/build/stripe-$version.zip"
rm -f "$out"
( cd "$(dirname "$payload")" && zip -q -r -X "$out" stripe )

printf 'built from Pro run %s\n' "$run_id" >&2
printf '%s\n' "$out"
```

- [ ] **Step 4: Make it executable and run the tests**

```bash
chmod +x bin/release-zip.sh
bash bin/tests/run.sh
```

Expected: PASS, all four test files.

- [ ] **Step 5: Commit**

```bash
git add bin/release-zip.sh bin/tests/test-release-zip.sh
git commit -m "Add bin/release-zip.sh"
```

---

### Task 7: Stop shipping tooling, retire the old command

**Files:**
- Modify: `gruntfile.js`
- Delete: `.claude/commands/wpsp-prep-release.md`

**Interfaces:**
- Consumes: nothing.
- Produces: nothing. This task only changes what the zip contains.

- [ ] **Step 1: Confirm what currently ships**

```bash
node -e '
const g = require("./gruntfile.js");
' 2>/dev/null || true
grep -n "distFiles" gruntfile.js
```

Note for the implementer: `distFiles` starts from `**`, and grunt's globbing does not match dotfiles unless `dot: true` is set, which it is not. So `.claude/`, `.gitignore` and `.nvmrc` are already excluded implicitly. `bin/` and `docs/` do not start with a dot and would ship.

- [ ] **Step 2: Exclude bin and docs**

In `gruntfile.js`, inside the `distFiles` array, add these two entries next to the other `!` entries:

```js
    "!bin/**",
    "!docs/**",
```

- [ ] **Step 3: Verify the exclusions take effect**

```bash
npx grunt copy:main
ls build/stripe/bin 2>/dev/null && echo "FAIL: bin shipped" || echo "ok: bin excluded"
ls build/stripe/docs 2>/dev/null && echo "FAIL: docs shipped" || echo "ok: docs excluded"
ls build/stripe/.claude 2>/dev/null && echo "FAIL: .claude shipped" || echo "ok: .claude excluded"
rm -rf build
```

Expected: three `ok:` lines.

- [ ] **Step 4: Delete the superseded command**

```bash
git rm .claude/commands/wpsp-prep-release.md
```

Pro's `/wpsp-prep-release` already writes the version bumps, stable tag and changelog into `readme-lite.txt`, and they arrive inside the artifact. Lite has nothing left to prep.

- [ ] **Step 5: Commit**

```bash
git add gruntfile.js
git commit -m "Keep bin/ and docs/ out of the release zip, drop Lite's prep-release"
```

---

### Task 8: The two slash commands

**Files:**
- Create: `.claude/commands/wpsp-sync-release.md`
- Create: `.claude/commands/wpsp-publish-release.md`

**Interfaces:**
- Consumes: `bin/sync-from-pro.sh`, `bin/release-zip.sh`, `pb_changelog_block`.
- Produces: `/wpsp-sync-release <version>` and `/wpsp-publish-release <version>`.

- [ ] **Step 1: Write the sync command**

Create `.claude/commands/wpsp-sync-release.md`:

````markdown
Sync the Lite release for version `$ARGUMENTS` from Pro's build, then open the release PR. Follow these steps exactly.

## Step 1: Validate

- The version is `$ARGUMENTS`. If none was given, ask the user for one before proceeding.
- It must look like `X.Y.Z` or `X.Y.Z.N`. If it does not, stop and say so.

## Step 2: Get onto the release branch

1. `git fetch origin`
2. If `release/$ARGUMENTS` exists locally or on `origin`, check it out.
3. If it does not exist, tell the user you will create it from `origin/master` and ask them to confirm. On confirmation: `git checkout -b release/$ARGUMENTS origin/master`.
4. Never sync onto `master` directly.

## Step 3: Preview the sync

Run:

```bash
bin/sync-from-pro.sh $ARGUMENTS --dry-run
```

If it reports a stale build, show the user the message and stop. They either redispatch **Build and Export Plugin** on Pro's `release/$ARGUMENTS` branch, or pass `--run <id>` if they know which run they want.

If it reports deletions, show them to the user and ask whether to continue. Deletions are normal when Pro drops a file, and a red flag when the path looks Lite-only.

## Step 4: Apply

```bash
bin/sync-from-pro.sh $ARGUMENTS
```

Pass `--run <id>` through if the user chose a specific run. The script asserts that `stripe-checkout.php` (both locations), `readme.txt` and `package.json` all state the version, and fails if they disagree.

## Step 5: Commit

```bash
git add -A
git commit -m "Release $ARGUMENTS"
```

Match the existing history, which uses a bare `Release X.Y.Z` subject.

## Step 6: Push and open the PR

1. `git push -u origin release/$ARGUMENTS`
2. Read the changelog block for this release out of the synced readme:

   ```bash
   . bin/lib/pro-build.sh && pb_changelog_block readme.txt $ARGUMENTS
   ```

3. Find Pro's own release PR, so the two link to each other:

   ```bash
   gh pr list --repo awesomemotive/wp-simple-pay-pro --head release/$ARGUMENTS --state all --json number,url --jq '.[0].url'
   ```

   If there is none, say so in the body instead of linking.

4. Open the PR:

   ```bash
   gh pr create --base master --title "Release $ARGUMENTS" --body "<body>"
   ```

   The body contains, in this order: the Pro run ID the artifact came from, the Pro release PR link (or a note that there is none), and the changelog block quoted verbatim.

## Step 7: Summary

Report:

- the branch and the Pro run ID used
- counts of added, modified and deleted files
- any deletions, listed individually
- the PR URL
- that the next step is to merge the PR and then run `/wpsp-publish-release $ARGUMENTS`
````

- [ ] **Step 2: Write the publish command**

Create `.claude/commands/wpsp-publish-release.md`:

````markdown
Publish the GitHub Release for version `$ARGUMENTS`. Run this after the `release/$ARGUMENTS` PR has merged. Follow these steps exactly.

## Step 1: Validate

- The version is `$ARGUMENTS`. If none was given, ask the user for one before proceeding.
- `git checkout master && git pull origin master`
- Confirm `master` states the version:

  ```bash
  grep -c "^ \* Version: $ARGUMENTS\$" stripe-checkout.php
  ```

  If it is not `1`, the PR has not merged. Stop and tell the user.

- Confirm the tag is free:

  ```bash
  git ls-remote --tags origin "refs/tags/$ARGUMENTS"
  ```

  If it returns anything, the release already exists. Stop and tell the user.

## Step 2: Build the zip

```bash
bin/release-zip.sh $ARGUMENTS
```

This downloads the same artifact the sync used and rezips it. It does not rebuild anything locally. The last line of output is the zip path.

## Step 3: Get the release description

Ask the user for the release description. It is normally a link to the field guide or to the release PR. Offer the Lite release PR URL as the default:

```bash
gh pr list --head release/$ARGUMENTS --state merged --json url --jq '.[0].url'
```

## Step 4: Create the release

```bash
gh release create $ARGUMENTS --target master --title "$ARGUMENTS" --notes "<description>" build/stripe-$ARGUMENTS.zip
```

This creates the tag on `master` as a side effect, so there is no separate tagging step.

## Step 5: Summary

Report the release URL, the tagged commit, and the attached zip's name and size.
````

- [ ] **Step 3: Verify the changelog extraction the command relies on**

```bash
. bin/lib/pro-build.sh && pb_changelog_block readme.txt 4.17.3
```

Expected: the `= Stripe Payment Forms 4.17.3 - June 16, 2026 =` heading and its entries, and nothing from 4.17.2.

- [ ] **Step 4: Run the full test suite once more**

```bash
bash bin/tests/run.sh
```

Expected: PASS, all four test files.

- [ ] **Step 5: Commit**

```bash
git add .claude/commands/wpsp-sync-release.md .claude/commands/wpsp-publish-release.md
git commit -m "Add /wpsp-sync-release and /wpsp-publish-release"
```

---

### Task 9: Document the process

**Files:**
- Modify: `CONTRIBUTING.md`

**Interfaces:**
- Consumes: the commands from Task 8.
- Produces: nothing.

- [ ] **Step 1: Read the current release section**

```bash
grep -n -i "release" CONTRIBUTING.md
```

- [ ] **Step 2: Replace the manual checklist**

Rewrite the release section so it reads:

```markdown
## Releasing

Lite is built in the Pro repo. This repo only receives the result, so there
is no hand-merging.

1. On Pro, prepare the release (`/wpsp-prep-release X.Y.Z`) and dispatch
   **Build and Export Plugin** on `release/X.Y.Z`. That run uploads a
   `stripe-X.Y.Z` artifact, which is the Lite tree.
2. Here, run `/wpsp-sync-release X.Y.Z`. It creates `release/X.Y.Z`, applies
   the artifact, stamps `package.json`, commits, pushes, and opens the PR.
3. Review and merge the PR.
4. Run `/wpsp-publish-release X.Y.Z` to tag `master` and publish the GitHub
   Release with the zip attached.

Version numbers, the stable tag and the changelog all come from Pro's
`readme-lite.txt` inside the artifact. Do not edit them here.

The scripts behind the commands are `bin/sync-from-pro.sh` and
`bin/release-zip.sh`. Their tests run with `bash bin/tests/run.sh` and need
no network.
```

- [ ] **Step 3: Commit**

```bash
git add CONTRIBUTING.md
git commit -m "Document the sync-based release process"
```

---

## Done when

- `bash bin/tests/run.sh` passes with no network access
- `bin/sync-from-pro.sh 4.17.4 --run 36035629943 --dry-run` against the real Pro build reports the 4.17.4 changes and zero deletions. The plain `--dry-run` form correctly aborts as stale, because `release/4.17.4` moved after run 36017634822 built it
- `npx grunt copy:main` produces a `build/stripe/` with no `bin/`, `docs/` or `.claude/`
- `.claude/commands/wpsp-prep-release.md` is gone
