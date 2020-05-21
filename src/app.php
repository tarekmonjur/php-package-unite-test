<?php

require_once __DIR__ . './../vendor/autoload.php';

$transactionData = explode("\n", file_get_contents(__DIR__.'./../tests/input.txt'));

$calCom = new CalculateCommission($transactionData);
$commissions = $calCom->generateCommissions();
print_r($commissions);