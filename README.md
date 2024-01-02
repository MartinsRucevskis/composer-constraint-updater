# Composer constraint updater
[![Tests](https://github.com/MartinsRucevskis/composer-constraint-updater/actions/workflows/php-unit.yml/badge.svg)](https://github.com/MartinsRucevskis/composer-constraint-updater/actions/workflows/php-unit.yml)
[![Code style](https://github.com/MartinsRucevskis/composer-constraint-updater/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/MartinsRucevskis/composer-constraint-updater/actions/workflows/php-cs-fixer.yml)
[![Static analysis](https://github.com/MartinsRucevskis/composer-constraint-updater/actions/workflows/php-stan.yml/badge.svg)](https://github.com/MartinsRucevskis/composer-constraint-updater/actions/workflows/php-stan.yml)
[![codecov](https://codecov.io/gh/MartinsRucevskis/composer-constraint-updater/graph/badge.svg?token=EHIZQNJ3CC)](https://codecov.io/gh/MartinsRucevskis/composer-constraint-updater)

---
Helps You automatically resolve composer conflicts

E.g. Need to upgrade your framework to the newest version? Just run
```bash
composer major-update --constraint=package/package:^10.0
```
Want to upgrade minor versions and also update your compose.json file to the up-to-date versions? Run
```
composer minor-update
```

## Installation

Use composer to automatically add it Your project

```bash
composer require martinsr/constraint-updater
```

## How to use

- Add it to Your project
- Specify Your needed constraints when running `composer major-update`
```bash
--constraint=package/package:^10.0
```
- Run the either `composer major-update` or `composer minor-update` with your params.

## How it works

### major-update

It will replace all your `composer.json` package versions with `*` except for packages You have added with `--constraint`.

Versions you add for the packages will be taken literally.

#### Examples:
`constraint=laravel/framework:^10.0` will set the version to `^10.0` `constraint=laravel/framework:10.0` will set it to `10.0`.

Would suggest to always add the `^` since composer will still keep the major version the same, while updating to the newest minor version other packages support.

This will make composer install the most up-to-date versions possible, taken the constraints and there won't be any conflicts as long as there is a supported version.

After composer update has been run, it will fix your `composer.json` file from versions that were installed and specified in Your `composer.lock`

## minor-update

This will run `composer update` and after that fix the `composer.json` with the actual versions that were installed.

#### Examples:

Run
```bash
composer minor-update
```
Will run composer update command, and rebuild composer.json file to have up-to-date dependencies with the lock file

# How to contribute
1. Select a GitHub issue or create one
2. Pull the repository locally
3. For development use PHP 8.1
4. Run `composer install`
5. To check if everything is set up, run tests with `composer test`
6. Make your changes, add tests and fix code quality
7. Submit a pull request!🎉

## Code quality
To run complete code quality use:
```bash
composer code-quality
```
To run rector use:
```bash
composer rector
```
To run php-stan use:
```bash
composer php-stan
```
To convert unsafe functions use:
```bash
composer use-safe
```
To fix code styling use:
```bash
composer php-cs-fixer
```
## Testing
To run tests use
```bash
composer test
```