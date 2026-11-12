# Advanced Operations

This document covers powers, square roots, rounding, overflow protection, and PHP math functions.
For everyday operations (creating, reading, arithmetic, comparisons), see the [Usage Guide](usage.md).

## Table of Contents

- [Advanced Mathematical Operations](#advanced-mathematical-operations)
- [Rounding Operations](#rounding-operations)
- [Overflow Protection](#overflow-protection)
- [Using PHP Math Functions](#using-php-math-functions)

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

A negative number throws an `InvalidArgumentException`. The root is computed digit by digit, so
intermediate values stay of the order of the result: an `IntegerOverflowException` is only thrown
when `sqrt(value) × 10^$decimals` exceeds about 4.6 × 10^17 (with 9 decimals, values above about
2 × 10^17). With 0 decimals, any value works.

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

The library works with 64-bit integers only. It detects every integer overflow before it happens
and throws an `IntegerOverflowException` instead of silently switching to floats:

```php
use RationalNumber\Exception\RationalNumberException;
use RationalNumber\RationalNumber;

try {
    $largeNum = new RationalNumber(PHP_INT_MAX, 2);
    $result = $largeNum->multiply($largeNum);  // Would cause overflow
} catch (RationalNumberException $e) {
    echo $e->getMessage();
    // "Integer overflow during multiplication: the result does not fit in a
    //  64-bit integer."
}
```

**Key points:**
- Overflow detection applies to `multiply()`, `add()`, `subtract()` operations
- `pow()` can also trigger overflow, especially with large exponents
- `sqrt($decimals)` throws when `sqrt(value) × 10^$decimals` exceeds about 4.6 × 10^17
- `multiply()` and `divideBy()` cancel common factors first: they throw only when the result
  itself does not fit (`(2^62/3) × (3/2^62)` is `1`). `add()` and `subtract()` also reduce by the
  common factor of the denominators; in rare cases an intermediate sum can still overflow when
  the result would fit
- Throws `RationalNumber\Exception\IntegerOverflowException` (an `\OverflowException`) before
  the overflow occurs
- The message names the operation that overflowed

**Exception hierarchy:** every exception thrown by the library implements
`RationalNumber\Exception\RationalNumberException`:

| Exception | Extends | Thrown for |
|-----------|---------|------------|
| `InvalidArgumentException` | `\InvalidArgumentException` | invalid input (format, zero denominator, INF/NAN, array, JSON) |
| `DivisionByZeroException` | `InvalidArgumentException` | `divideBy()` zero, `reciprocal()` of zero |
| `EmptyCollectionException` | `InvalidArgumentException` | `average()`, `min()`, `max()` of an empty collection |
| `IntegerOverflowException` | `\OverflowException` | any value that does not fit in a 64-bit integer (`\ArithmeticError` in 2.x) |
| `ImmutableCollectionException` | `\LogicException` | writing to a `RationalCollection` through array access (`[]=`, `unset()`) |

**For numbers beyond 64 bits**, this library is not the right tool: it does not use GMP or
BCMath, even when they are installed. Use an arbitrary precision library such as
[brick/math](https://github.com/brick/math) (`BigRational`) instead; see the
[library comparison](comparison.md).

## Using PHP Math Functions

The library has no trigonometric, logarithmic or exponential functions: their results are
irrational (sin(1), e, ln(2)…), so no fraction can hold them exactly. Combine it with PHP's math
functions instead:

1. convert to a float with `getFloat()` and call the PHP function;
2. bring the result back with `RationalNumber::fromFloat()`;
3. round it to the precision you need with `round()`: the float is only an approximation, so
   keeping its 17 significant digits makes the following operations heavier for nothing.

The float step is the only place where precision is lost. Everything before and after it stays
exact.

### Geometry: height of a ramp

```php
use RationalNumber\RationalNumber;

$length = RationalNumber::fromString('3.5');                    // metres

$sin30 = RationalNumber::fromFloat(sin(deg2rad(30)));
echo $sin30->toString();   // "24999999999999997/50000000000000000": the float error is visible

$sin30 = $sin30->round(1000000);                                // 6 decimal places
echo $sin30->toString();   // "1/2"

echo $length->multiply($sin30)->toString();   // "7/4" (1.75 m), exact from here on
```

### Finance: continuous compounding

`A = P × e^(r × t)`: only `e^(r × t)` goes through a float.

```php
$capital = RationalNumber::fromString('1000');
$rate = RationalNumber::fromPercentage('5%');
$years = 3;

$factor = RationalNumber::fromFloat(exp($rate->getFloat() * $years))->round(1000000000);
$amount = $capital->multiply($factor)->round(100);   // to the cent

echo $amount->getFloat();   // 1161.83
```

### Estimate with floats, verify exactly: doubling time

Logarithms give an estimate; `pow()` then checks it without any rounding error.

```php
$growth = RationalNumber::one()->add(RationalNumber::fromPercentage('10%'));   // 11/10 per year
$two = new RationalNumber(2);

$years = (int) ceil(log(2) / log($growth->getFloat()));   // 8

// Exact check: 1.1^8 >= 2 and 1.1^7 < 2
var_dump($growth->pow($years)->isGreaterThanOrEqual($two));   // true
var_dump($growth->pow($years - 1)->isLessThan($two));         // true
```

### Average growth rate (CAGR)

The ratio is exact; only its 4th root goes through a float.

```php
$start = RationalNumber::fromString('120000');
$end = RationalNumber::fromString('175000');

$ratio = $end->divideBy($start);   // 35/24, exact
$cagr = RationalNumber::fromFloat($ratio->getFloat() ** (1 / 4) - 1)->round(10000);

echo $cagr->toPercentage(2);   // "9.89%" per year
```

For results that must be exact end to end, keep to the library's own operations (`add()`,
`multiply()`, `pow()`, `sqrt($decimals)`…). For heavy scientific computing, native floats are
the right tool; see the [library comparison](comparison.md).
