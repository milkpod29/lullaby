# lullaby

## Requirements

* PHP version >= 5.5.9
* Laravel >= 5.1

## Installation

Install Composer

```
$ curl -sS https://getcomposer.org/installer | php
$ sudo mv composer.phar /usr/local/bin/composer
```

Add the following to your require block in composer.json config

```
"require-dev": {
  "milkpod29/lullaby": "~1.0.*"
}
```

## Configuration

To install into a Laravel project, first do the composer install then add *ONE *of the following classes to your config/app.php service providers list.

```php
Lullaby\Database\MigrationServiceProvider::class,
```

## Documentation

## Usage

### Migrations

```
$ php artisan lullaby:migration create_users_table --definition=/tmp/tables.xlsx
```
