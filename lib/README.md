# WP Simple Pay Vendor Libraries

**These files are automatically generated, do not make changes -- they will be lost.**

Dependencies managed by [Mozart](https://github.com/coenjacobs/mozart) are automatically placed here with updated namespaces to avoid conflicts with other plugins or themes using the same pages, but different versions.

## Adding a dependency

Mozart must be manually run when a new managed dependency is introduced. First, add the package to Mozart's configuration in [`composer.json`](https://github.com/wpsimplepay/wp-simple-pay-pro/blob/master/composer.json):

```diff
"packages": [
	"stripe/stripe-php",
+	"berlindb/core"
],
```

Next, ensure Mozart is installed globally:

```
composer global require coenjacobs/mozart
```

_Mozart cannot be instaled as a project dependency because it [requires PHP 7.3 or higher](https://github.com/coenjacobs/mozart/blob/75ae1f91f04bbbd4b6edff282a483dfe611b2cea/composer.json#L15) to run, which will fail when automatically running integration tests in lower versions of PHP._

```
composer run mozart
```
