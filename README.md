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
use Monolog\Level;
use Attlaz\Client;
use Attlaz\Model\Log\LogStreamId;
use Attlaz\AttlazMonolog\Handler\AttlazHandler;

$client = new Client();
$client->authWithToken('<your-token>');
// or: $client->authWithClient('<client-id>', '<client-secret>');

$handler = new AttlazHandler($client, new LogStreamId('Vt9HtWRee'), Level::Info);

$logger = new Logger('my-channel');
$logger->pushHandler($handler);

// Add records to the log
$logger->warning('Foo');
$logger->error('Bar');
```

## About

### Requirements

- Attlaz Monolog `^2.2` works with PHP 8.2 and above
- Attlaz Monolog `^2.0` works with PHP 8.1 and above
- Attlaz Monolog `^1.0` works with PHP 7.2 and above
- Attlaz Monolog `^0.0` works with PHP 5.3 up to 8.1 (No longer maintained)

### Documentation

- [Attlaz documentation](https://docs.attlaz.com)

