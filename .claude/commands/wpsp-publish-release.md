Publish the GitHub Release for version `$ARGUMENTS`. Run this after the `release/$ARGUMENTS` PR has merged. Follow these steps exactly.

## Step 1: Validate

- The version is `$ARGUMENTS`. If none was given, ask the user for one before proceeding.
- `git checkout master && git pull origin master`
- Confirm `master` states the version. Use `grep -qxF` with an explicit
  branch: `grep -c` exits non-zero on zero matches, which reads as a tool
  failure rather than an answer, and `$ARGUMENTS` in a pattern would treat
  the dots as wildcards.

  ```bash
  if grep -qxF " * Version: $ARGUMENTS" stripe-checkout.php; then
    echo "merged"
  else
    echo "NOT merged"
  fi
  ```

  If it prints `NOT merged`, stop and tell the user the PR has not landed.

- Confirm the tag is free:

  ```bash
  git ls-remote --tags origin "refs/tags/$ARGUMENTS"
  ```

  If it returns anything, the release already exists. Stop and tell the user.

## Step 2: Build the zip

Pass the Pro run ID that `/wpsp-sync-release` used, which is recorded in the
release PR body. Without `--run`, this script resolves a run of its own,
which can be a redispatch built after the sync.

```bash
bin/release-zip.sh $ARGUMENTS --run <id>
```

It rezips that artifact and does not rebuild anything locally, so the
released zip is the one Pro built.

Before zipping, it compares the artifact against the working tree across the
sync set and refuses on any difference, so the published zip cannot contain
code that was never synced. If it refuses:

- the run ID is probably wrong. Get the right one from the PR body.
- `--allow-tree-mismatch` overrides the check. Only use it to rebuild an
  older release's zip from a checkout that has moved on, and tell the user
  that is what you are doing.

The last line of output is the zip path.

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

Report the release URL, the tagged commit, the Pro run ID the zip came from, and the attached zip's name and size.
