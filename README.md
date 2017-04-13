# Shoper JSON API Client

## Requirements and dependencies

You need PHP 5.5 and later. Additionally, make sure that the following PHP extensions are installed on your server:
- [`curl`](https://secure.php.net/manual/en/book.curl.php),
- [`json`](https://secure.php.net/manual/en/book.json.php)


## Composer

You can install the client via [Composer](http://getcomposer.org/) by running the command:

```bash
composer require monetivo/shoper-json-api-php
```

Then use Composer's [autoload](https://getcomposer.org/doc/00-intro.md#autoloading) mechanism:

```php
require_once('vendor/autoload.php');
```

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/monetivo/shoper-json-api-php/releases). Then include the `autoload.php` file.

```php
require_once('/path/to/shoper-json-api-php/autoload.php');
```

## Getting Started

Basic usage looks like:

```php
<?php

try {
    // Shoper shop url
    $shopUrl = 'https://shop.url/';

    // webapi user login
    $login = 'webapi';

    // webapi user password
    $password = 's33m$d!FFicult';

    // init client
    $client = new \Monetivo\ShoperJsonApi($shopUrl);

    // try login, throws exception on fail
    $client->login($login, $password);

    //example - get order list
    $orderList = $client->orderList(true, false, null);

    $client->logout();
}
catch (\Monetivo\Exceptions\MonetivoException $e)
{
    // Shoper error message
    $e->getMessage();
    // Shoper error code
    $e->getCode();
}
```

## Documentation

See https://www.shoper.pl/api for information about acquiring webapi credentials and documentation of available methods.

## Issues

If you find any issues, please do not hesitate to file them via GitHub.