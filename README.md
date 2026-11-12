# RationalNumber

![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-blue)
![Tests](https://img.shields.io/badge/tests-371%20passing-brightgreen)
![License](https://img.shields.io/badge/license-MIT-green)
![CI](https://github.com/JeremiePRepo/RationalNumber/actions/workflows/ci.yml/badge.svg)
![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen)

A modern PHP library for precise rational number arithmetic. When floating-point math isn't accurate enough, `RationalNumber` guarantees exact calculations with fractions.

## Why not just use floats?

Floating-point arithmetic introduces precision errors that can break calculations:

```php
var_dump(0.1 + 0.2 === 0.3);  // false ❌
var_dump((0.1 + 0.2) - 0.3);  // 5.5511151231258E-17 ❌
```

`RationalNumber` guarantees exact arithmetic:

```php
use RationalNumber\RationalNumber;

$result = RationalNumber::fromFloat(0.1)
    ->add(RationalNumber::fromFloat(0.2));
    
echo $result->getFloat();  // 0.3 ✅
```

Perfect for financial calculations, scientific computing, and anywhere precision matters.

**Not sure if RationalNumber is right for you?** See our [comparison with alternative libraries](docs/comparison.md) (brick/math, GMP, BCMath, native floats).

### Comparison: float vs RationalNumber

| Feature | float | RationalNumber |
|---------|-------|----------------|
| `0.1 + 0.2 === 0.3` | ❌ | ✅ |
| Exact fractions | ❌ | ✅ |
| Speed | ✅ | ⚠️ |
| Memory usage | ✅ | ⚠️ |

## Features

### Core
- Precise arithmetic (add, subtract, multiply, divide)
- Comparison methods (equals, greater than, less than, etc.)
- Immutable & automatically normalized

### Advanced
- Power, square root, min, max
- Rounding (round, floor, ceil)
- Percentage support (increase, decrease, convert)
- Scientific notation support

### Infrastructure
- JSON serialization
- Collections for batch processing
- Overflow detection with GMP suggestions
- 371 tests, 760 assertions

## Requirements

- PHP 8.4 or later

**Performance note:** Rational numbers are slower than native floats due to normalization and overflow checks. Use them when precision matters more than raw speed.

## Installation

```bash
composer require jeremie-pasquis/rational-number
```

## Quick Start (5 minutes)

### Creating Rational Numbers

```php
use RationalNumber\RationalNumber;

// From numerator and denominator
$half = new RationalNumber(1, 2);      // 1/2

// From float
$twoAndHalf = RationalNumber::fromFloat(2.5);  // 5/2

// From string
$quarter = RationalNumber::fromString('0.25');  // 1/4
$pi = RationalNumber::fromString('22/7');       // 22/7

// Common values
$zero = RationalNumber::zero();
$one = RationalNumber::one();
```

### Basic Arithmetic

```php
$threeFourths = new RationalNumber(3, 4);  // 3/4
$oneHalf = new RationalNumber(1, 2);       // 1/2

// Addition
$result = $threeFourths->add($oneHalf);
echo $result->toString();  // "5/4"

// Subtraction, multiplication, division
$result = $threeFourths->subtract($oneHalf);   // "1/4"
$result = $threeFourths->multiply($oneHalf);   // "3/8"
$result = $threeFourths->divideBy($oneHalf);   // "3/2"

// Other operations
echo $threeFourths->reciprocal()->toString();  // "4/3"
echo $threeFourths->negate()->toString();      // "-3/4"
echo $threeFourths->abs()->toString();         // "3/4"
```

### Comparisons

```php
$half = new RationalNumber(1, 2);
$quarter = new RationalNumber(1, 4);

// Equality
if ($half->equals(new RationalNumber(2, 4))) {
    echo "Equal!";  // ✅
}

// Comparison methods
$half->isGreaterThan($quarter);         // true
$quarter->isLessThan($half);            // true
$half->isGreaterThanOrEqual($quarter);  // true
$half->compareTo($quarter);             // 1 (positive if greater)
```

### Working with Percentages

```php
// Convert to/from percentage
$half = new RationalNumber(1, 2);
echo $half->toPercentage(2);  // "50.00%"

$number = RationalNumber::fromPercentage("75%");
echo $number->toString();  // "3/4"

// Calculations
$price = RationalNumber::fromFloat(100);
$withTax = $price->increaseByPercentage("20%");
echo $withTax->getFloat();  // 120

$discounted = $price->decreaseByPercentage("15%");
echo $discounted->getFloat();  // 85
```

## Key Examples

### Financial Calculation

```php
use RationalNumber\RationalNumber;

// Calculate price with tax and discount
$originalPrice = RationalNumber::fromFloat(100.00);
$afterDiscount = $originalPrice->decreaseByPercentage("15%");  // $85.00
$finalPrice = $afterDiscount->increaseByPercentage("20%");     // $102.00

echo "Final Price: $" . $finalPrice->getFloat();  // $102.00
```

### Working with Collections

```php
use RationalNumber\RationalNumber;
use RationalNumber\Collection\RationalCollection;

$grades = new RationalCollection(
    RationalNumber::fromFloat(15.5),
    RationalNumber::fromFloat(17),
    RationalNumber::fromFloat(14.25),
    RationalNumber::fromFloat(16)
);

echo $grades->average()->getFloat();  // 15.6875
echo $grades->max()->getFloat();      // 17.0

// Apply operations to all elements
$withBonus = $grades->map(fn($g) => $g->increaseByPercentage('5%'));
foreach ($withBonus as $grade) {
    echo $grade->toString() . "\n";
}
```

### JSON Serialization

```php
use RationalNumber\RationalNumber;

$price = RationalNumber::fromFloat(99.99);
$json = json_encode($price);
// {"numerator":9999,"denominator":100,"float":99.99,"string":"9999/100"}

// Restore from JSON
$restored = RationalNumber::fromJson($json);
echo $restored->getFloat();  // 99.99
```

## Documentation

For advanced usage, see:

- **[Advanced Operations](docs/advanced.md)** - Power, sqrt, rounding, overflow protection
- **[Collections](docs/collections.md)** - Batch processing with RationalCollection
- **[Serialization](docs/serialization.md)** - JSON, arrays, persistence
- **[Percentage Operations](docs/percentage.md)** - Complete percentage guide
- **[Performance Benchmarks](docs/benchmarks.md)** - Performance testing and optimization
- **[Library Comparison](docs/comparison.md)** - Compare with brick/math, GMP, BCMath, floats

### Generating API Documentation

Generate HTML documentation from PHPDoc annotations:

```bash
composer docs
```

The generated documentation will be available at `docs/api/index.html`. For continuous documentation generation during development:

```bash
composer docs:watch
```

**Online Documentation:** [View API documentation](https://JeremiePRepo.github.io/RationalNumber/)

## Design

This library is built around clean design principles:

- **Immutable value objects** - `RationalNumber` is a `final readonly class`: all operations return new instances, ensuring thread-safety and predictable behavior
- **Precise errors** - Every exception implements `RationalNumber\Exception\RationalNumberException`, so one `catch` handles any library error
- **Exact by construction** - Decimal strings and floats are converted digit by digit, never rounded through a float

The design prioritizes precision, immutability, and developer experience while maintaining simplicity.

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit
composer phpstan   # static analysis (level max)
composer cs        # coding style (PSR-12)
```

**Current metrics:** 371 tests, 760 assertions

Coverage includes:
- All arithmetic and comparison operations
- Exception handling and edge cases
- Advanced math (pow, sqrt, min, max)
- Rounding operations
- Percentage calculations
- JSON serialization and collections
- PHP 8.4+ compatibility

## Creating Numbers

Use the constructor and the static constructors:

```php
use RationalNumber\RationalNumber;

$number = new RationalNumber(3, 4);
$number = RationalNumber::fromFloat(2.5);
$number = RationalNumber::fromPercentage("50%");
$number = RationalNumber::fromString("3/4");
```

## Upgrading from 2.x

3.0 removes the factory, `PercentageCalculator`, the `Contract` interfaces, `reduce()`,
`divideFrom()` and collection mutations. Upgrade to 2.11 first: each of them triggers a
deprecation warning that names its replacement. See [UPGRADE-3.0.md](UPGRADE-3.0.md) and the
[CHANGELOG](CHANGELOG.md).

## License

MIT License - Copyright (c) 2026 Jérémie Pasquis

See [LICENSE](LICENSE) file for details.
