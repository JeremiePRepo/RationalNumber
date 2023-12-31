# RationalNumber

The `RationalNumber` class is a PHP implementation for handling rational numbers with precise arithmetic operations and percentage conversions. This class is designed to provide an accurate representation of rational numbers, helping you avoid issues related to rounding errors.

## Table of Contents

- [Features](#features)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
- [Usage](#usage)
- [Examples](#examples)

## Features

- Supports precise arithmetic operations for rational numbers, including addition, subtraction, multiplication, and division.
- Conversion methods to create `RationalNumber` objects from floats, integers, and percentage values.
- Easy-to-use percentage calculations, allowing you to increase or decrease a number by a specified percentage.
- Methods to check if a rational number is zero, an integer, and more.
- Normalization of rational numbers to their simplest form.
- Exception handling for invalid input, such as a denominator set to zero.

## Getting Started

### Prerequisites

- PHP 7.2 or later

### Installation

To use the `RationalNumber` class, you can simply include it in your PHP project:

```php
require 'RationalNumber.php';
```

## Usage

To get started with the `RationalNumber` class, you can create a `RationalNumber` object using the constructor and perform various arithmetic operations. Here's a quick example:

```php
$number1 = new RationalNumber(3, 4); // 3/4
$number2 = new RationalNumber(1, 2); // 1/2

$result = $number1->add($number2);

echo "Addition: " . $result->toString() . "\n";
// Addition: 5/4
```

## Examples

Subtracting Rational Numbers

```php
$number1 = new RationalNumber(3, 4); // 3/4
$number2 = new RationalNumber(1, 2); // 1/2

$result = $number1->subtract($number2);

echo "Subtraction: " . $result->toString() . "\n";
// Subtraction: 1/4
```

Multiplying Rational Numbers

```php
$number1 = new RationalNumber(3, 4); // 3/4
$number2 = new RationalNumber(1, 2); // 1/2

$result = $number1->multiply($number2);

echo "Multiplication: " . $result->toString() . "\n";
// Multiplication: 3/8
echo "Float result: " . $result->getFloat() . "\n";
// Float result: 0.375
```

Dividing Rational Numbers

```php
$number1 = new RationalNumber(3, 4); // 3/4
$number2 = new RationalNumber(1, 2); // 1/2

$result = $number1->divideBy($number2);

echo "Division: " . $result->toString() . "\n";
// Division: 3/2
```

Dividing by a Rational Number

```php
$number1 = new RationalNumber(1, 2); // 1/2
$number2 = new RationalNumber(3, 4); // 3/4

$result = $number1->divideFrom($number2);

echo "Division: " . $result->toString() . "\n";
// Division: 2/3
```

Creating Rational Numbers from Float values

```php
$floatValue = 2.5;
$numberFromFloat = RationalNumber::fromFloat($floatValue);

echo "RationalNumber from float: " . $numberFromFloat->toString() . "\n";
// RationalNumber from float: 5/2
```

Normalizing Rational Numbers

```php
$unnormalizedNumber = new RationalNumber(6, 8); // 6/8
$normalizedNumber = $unnormalizedNumber->reduce();

echo "Normalized number: " . $normalizedNumber->toString() . "\n";
// Normalized number: 3/4
```

Creating a Rational Number from a Percentage

```php
$percentageValue = "50%";
$numberFromPercentage = RationalNumber::fromPercentage($percentageValue);

echo "RationalNumber from percentage: " . $numberFromPercentage->toString() . "\n";
// RationalNumber from percentage: 1/2
```

Increasing a Number by a Percentage

```php
$number = new RationalNumber(100);
$increasePercentage = "10%";
$increasedRationalNumber = $number->increaseByPercentage($increasePercentage);

echo "Increased by " . $increasePercentage . ": " . $increasedRationalNumber->toString() . "\n";
// Increased by 10%: 110/1
```

Decreasing a Number by a Percentage

```php
$number = new RationalNumber(200);
$decreasePercentage = "25%";
$decreasedRationalNumber = $number->decreaseByPercentage($decreasePercentage);

echo "Decreased by " . $decreasePercentage . ": " . $decreasedRationalNumber->toString() . "\n";
// Decreased by 25%: 150/1
```