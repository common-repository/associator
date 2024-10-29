Associator PHP SDK
==================
This repository contains the open source PHP SDK that allows you to access the AssociatorAPI from your PHP application.

[![Build Status](https://travis-ci.org/tarnawski/associator-php-sdk.svg?branch=master)](https://travis-ci.org/tarnawski/associator-php-sdk)

### Installation

To install Associator PHP SDK, simply:
```
composer require tarnawski/associator-php-sdk
```

### Quick Start and Examples
```php
<?php

use Associator\Client;
use Associator\Associator;

require_once '../vendor/autoload.php';

$client = new Client();
$associator = new Associator($client);
$associator->setApiKey('bf357212-41d4-41de-a0a5-01372c939583');

$result = $associator->getAssociations([2], 0.5, 0.5);
```

### Prepare development environment:
In order to set application up you must follow by steps:
1. Install Docker ([docs.docker.com/install/](https://docs.docker.com/install/)) and docker-compose ([docs.docker.com/compose/install/](https://docs.docker.com/compose/install/))
2. Run containers (use -d flag for “detached” mode):
```text
docker-compose up -d
```
3. Open in your browser: [localhost:85](localhost:85)

### Test
The tests can be executed by running this command from the project directory:
```bash
./vendor/bin/phpunit
```