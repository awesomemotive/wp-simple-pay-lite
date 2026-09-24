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
