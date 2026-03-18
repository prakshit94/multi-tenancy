<?php

// Using absolute path for vendor/autoload.php
require 'd:/Multi-Tenancy/vendor/autoload.php';
$app = require_once 'd:/Multi-Tenancy/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;

$customer = Customer::first();
if (!$customer) {
    echo "No customer found.\n";
    exit(1);
}

$initialBalance = (float) $customer->outstanding_balance;
echo "Initial Balance: $initialBalance\n";

// Simulate the same logic as in the controller
$refundAmount = 400.00;
$customer->increment('outstanding_balance', $refundAmount);
$customer->refresh();

$newBalance = (float) $customer->outstanding_balance;
echo "Balance after simulating refund (+400): $newBalance\n";

if (abs($newBalance - ($initialBalance + $refundAmount)) < 0.01) {
    echo "SUCCESS: Balance correctly incremented.\n";
} else {
    echo "FAILURE: Balance logic mismatch. Expected: " . ($initialBalance + $refundAmount) . ", Got: $newBalance\n";
}

// Revert
$customer->decrement('outstanding_balance', $refundAmount);
echo "Balance reverted.\n";
