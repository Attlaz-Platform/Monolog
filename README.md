# Attlaz Monolog integration

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

// Create a log channel
$log = new Logger('name');

$attlazClient = new Client('https://api.attlaz.com', '<your-token>', '<your-token>');
$log->pushHandler(new AttlazHandler($attlazClient));

// Add records to the log
$log->warning('Foo');
$log->error('Bar');
```
