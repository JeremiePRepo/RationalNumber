# Advanced Operations

This document covers advanced mathematical operations, rounding, and overflow protection.

## Table of Contents

- [Advanced Mathematical Operations](#advanced-mathematical-operations)
- [Rounding Operations](#rounding-operations)
- [Overflow Protection](#overflow-protection)

## Advanced Mathematical Operations

### Power (Exponentiation)

```php
use RationalNumber\RationalNumber;

// Power (exponentiation)
$base = RationalNumber::fromFloat(2);
$result = $base->pow(3);  // 2^3 = 8
echo $result->toString();  // "8/1"

// Negative exponents work via reciprocal
$result = $base->pow(-2);  // 2^-2 = 1/4
echo $result->toString();  // "1/4"

// Practical example: Compound interest calculation
$principal = RationalNumber::fromFloat(1000);
$rate = RationalNumber::fromPercentage('5%')->add(RationalNumber::one());  // 1.05
$years = 10;
$final = $principal->multiply($rate->pow($years));
echo $final->getFloat();  // ≈ 1628.89
```

### Square Root

`sqrt(int $decimals)` returns the square root rounded to the nearest multiple of `10^-$decimals`
(halves away from zero). The computation uses integer arithmetic only, so the result is exact
to the requested number of decimal places. `$decimals` must be between 0 and 9.

```php
use RationalNumber\RationalNumber;

echo (new RationalNumber(4))->sqrt(0)->toString();     // "2/1"
echo (new RationalNumber(1, 4))->sqrt(2)->toString();  // "1/2"

$sqrt2 = (new RationalNumber(2))->sqrt(6);
echo $sqrt2->toString();  // "707107/500000" (1.414214)
```

A negative number throws an `InvalidArgumentException`. If `value × 10^(2 × $decimals)` does not
fit in a 64-bit integer, an `IntegerOverflowException` is thrown: lower `$decimals` for large values.

### Min and Max Operations

```php
use RationalNumber\RationalNumber;

// Min and max operations
$a = RationalNumber::fromFloat(3.5);
$b = RationalNumber::fromFloat(2.8);

$min = $a->min($b);
echo $min->getFloat();  // 2.8

$max = $a->max($b);
echo $max->getFloat();  // 3.5

// Finding min/max in array
$prices = [
    RationalNumber::fromFloat(10.50),
    RationalNumber::fromFloat(8.75),
    RationalNumber::fromFloat(12.00)
];

$minPrice = array_reduce($prices, fn($currentMin, $p) => $currentMin->min($p), $prices[0]);
echo $minPrice->getFloat();  // 8.75
```

## Rounding Operations

### Round to Nearest

The `round()` method uses standard half-up rounding (rounds 0.5 away from zero).

```php
use RationalNumber\RationalNumber;

// Round to nearest integer
$number = RationalNumber::fromFloat(12.6);
$rounded = $number->round();
echo $rounded->toString();  // "13/1"

// Round to specific denominator (useful for currency)
$price = RationalNumber::fromFloat(12.3456);
$roundedPrice = $price->round(100);  // Round to cents
echo $roundedPrice->toString();  // "1235/100"
echo $roundedPrice->getFloat();   // 12.35
```

### Floor (Round Down)

```php
use RationalNumber\RationalNumber;

// Floor (round down)
$number = RationalNumber::fromFloat(5.9);
$floored = $number->floor();
echo $floored->toString();  // "5/1"

// Negative numbers floor toward negative infinity
$negative = RationalNumber::fromFloat(-3.2);
$floored = $negative->floor();
echo $floored->toString();  // "-4/1"
```

### Ceil (Round Up)

```php
use RationalNumber\RationalNumber;

// Ceil (round up)
$number = RationalNumber::fromFloat(5.1);
$ceiled = $number->ceil();
echo $ceiled->toString();  // "6/1"

// Calculate units needed (practical example)
$totalItems = RationalNumber::fromFloat(100);
$itemsPerUnit = RationalNumber::fromFloat(12);
$unitsNeeded = $totalItems->divideBy($itemsPerUnit)->ceil();
echo $unitsNeeded->toString();  // "9/1" (need 9 units for 100 items)
```

## Overflow Protection

The library automatically detects integer overflow in arithmetic operations and throws an `IntegerOverflowException` with helpful guidance:

```php
use RationalNumber\Exception\RationalNumberException;
use RationalNumber\RationalNumber;

try {
    $largeNum = new RationalNumber(PHP_INT_MAX, 2);
    $result = $largeNum->multiply($largeNum);  // Would cause overflow
} catch (RationalNumberException $e) {
    echo $e->getMessage();
    // "Operation would cause integer overflow during numerator multiplication.
    //  Consider using the GMP extension for arbitrary precision arithmetic."
}
```

**Key points:**
- Overflow detection applies to `multiply()`, `add()`, `subtract()` operations
- `pow()` can also trigger overflow, especially with large exponents
- `sqrt($decimals)` throws when `value × 10^(2 × $decimals)` does not fit in an integer
- Large intermediate values can overflow even if the final result would fit in memory
- Throws `RationalNumber\Exception\IntegerOverflowException` (an `\OverflowException`) before
  the overflow occurs
- Error messages suggest installing/using GMP extension for larger numbers
- Detection happens during cross-multiplication in addition/subtraction

**Exception hierarchy:** every exception thrown by the library implements
`RationalNumber\Exception\RationalNumberException`:

| Exception | Extends | Thrown for |
|-----------|---------|------------|
| `InvalidArgumentException` | `\InvalidArgumentException` | invalid input (format, zero denominator, INF/NAN, array, JSON) |
| `DivisionByZeroException` | `InvalidArgumentException` | `divideBy()` zero, `reciprocal()` of zero |
| `EmptyCollectionException` | `InvalidArgumentException` | `average()`, `min()`, `max()` of an empty collection |
| `IntegerOverflowException` | `\OverflowException` | any value that does not fit in a 64-bit integer (`\ArithmeticError` in 2.x) |
| `ImmutableCollectionException` | `\LogicException` | writing to a `RationalCollection` through array access (`[]=`, `unset()`) |

**For very large numbers**, consider installing the GMP extension:

```bash
# Ubuntu/Debian
sudo apt-get install php-gmp

# macOS (with Homebrew)
brew install gmp

# Windows
# Enable extension=gmp in php.ini
```

Note: The library currently detects overflow but doesn't automatically use GMP. This ensures predictable behavior and lets you choose when to use arbitrary precision arithmetic.

## Additional Operations

### Simplest Form

Every number is reduced to its simplest form when it is created:

```php
use RationalNumber\RationalNumber;

$number = new RationalNumber(6, 8);
echo $number->toString();  // "3/4" (automatically normalized)

// Negative denominators are normalized
$number = new RationalNumber(3, -4);
echo $number->toString();  // "-3/4" (negative moved to numerator)
```

### Scientific Notation Support

```php
use RationalNumber\RationalNumber;

// Scientific notation support in fromFloat()
$small = RationalNumber::fromFloat(1e-10);
echo $small->getFloat();  // 1.0E-10

$large = RationalNumber::fromFloat(1.5e10);
echo $large->getFloat();  // 15000000000
```
