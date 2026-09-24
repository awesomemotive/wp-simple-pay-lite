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
