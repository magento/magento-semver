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

### Releases & Versioning

#### Releases

- Magento-semver development should happen against the `develop` branch. 
- New releases will shipped monthly. However, new releases will only occur if the `develop` branch has diverged from the `master` branch.
- If a hot-fix needs to be applied, a new release may be cut at any time. If this happens, the release cycle does not change.

#### Versioning

- Versions will be handled via GitHub Tags.
- Only `MAJOR` versions, as understood by the Semantic Versioning specification, are allowed; e.g.: increasing from version `2.0.0` to version `3.0.0`.
- With each new version, the `composer.json` file must be updated to match the new target version before creating a tag.
- After a new version is released, `magento-semver` will be packaged and published to `repo.magento.com` for consumption.

## Tests
- `vendor/bin/phpunit -c tests/Unit/phpunit.xml.dist`
