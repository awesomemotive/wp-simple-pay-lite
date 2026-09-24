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
