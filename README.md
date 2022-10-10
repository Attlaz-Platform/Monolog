# Attlaz Monolog Handler

This package allows you to integrate [Attlaz](https://attlaz.com) into Monolog.

## Installation

Install the latest version with

```
$ composer require attlaz/attlaz-monolog
```

## Basic Usage

```php
<?php

use Monolog\Logger;
use \Attlaz\Client;
use \Attlaz\AttlazMonolog\Handler\AttlazHandler;

$attlazClient = new Client('<your-token>', '<your-token>');

$attlazHandler = new AttlazHandler($client, new LogStreamId('Vt9HtWRee'), Level::Info);

/** @var $logger Monolog\Logger */
$logger->pushHandler($attlazHandler);

// Add records to the log
$log->warning('Foo');
$log->error('Bar');
```

## About

### Requirements

- Attlaz Monolog `^2.0` works with PHP 8.1 and above
- Attlaz Monolog `^1.0` works with PHP 7.2 and above
- Attlaz Monolog `^0.0` works with PHP 5.3 up to 8.1 (No longer maintained)

### Documentation

- [Attlaz documentation](https://docs.attlaz.com)

