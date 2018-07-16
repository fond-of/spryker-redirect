# spryker-redirect
Simple redirect router.

1. Adds current locale to uri and redirects if not given.
2. Removes trailing slash from url if given and redirects.

Examples:
example.org/my/path => example.org/[locale]/my/path
example.org/en/my/path/ => example.org/en/my/path

*locale - if not given, detect browser locale. If browser locale is not given in shop, use default shop locale. 