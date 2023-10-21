# RationalNumber

The `RationalNumber` class is a PHP implementation for handling rational numbers with precise arithmetic operations and percentage conversions. This class is designed to provide an accurate representation of rational numbers, helping you avoid issues related to rounding errors.

## Table of Contents

- [Features](#features)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
- [Usage](#usage)

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
$number1 = new RationalNumber(3, 4);
$number2 = new RationalNumber(1, 2);

$result = $number1->add($number2);
echo "Addition: " . $result->toString() . "\n";
```