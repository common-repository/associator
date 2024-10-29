<?php

use Associator\Client;
use Associator\Associator;

require_once '../vendor/autoload.php';

$client = new Client();
$associator = new Associator($client);
$associator->setApiKey('bf357212-41d4-41de-a0a5-01372c939583');

$result = $associator->getAssociations([2], 0.5, 0.5);
