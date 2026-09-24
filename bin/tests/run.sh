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
