<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use RationalNumber\RationalNumber;

echo "=== RationalNumber Library Demo ===" . PHP_EOL . PHP_EOL;

// Basic arithmetic
echo "1. Basic Arithmetic:" . PHP_EOL;
$a = new RationalNumber(3, 4);
$b = new RationalNumber(1, 2);

echo "   a = {$a->toString()} ({$a->getFloat()})" . PHP_EOL;
echo "   b = {$b->toString()} ({$b->getFloat()})" . PHP_EOL;
echo "   a + b = {$a->add($b)->toString()}" . PHP_EOL;
echo "   a - b = {$a->subtract($b)->toString()}" . PHP_EOL;
echo "   a × b = {$a->multiply($b)->toString()}" . PHP_EOL;
echo "   a ÷ b = {$a->divideBy($b)->toString()}" . PHP_EOL;
echo PHP_EOL;

// Normalization
echo "2. Automatic Normalization:" . PHP_EOL;
$unnormalized = new RationalNumber(6, 8);
echo "   new RationalNumber(6, 8) = {$unnormalized->toString()}" . PHP_EOL;

$negative = new RationalNumber(3, -4);
echo "   new RationalNumber(3, -4) = {$negative->toString()}" . PHP_EOL;
echo PHP_EOL;

// From float
echo "3. Creating from Float:" . PHP_EOL;
$fromFloat = RationalNumber::fromFloat(2.5);
echo "   fromFloat(2.5) = {$fromFloat->toString()}" . PHP_EOL;

$fromNegative = RationalNumber::fromFloat(-0.75);
echo "   fromFloat(-0.75) = {$fromNegative->toString()}" . PHP_EOL;
echo PHP_EOL;

// Percentage operations
echo "4. Percentage Operations:" . PHP_EOL;
$price = new RationalNumber(100);
echo "   Original price: {$price->toString()}" . PHP_EOL;

$increased = $price->increaseByPercentage("15%");
echo "   After 15% increase: {$increased->toString()} ({$increased->getFloat()})" . PHP_EOL;

$discounted = $price->decreaseByPercentage("20%");
echo "   After 20% discount: {$discounted->toString()} ({$discounted->getFloat()})" . PHP_EOL;

$percentage = $a->toPercentage(2);
echo "   3/4 as percentage: {$percentage}" . PHP_EOL;
echo PHP_EOL;

// Reciprocal
echo "5. Reciprocal:" . PHP_EOL;
$num = new RationalNumber(5, 3);
$reciprocal = $num->reciprocal();
echo "   {$num->toString()} reciprocal = {$reciprocal->toString()}" . PHP_EOL;
echo PHP_EOL;

// Checking properties
echo "6. Checking Properties:" . PHP_EOL;
$zero = new RationalNumber(0, 1);
$integer = new RationalNumber(5, 1);
$fraction = new RationalNumber(5, 3);

echo "   {$zero->toString()} is zero? " . ($zero->isZero() ? "Yes" : "No") . PHP_EOL;
echo "   {$integer->toString()} is integer? " . ($integer->isInteger() ? "Yes" : "No") . PHP_EOL;
echo "   {$fraction->toString()} is integer? " . ($fraction->isInteger() ? "Yes" : "No") . PHP_EOL;
echo PHP_EOL;

// Exception handling
echo "7. Exception Handling:" . PHP_EOL;
try {
    $invalid = new RationalNumber(1, 0);
} catch (InvalidArgumentException $e) {
    echo "   ✓ Caught: {$e->getMessage()}" . PHP_EOL;
}

try {
    $zero = new RationalNumber(0, 1);
    $zero->reciprocal();
} catch (InvalidArgumentException $e) {
    echo "   ✓ Caught: {$e->getMessage()}" . PHP_EOL;
}

try {
    $num = new RationalNumber(5, 1);
    $zero = new RationalNumber(0, 1);
    $num->divideBy($zero);
} catch (InvalidArgumentException $e) {
    echo "   ✓ Caught: {$e->getMessage()}" . PHP_EOL;
}

echo PHP_EOL;
echo "=== Demo Complete ===" . PHP_EOL;
