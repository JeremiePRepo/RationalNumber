# Usage Guide

Everyday operations with `RationalNumber`: creating numbers, reading them, arithmetic and
comparisons. Every result shown in the comments is the actual output. For powers, square roots,
rounding and overflow handling, see [Advanced Operations](advanced.md).

## Table of Contents

- [Creating Numbers](#creating-numbers)
- [Reading a Number](#reading-a-number)
- [Arithmetic](#arithmetic)
- [Comparisons](#comparisons)
- [Immutability and Chaining](#immutability-and-chaining)
- [Errors](#errors)
- [Going Further](#going-further)

## Creating Numbers

### From a numerator and a denominator

```php
use RationalNumber\RationalNumber;

$half = new RationalNumber(1, 2);    // 1/2
$five = new RationalNumber(5);       // 5/1 (the denominator defaults to 1)
```

Every number is normalized when it is created: the fraction is reduced and the sign moves to
the numerator. Two equal values therefore always have the same numerator and denominator.

```php
echo new RationalNumber(6, 8);    // "3/4"
echo new RationalNumber(3, -4);   // "-3/4"
```

A zero denominator throws an `InvalidArgumentException`. `PHP_INT_MIN` throws an
`IntegerOverflowException`, because its opposite does not fit in an integer.

### From a string

`fromString()` parses fractions, integers, decimals and scientific notation exactly, with no
float involved. Surrounding spaces are ignored.

```php
RationalNumber::fromString('3/4');       // 3/4
RationalNumber::fromString('6/8');       // 3/4
RationalNumber::fromString('5');         // 5/1
RationalNumber::fromString('0.25');      // 1/4
RationalNumber::fromString('-12.50');    // -25/2
RationalNumber::fromString('1.5e-3');    // 3/2000
RationalNumber::fromString(' 3 / 4 ');   // 3/4
```

An invalid format (`'abc'`) or a zero denominator (`'1/0'`) throws an
`InvalidArgumentException`. A value that does not fit in 64-bit integers (`'1e30'`) throws an
`IntegerOverflowException`.

### From a float or an int

`fromFloat()` converts a float from its shortest decimal representation, the one PHP prints:
`0.1` becomes `1/10`, not the binary approximation actually stored in memory. Ints are accepted
too.

```php
RationalNumber::fromFloat(0.1);      // 1/10
RationalNumber::fromFloat(2.5);      // 5/2
RationalNumber::fromFloat(1e-10);    // 1/10000000000
RationalNumber::fromFloat(1.5e10);   // 15000000000/1
RationalNumber::fromFloat(7);        // 7/1
```

`INF` and `NAN` throw an `InvalidArgumentException`. Values too large (`1e30`) or too small
(`1e-30`) for 64-bit integers throw an `IntegerOverflowException`.

### Shortcuts

```php
RationalNumber::zero();                    // 0/1
RationalNumber::one();                     // 1/1
RationalNumber::fromPercentage('12.5%');   // 1/8, see the percentage guide
```

See [Percentage Operations](percentage.md) for everything about percentages.

## Reading a Number

```php
$number = new RationalNumber(-6, 8);

$number->getNumerator();     // -3 (always reduced)
$number->getDenominator();   // 4 (always positive)

echo $number->toString();    // "-3/4"
echo $number;                // "-3/4" (__toString())
echo "Value: $number";       // "Value: -3/4"
echo new RationalNumber(5);  // "5/1": integers keep the "/1"
```

`getFloat()` returns the value as a float. That is an approximation: use it for display or
for PHP functions that expect a float, not for further exact calculations.

```php
(new RationalNumber(1, 3))->getFloat();       // 0.3333333333333333
(new RationalNumber(1, 3))->toPercentage();   // "33.33%"
```

For arrays and JSON (`toArray()`, `jsonSerialize()`, `fromArray()`, `fromJson()`), see
[Serialization](serialization.md):

```php
(new RationalNumber(3, 4))->toArray();    // ['numerator' => 3, 'denominator' => 4]
json_encode(new RationalNumber(3, 4));    // {"numerator":3,"denominator":4,"float":0.75,"string":"3\/4"}
RationalNumber::fromJson('{"numerator":3,"denominator":4}');   // 3/4
```

## Arithmetic

Every operation returns a new, exact and reduced number.

```php
$a = new RationalNumber(3, 4);
$b = new RationalNumber(1, 6);

echo $a->add($b);        // "11/12"
echo $a->subtract($b);   // "7/12"
echo $a->multiply($b);   // "1/8"
echo $a->divideBy($b);   // "9/2"

echo $a->negate();                              // "-3/4"
echo (new RationalNumber(-3, 4))->abs();        // "3/4"
echo (new RationalNumber(-3, 4))->reciprocal(); // "-4/3"
```

This is where fractions beat floats:

```php
var_dump(0.1 + 0.2);   // float(0.30000000000000004)

echo RationalNumber::fromString('0.1')->add(RationalNumber::fromString('0.2'));   // "3/10"
```

`divideBy()` with zero and `reciprocal()` of zero throw a `DivisionByZeroException`. A result
that does not fit in 64-bit integers throws an `IntegerOverflowException`. See
[Overflow Protection](advanced.md#overflow-protection).

## Comparisons

```php
$half = new RationalNumber(1, 2);
$third = new RationalNumber(1, 3);

$half->equals(new RationalNumber(2, 4));            // true

$third->compareTo($half);                           // -1
$half->compareTo(new RationalNumber(2, 4));         // 0
$half->compareTo($third);                           // 1

$half->isGreaterThan($third);                       // true
$half->isLessThan($third);                          // false
$half->isGreaterThanOrEqual($third);                // true
$half->isLessThanOrEqual(new RationalNumber(2, 4)); // true

RationalNumber::zero()->isZero();                   // true
(new RationalNumber(8, 4))->isInteger();            // true
(new RationalNumber(7, 4))->isInteger();            // false
```

`compareTo()` returns -1, 0 or 1, so it works directly with `usort()`:

```php
$numbers = [new RationalNumber(3, 4), new RationalNumber(-1, 2), new RationalNumber(1, 3)];
usort($numbers, fn (RationalNumber $x, RationalNumber $y) => $x->compareTo($y));
// -1/2, 1/3, 3/4
```

### Min and max

```php
$a = RationalNumber::fromString('3.5');
$b = RationalNumber::fromString('2.8');

echo $a->min($b);   // "14/5" (2.8)
echo $a->max($b);   // "7/2" (3.5)

// Smallest value of an array
$prices = [
    RationalNumber::fromString('10.50'),
    RationalNumber::fromString('8.75'),
    RationalNumber::fromString('12'),
];
echo array_reduce($prices, fn (RationalNumber $min, RationalNumber $p) => $min->min($p), $prices[0]);   // "35/4" (8.75)
```

For many values, [`RationalCollection`](collections.md) provides `min()`, `max()`, `sum()` and
`average()`.

### Exact even for huge numbers

Comparisons never go through floats. They stay exact where floats can no longer tell two
values apart:

```php
$x = new RationalNumber(PHP_INT_MAX - 2, PHP_INT_MAX - 1);
$y = new RationalNumber(PHP_INT_MAX - 1, PHP_INT_MAX);

$x->isLessThan($y);                                                       // true (correct)
(PHP_INT_MAX - 2) / (PHP_INT_MAX - 1) < (PHP_INT_MAX - 1) / PHP_INT_MAX;  // false with floats
```

## Immutability and Chaining

A `RationalNumber` never changes: every operation returns a new number and leaves the
original untouched. Always use the result:

```php
$price = RationalNumber::fromString('19.99');
$price->add(RationalNumber::one());   // the result is discarded
echo $price;                          // "1999/100": $price is unchanged
```

Operations can therefore be chained:

```php
$total = RationalNumber::fromString('19.99')
    ->multiply(new RationalNumber(3))   // 3 items
    ->increaseByPercentage('20%')       // VAT
    ->round(100);                       // to the cent

echo $total;              // "1799/25"
echo $total->getFloat();  // 71.96
```

Numbers can be shared safely between calculations, with no defensive copies:

```php
$vat = RationalNumber::fromPercentage('20%');

echo RationalNumber::fromString('100')->increaseByPercentage($vat);   // "120/1"
echo RationalNumber::fromString('50')->increaseByPercentage($vat);    // "60/1"
echo $vat;                                                            // "1/5": still 20%
```

## Errors

| Method | Exception | When |
|--------|-----------|------|
| `new RationalNumber()` | `InvalidArgumentException` | zero denominator |
| `new RationalNumber()` | `IntegerOverflowException` | `PHP_INT_MIN` as numerator or denominator |
| `fromString()` | `InvalidArgumentException` | invalid format, zero denominator |
| `fromString()`, `fromFloat()` | `IntegerOverflowException` | value too large or too small for 64-bit integers |
| `fromFloat()` | `InvalidArgumentException` | `INF` or `NAN` |
| `divideBy()`, `reciprocal()` | `DivisionByZeroException` | division by zero |
| `add()`, `subtract()`, `multiply()`, `divideBy()` | `IntegerOverflowException` | the result does not fit in 64-bit integers |

All of them implement `RationalNumber\Exception\RationalNumberException`, so one `catch`
handles any error from the library:

```php
use RationalNumber\Exception\RationalNumberException;

try {
    $number = RationalNumber::fromString($userInput);
} catch (RationalNumberException $e) {
    echo "Invalid number: " . $e->getMessage();
}
```

See the [exception hierarchy](advanced.md#overflow-protection) for the parent class of each
exception.

## Going Further

- [Advanced Operations](advanced.md): `pow()`, `sqrt()`, rounding (`round()`, `floor()`,
  `ceil()`), overflow protection, and combining the library with PHP math functions
- [Percentage Operations](percentage.md): `toPercentage()`, `fromPercentage()`,
  `increaseByPercentage()`, `decreaseByPercentage()`
- [Collections](collections.md): sums, averages, min/max, `map()` and `filter()`
- [Serialization](serialization.md): JSON, arrays, storage
