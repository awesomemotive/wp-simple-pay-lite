# Releasing WP Simple Pay Lite

Lite is built in the Pro repo. This repo only receives the result, so there
is no hand-merging and no version bumping by hand.

## The steps

1. On Pro, prepare the release (`/wpsp-prep-release X.Y.Z`) and dispatch
   **Build and Export Plugin** on `release/X.Y.Z`. That run uploads a
   `stripe-X.Y.Z` artifact, which is the Lite tree.
2. Here, run `/wpsp-sync-release X.Y.Z`. It creates `release/X.Y.Z`, applies
   the artifact, stamps `package.json`, commits, pushes, and opens the PR.
3. Review and merge the PR.
4. Run `/wpsp-publish-release X.Y.Z` to tag `master` and publish the GitHub
   Release with the zip attached.

## What comes from where

Version numbers, the stable tag and the changelog all come from Pro's
`readme-lite.txt` inside the artifact. Do not edit them here.

The artifact owns `data/`, `includes/`, `lib/`, `src/`, `views/`,
`languages/stripe.pot`, `readme.txt`, `stripe-checkout.php`, `uninstall.php`
and `license.txt`. Everything else in this repo is Lite-only and the sync
never touches it. `vendor/` is left alone because it is gitignored here and
comes from the build.

`package.json` is the one version location the artifact cannot carry, since
`gruntfile.js` excludes it from the zip. `bin/sync-from-pro.sh` stamps it,
then asserts that all four version locations agree before you commit.

## The scripts

`bin/sync-from-pro.sh X.Y.Z` applies the artifact. Useful flags:

- `--dry-run` shows what would change and writes nothing
- `--run <id>` uses a specific Pro run and skips the staleness check, for
  when the build was dispatched from a branch not named `release/X.Y.Z`
- `--force` applies over uncommitted changes in the sync set
- `--allow-deletions` is required when the sync would remove files. Read the
  reported paths first: a Lite-only path means the build is wrong

`bin/release-zip.sh X.Y.Z --run <id>` rezips that run's artifact into
`build/stripe-X.Y.Z.zip` for the GitHub Release. It does not rebuild
anything, so the released zip is the one the Pro build produced.

Pass the run ID the sync used, which is in the release PR body. Without it
the script resolves its own run, which may be a redispatch built after the
sync. It compares the artifact against the working tree and refuses on any
difference, so the published zip cannot contain code that was never synced.
`--allow-tree-mismatch` overrides that, and is only for rebuilding an older
release's zip from a checkout that has moved on.

By default both refuse a build whose release branch has moved since the run,
because that artifact no longer matches the branch.

## If a sync fails partway through

Validation runs before anything is written, so a bad artifact leaves the
tree untouched. If an apply still fails mid-way:

```bash
git checkout -- . && git clean -fd
```

The checkout restores modified files; `git clean -fd` is what removes the
files the partial apply added. Re-running the sync also works, but needs
`--force`, because the partial apply has made the sync set dirty.

## Requirements

`gh` (authenticated, `repo` scope), `jq`, `rsync`, `zip`, `node` and `npm`.

## Tests

```bash
bash bin/tests/run.sh
```

They stub `gh` and build fixture trees, so they need no network and no
credentials.

The design behind all of this is in
`docs/superpowers/specs/2026-09-24-lite-sync-from-pro-design.md`.
