# Percentage Operations

This document covers all percentage-related operations available in the RationalNumber library.

## Table of Contents

- [Converting to Percentage](#converting-to-percentage)
- [Creating from Percentage](#creating-from-percentage)
- [Calculations](#calculations)
- [Legacy PercentageCalculator](#legacy-percentagecalculator)

## Converting to Percentage

Convert a rational number to a percentage string:

```php
use RationalNumber\RationalNumber;

$half = new RationalNumber(1, 2);
echo $half->toPercentage(2);  // "50.00%"

$threeFourths = new RationalNumber(3, 4);
echo $threeFourths->toPercentage();  // "75.00%"

// Custom precision
$oneThird = new RationalNumber(1, 3);
echo $oneThird->toPercentage(0);   // "33%"
echo $oneThird->toPercentage(2);   // "33.33%"
echo $oneThird->toPercentage(4);   // "33.3333%"
```

## Creating from Percentage

Create a rational number from a percentage string:

```php
use RationalNumber\RationalNumber;

$number = RationalNumber::fromPercentage("75%");
echo $number->toString();  // "3/4"

$fifty = RationalNumber::fromPercentage("50%");
echo $fifty->toString();  // "1/2"

$ten = RationalNumber::fromPercentage("10%");
echo $ten->toString();  // "1/10"
```

## Calculations

### Increase by Percentage

```php
use RationalNumber\RationalNumber;

$hundred = new RationalNumber(100);
$result = $hundred->increaseByPercentage("10%");
echo $result->getFloat();  // 110

// Practical example: Adding tax
$price = RationalNumber::fromFloat(99.99);
$withTax = $price->increaseByPercentage("20%");
echo $withTax->getFloat();  // 119.988
```

### Decrease by Percentage

```php
use RationalNumber\RationalNumber;

$twoHundred = new RationalNumber(200);
$result = $twoHundred->decreaseByPercentage("25%");
echo $result->getFloat();  // 150

// Practical example: Applying discount
$originalPrice = RationalNumber::fromFloat(50.00);
$discounted = $originalPrice->decreaseByPercentage("15%");
echo $discounted->getFloat();  // 42.5
```

### Calculate What Percentage One Number Is of Another

```php
use RationalNumber\RationalNumber;

$threeFourths = new RationalNumber(3, 4);
$one = RationalNumber::one();
echo $threeFourths->divideBy($one)->toPercentage();  // "75.00%"

// Practical example: Progress tracker
$completed = RationalNumber::fromFloat(7);
$total = RationalNumber::fromFloat(10);
$progress = $completed->divideBy($total)->toPercentage(1);
echo "Progress: " . $progress;  // "Progress: 70.0%"
```

## Practical Examples

### E-commerce Price Calculations

```php
use RationalNumber\RationalNumber;

// Original price
$price = RationalNumber::fromFloat(100.00);

// Apply 20% discount
$discounted = $price->decreaseByPercentage("20%");
echo "Sale Price: $" . $discounted->getFloat();  // $80.00

// Add 10% tax
$final = $discounted->increaseByPercentage("10%");
echo "Final Price: $" . $final->getFloat();  // $88.00

// Calculate total discount from original
$totalDiscount = $price->subtract($final);
$discountPercent = $totalDiscount->divideBy($price)->toPercentage(1);
echo "You saved: " . $discountPercent;  // "12.0%"
```

### Interest Calculations

```php
use RationalNumber\RationalNumber;

$principal = RationalNumber::fromFloat(1000);
$annualRate = "5%";  // 5% per year
$years = 10;

// Compound interest: P * (1 + r)^n
$rate = RationalNumber::fromPercentage($annualRate)->add(RationalNumber::one());
$final = $principal->multiply($rate->pow($years));

echo "Principal: $" . $principal->getFloat() . "\n";
echo "After {$years} years: $" . number_format($final->getFloat(), 2) . "\n";
```

### Grade Scaling

```php
use RationalNumber\RationalNumber;

$rawScore = RationalNumber::fromFloat(85);
$maxScore = RationalNumber::fromFloat(100);

// Calculate percentage
$percentage = $rawScore->divideBy($maxScore)->toPercentage(1);
echo "Score: " . $percentage;  // "85.0%"

// Apply curve (increase by 5%)
$curved = $rawScore->increaseByPercentage("5%");
$curvedPercentage = $curved->divideBy($maxScore)->toPercentage(1);
echo "Curved Score: " . $curvedPercentage;  // "89.3%"
```

## Legacy PercentageCalculator

**Note:** The `PercentageCalculator` class is deprecated. Use the convenience methods on `RationalNumber` instances instead (shown above).

For backward compatibility, `PercentageCalculator` still exists but will trigger deprecation warnings:

```php
use RationalNumber\RationalNumber;
use RationalNumber\Calculator\PercentageCalculator;

// Deprecated: This will trigger E_USER_DEPRECATED warnings

$calculator = new PercentageCalculator();

// Convert to percentage
$half = new RationalNumber(1, 2);
echo $calculator->toPercentage($half, 2);  // "50.00%"

// Create from percentage
$number = $calculator->fromPercentage("75%");
echo $number->toString();  // "3/4"

// Increase by percentage
$hundred = new RationalNumber(100);
$result = $calculator->increaseBy($hundred, "10%");
echo $result->getFloat();  // 110

// Decrease by percentage
$twoHundred = new RationalNumber(200);
$result = $calculator->decreaseBy($twoHundred, "25%");
echo $result->getFloat();  // 150

// Calculate what percentage one number is of another
$threeFourths = new RationalNumber(3, 4);
$one = RationalNumber::one();
echo $calculator->percentageOf($threeFourths, $one);  // "75.00%"
```

**Migration Guide:**

Replace deprecated calculator methods with the new direct methods:

```php
// Old (deprecated)
$calculator = new PercentageCalculator();
$result = $calculator->toPercentage($number, 2);
$result = $calculator->increaseBy($number, "10%");
$result = $calculator->decreaseBy($number, "10%");

// New (recommended)
$result = $number->toPercentage(2);
$result = $number->increaseByPercentage("10%");
$result = $number->decreaseByPercentage("10%");
```
