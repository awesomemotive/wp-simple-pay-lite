# Syncing Lite from the Pro build

Date: 2026-09-24
Status: implemented on crossi/add/lite-sync-from-pro

## Problem

Publishing a Lite release currently means hand-merging the contents of the
Lite zip that Pro's CI builds into the `wp-simple-pay-lite` working tree,
then repeating version bumps and changelog edits that Pro has already made.

The hand-merge is the whole problem. It is the step that has no record of
what it did, and it is where files get missed. One miss is visible in the
repo today: `package.json` says `4.17.1` while every other version location
says `4.17.3`. Lite's `gruntfile.js` `distFiles` excludes `package.json`, so
the file is never inside the zip, so the hand-merge could never have carried
a version bump into it.

## What is already automated

Most of the manual checklist is already done in Pro, which is why the
remaining gap is narrow.

`.github/workflows/build-and-export.yml` in Pro, step "Build Lite version",
already produces the exact tree Lite should contain. It clones
`wp-simple-pay-lite` from GitHub master, wipes
`data includes lib src vendor views`, copies Pro's built equivalents in,
strips Pro-only code (`includes/pro`, five REST routes under
`src/RestApi/Internal/Payment/`, `src/PaymentMethods/PaymentMethod`), copies
`readme-lite.txt` over `readme.txt`, stamps the version into
`stripe-checkout.php` and `package.json`, regenerates `languages/stripe.pot`,
and zips the result. It uploads the unzipped contents as artifact
`stripe-<version>`.

`/wpsp-prep-release` in Pro already bumps `readme-lite.txt`'s `Stable tag`
and `Tested up to`, and already inserts the Lite changelog there.

So the artifact carries the version stamps, the stable tag, the changelog,
and the regenerated pot. Manual steps 2 through 5 of the old checklist are
redundant, and Lite's own `/wpsp-prep-release` is dead code.

The one thing the workflow does not do is push the synced tree back to the
Lite repo. It keeps only the zip. That is the gap this design closes.

## Approach

A script in the Lite repo downloads the `stripe-<version>` artifact from
Pro's build run and applies it to the working tree. The build stays in Pro.
Lite only consumes its output, so the committed tree is the tree that was
built and tested rather than a re-derivation of it.

Applying the artifact uses per-directory `rsync -a --delete` over the five
directories the artifact owns in this repo, plus explicit copies of the loose
files. Deletions are therefore confined to Pro-owned paths: a file removed
in Pro disappears from Lite, and nothing Lite-only is ever at risk.

The rejected alternative was a single whole-tree `rsync --delete` with an
exclude list of Lite-only paths. That puts the risk on the "what do I
protect" side: add a Lite-only file, forget the list, and the next sync
deletes it. Confining deletions to an explicit copy set is the safer
failure mode.

Nothing rebuilds locally. The release zip is the downloaded artifact's
`stripe/` directory rezipped, not the output of a local `npm run build`.

## The sync set

Artifact root is `stripe/`. It contains exactly:

```
data/ includes/ languages/ lib/ src/ vendor/ views/
license.txt  readme.txt  stripe-checkout.php  uninstall.php
```

| Path | Treatment |
|---|---|
| `data/` `includes/` `lib/` `src/` `views/` | `rsync -a --delete` from artifact |
| `languages/stripe.pot` | copy the single file, not an rsync of the directory, so Crowdin `.po`/`.mo` files arriving later are not deleted |
| `readme.txt` `stripe-checkout.php` `uninstall.php` `license.txt` | copy |
| `vendor/` | never touched; Lite gitignores it |
| `package.json` | version stamped by the script, since the artifact cannot contain it |

Everything else in the Lite repo is Lite-only and untouched: `.gitignore`,
`.nvmrc`, `CONTRIBUTING.md`, `composer.json`, `composer.lock`, `crowdin.yml`,
`gruntfile.js`, `package-lock.json`, `readme.md`, `wordpress_org_assets/`,
`.claude/`, `bin/`, `docs/`.

`rsync -a` preserves the artifact's mtimes, so content-identical files are
rewritten but produce no git diff.

Verified against the `4.17.4` artifact (Pro run 36035629943): zero deletions
across all five directories, `data/` and `lib/` byte-identical, additions in
`includes/` (5), `src/` (19 files, 3 directories) and `views/` (4).

## Run resolution

Given a version, the newest successful `build-and-export.yml` run on
`awesomemotive/wp-simple-pay-pro` whose head branch is `release/<version>`
or `hotfix/<version>` and which has a non-expired `stripe-<version>`
artifact.

Resolution is scoped with `gh run list --branch`, not a fixed `--limit`
window over recent runs: the workflow also fires on `pull_request`, so a
busy week can push a legitimate release run past any limit and produce a
misleading "no successful run" alongside advice to redispatch a build that
already exists.

The run's head SHA is compared against that branch's current tip. A
mismatch means the branch moved after the build, so the artifact is stale
and the script refuses to proceed, naming both SHAs and telling the user to
redispatch the build or pass `--run <id>`. Reading the tip distinguishes a
genuine 404 from every other failure and fails closed on the latter: a lost
token or a 5xx must not read as "the branch was merged" and quietly disable
the guard.

`--run <id>` overrides resolution entirely. This covers builds dispatched
from a differently-named branch, such as the `release/4.17.4-verify` run.

## Validation

The payload is checked before the working tree is touched, so a bad
artifact leaves the repo untouched:

- all six directories present and non-empty (the five synced ones plus
  `vendor/`, which ships in the zip even though the sync never touches it)
- `vendor/autoload.php` present, since `stripe-checkout.php` requires it and
  a missing one is a fatal on activation
- `stripe-checkout.php`, `readme.txt`, `uninstall.php` and `license.txt`
  present, so the apply step cannot fail partway through
- `includes/pro` absent
- `stripe-checkout.php` `Version:` header equals the requested version
- `stripe-checkout.php` `SIMPLE_PAY_VERSION` equals the requested version
- `readme.txt` `Stable tag:` equals the requested version
- `languages/stripe.pot` `Project-Id-Version` ends with the requested version

After applying, one assertion that all four version locations agree:
`stripe-checkout.php` header, `SIMPLE_PAY_VERSION`, `readme.txt` stable tag,
and `package.json`. This is the check that would have caught the
`package.json` miss.

Four-part versions: `package.json` takes `X.Y.Z+N` for `X.Y.Z.N`, since
npm rejects four-part versions. The other three locations take the literal
four-part version. The assertion compares the normalized forms.

## Components

`bin/lib/pro-build.sh`, sourced, not executed. Resolves the Pro run,
downloads the artifact to a temp directory, validates the payload. Both
entry points share it so run resolution has exactly one implementation.

`bin/sync-from-pro.sh <version> [--run ID] [--dry-run] [--force]
[--allow-deletions]`. Sources the helper, applies the sync set, stamps
`package.json`, runs the version assertion, prints a change summary.
Deletions are a gate rather than a warning: dropping a file is right when
Pro removed it and wrong when the path is Lite-only, only a person can tell
those apart, so the apply refuses without `--allow-deletions`. `--dry-run` prints the itemized rsync
changes and exits without writing. Aborts if the working tree is dirty
within the sync set unless `--force`.

`bin/release-zip.sh <version> [--run ID] [--allow-tree-mismatch]`. Sources
the helper, rezips the artifact's `stripe/` root to
`build/stripe-<version>.zip`. Refuses when the payload's contents differ
from the working tree's sync set, unless overridden.

`bin/lib/lite-tree.sh`, sourced. Holds the tree operations: the deletion
preview, the apply, the `package.json` stamp, the version assertion, and the
payload-versus-tree comparison. Split out from the entry points so the tests
can source these functions without executing a CLI's main body.

`/wpsp-sync-release <version>`. Checks out `release/<version>`, creating it
from `origin/master` if absent (after confirming with the user). Runs
`bin/sync-from-pro.sh`. Shows the diff and stops to ask about anything
surprising, deletions in particular. Commits as `Release <version>`,
matching existing history. Pushes and opens a PR against `master`. Prints
the PR URL.

The PR body carries three things:

- a link to the Pro run the artifact came from, by ID
- a link to Pro's own release PR, found with `gh pr list` on the Pro repo
  filtered to the run's head branch. Omitted with a note if there is no
  open PR for that branch
- the changelog block quoted from the synced `readme.txt`: the lines from
  the `= <version> - ...` heading under `== Changelog ==` up to the next
  `= ` heading

`/wpsp-publish-release <version>`. Run after the PR merges. Confirms
`master` carries the release commit, that `stripe-checkout.php` on `master`
states the version, and that no `<version>` tag exists yet. Runs
`bin/release-zip.sh --run <id>` with the run ID recorded in the sync PR
body, and that script refuses to zip a payload whose contents differ from
the working tree's sync set: it resolves its own run when not given one, and
a redispatch would otherwise publish code that was never synced while every
version string still agreed. Asks for the release description, defaulting to the
Lite release PR URL. Then one `gh release create <version> --target master
--title <version>` call, which creates the tag on `master` as a side effect,
with `build/stripe-<version>.zip` attached. Prints the release URL.

## Changes to existing files

`gruntfile.js`: add `!bin/**` and `!docs/**` to `distFiles`, so the new
scripts and this spec do not ship to wordpress.org.

`distFiles` starts from `**`, and grunt's globbing does not match dotfiles
unless `dot: true` is set, which it is not. So `.claude/`, `.gitignore` and
`.nvmrc` are already excluded implicitly, confirmed by the artifact root
containing no dotfiles. `bin/` and `docs/` do not start with a dot and would
ship without the explicit excludes.

`.claude/commands/wpsp-prep-release.md`: delete. Version bumps and changelog
now arrive inside the artifact from Pro.

## Re-running

The sync converges rather than accumulating. `rsync --delete` makes the
directories match the artifact regardless of prior state, and
`npm pkg set version` is idempotent. Running the sync twice on the same
artifact produces no second diff, and the command reports the tree as
already in sync.

A failed validation aborts before any write, which is why validation covers
every path the apply step touches, `vendor/autoload.php`, `uninstall.php`
and `license.txt` included.

If an apply still fails partway through, recovery is
`git checkout -- . && git clean -fd`: the checkout alone restores modified
files but leaves behind whatever rsync added. Re-running the sync also
works, but only with `--force`, because the partial apply has made the sync
set dirty and the dirty-tree guard will otherwise refuse.

## Out of scope

Pro's workflow sets Lite's `package.json` from Pro's raw version without the
`sed 's/+/./g'` that the Dockerfile applies, so a four-part release such as
`4.17.0.1` would stamp `Version: 4.17.0+1` into `stripe-checkout.php`. That
is a Pro-side bug. The Lite script derives four-part versions correctly on
its own, and its pre-apply validation would reject such an artifact rather
than commit it.

Pro's workflow also runs `rm -rf lib/symfony` lowercase, which no-ops on
case-sensitive Linux against `lib/Symfony`. Harmless today, since `lib/` in
the artifact matches what Lite should ship, but it means that line is not
doing what it reads as doing.
