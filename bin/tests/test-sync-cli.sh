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
mkdir -p "$payload"/{data,includes,lib,src,views,languages,vendor}
printf '<?php // autoload\n' >"$payload/vendor/autoload.php"
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
[{"databaseId": 900, "headBranch": "release/4.17.4", "headSha": "tipsha0000000000000000000000000000000", "conclusion": "success", "createdAt": "2026-09-24T17:00:00Z"}]
JSON
printf '{"object":{"sha":"tipsha0000000000000000000000000000000"}}\n' >"$GH_STUB_DIR/ref.json"
printf '{"artifacts":[{"name":"stripe-4.17.4","expired":false}]}\n' >"$GH_STUB_DIR/artifacts.json"

# Builds a fresh git repo standing in for the Lite checkout.
make_repo() {
	local repo="$1" d
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
assert_eq "bad version exits 1" "1" "$?"

# Dry run writes nothing.
make_repo "$work/dry"
out=$(LITE_REPO="$work/dry" bash "$script" 4.17.4 --dry-run 2>&1)
assert_eq "dry run leaves the tree alone" "stale" "$(cat "$work/dry/src/App.php")"
assert_contains "dry run mentions the run" "900" "$out"

# A real sync applies, stamps, and asserts.
make_repo "$work/real"
LITE_REPO="$work/real" bash "$script" 4.17.4 >/dev/null 2>&1
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

# --- deletions are a hard gate --------------------------------------------

# Finding 7: the deletion report used to be advisory, so nothing in code
# stopped a release that silently dropped Lite files.
#
# The staleness tests above left ref.json on a moved SHA; restore it so a
# failure here can only come from the deletion gate.
printf '{"object":{"sha":"tipsha0000000000000000000000000000000"}}\n' >"$GH_STUB_DIR/ref.json"

make_repo "$work/del"
printf 'lite only\n' >"$work/del/src/LiteOnly.php"
git -C "$work/del" add -A
git -C "$work/del" -c user.email=t@t -c user.name=t commit -qm add-lite-only
LITE_REPO="$work/del" bash "$script" 4.17.4 >/dev/null 2>&1
assert_eq "deletions block the sync" "1" "$?"
assert_ok "the file survives a blocked sync" test -f "$work/del/src/LiteOnly.php"
del_msg=$(LITE_REPO="$work/del" bash "$script" 4.17.4 2>&1 || true)
assert_contains "blocked sync names the flag" "--allow-deletions" "$del_msg"
LITE_REPO="$work/del" bash "$script" 4.17.4 --allow-deletions >/dev/null 2>&1
assert_eq "--allow-deletions lets it through" "0" "$?"
assert_fails "the file is gone once allowed" test -f "$work/del/src/LiteOnly.php"

# A dry run reports deletions without needing the flag.
make_repo "$work/deldry"
printf 'lite only\n' >"$work/deldry/src/LiteOnly.php"
dry_out=$(LITE_REPO="$work/deldry" bash "$script" 4.17.4 --dry-run 2>&1)
assert_contains "dry run still reports deletions" "src/LiteOnly.php" "$dry_out"
assert_ok "dry run leaves the file alone" test -f "$work/deldry/src/LiteOnly.php"

# --- resolution coordinates ------------------------------------------------

# Finding 8: --run must not consult the branch tip at all.
make_repo "$work/norun"
: >"$GH_STUB_DIR/calls.log"
LITE_REPO="$work/norun" bash "$script" 4.17.4 --run 900 >/dev/null 2>&1
calls=$(cat "$GH_STUB_DIR/calls.log")
case "$calls" in
	*git/ref*) fail "--run skips the branch lookup" "it called git/ref anyway" ;;
	*) ok "--run skips the branch lookup" ;;
esac

summary
