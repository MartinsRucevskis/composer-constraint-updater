# Composer constraint upgrader

Helps You automatically resolve composer conflicts

E.g Need to upgrade your framework to the newest version? Just run
```bash
composer major-update -C=composerPath --constraint=package/package:^10.0 --constraint=php:^8.1
```
Want to upgrade minor versions and also update your compose.json file to the up-to-date versions? Run
```
composer minor-update -C=composerPath
```

## Installation

Use composer to automatically add it Your project

```bash
composer require martinsr/constraint-updater
```

## How to use

- Add it to Your project
- Specify your composer location when running any of the commands
```
-C=composer/json/path
```
- Specify Your needed constraints when running `composer major-update`
```
--constraint=php:^8.1 --constraint=package/package:^10.0
```
- Run the either `composer major-update` or `composer minor-update` with your params.

## How it works

### update-major

It will replace all your `composer.json` package versions with `*` except for packages You have added with `--constraint`.

Versions you add for the packages will be taken literally.

#### Examples:
`constraint=laravel/framework:^10.0` will set the version to `^10.0` `constraint=laravel/framework:10.0` will set it to `10.0`.

Would suggest to always add the `^` since composer will still keep the major version the same, while updating to newest minor version other packages support.

This will make composer install the most up to date versions possible, taken the constraints and there won't be any conflicts.

After composer update has been run, it will fix your `composer.json` file from versions that were installed and specified in Your `composer.lock`

## update-minor

This will run `composer update` and after that fix the `composer.json` with the actual versions that were installed.
