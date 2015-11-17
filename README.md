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
"milkpod29/lullaby": "~1.0"
```

## Documentation

## Usage

### Migrations

```
$ php artisan make:migration create_users_table --definition=/tmp/tables.xlsx
```
