# Magento Semantic Version Checker

## Installation

- `git clone git@github.com:magento/magento-semver.git`
- `cd magento-semver`
- `composer install`

## Usage
- `php bin/svc --help`

### Commands
- `php bin/svc compare` - Compare a set of files to determine what semantic versioning change needs to be done.
- `php bin/svc update-breaking-changes` - Update the file with a list of backward incompatible changes between two sources.

## Tests
- `vendor/bin/phpunit -c tests/Unit/phpunit.xml.dist`
