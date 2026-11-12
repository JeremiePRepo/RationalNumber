# Upgrading from 2.x to 3.0

3.0 makes the API smaller and fully exact. Version 2.11 prepares it: everything 3.0 removes
triggers an `E_USER_DEPRECATED` warning in 2.11 that names its replacement.

**The short version:** upgrade to 2.11 first, run your test suite with deprecation warnings
visible, and fix each one using the table below. Code that runs without deprecation warnings on
2.11 is ready for 3.0, apart from the two points under
[Changes 2.11 cannot warn about](#changes-211-cannot-warn-about).

## Requirements

| | 2.x | 3.0 |
|---|---|---|
| PHP | 8.3+ | **8.4+** |
| Integers | 64-bit | 64-bit (no GMP support planned) |

## Removed APIs

| 2.x | 3.0 replacement |
|---|---|
| `RationalNumberFactory`, `RationalNumberFactoryInterface` | `new RationalNumber()`, `RationalNumber::fromString()`, `fromFloat()`, `fromPercentage()`, `zero()`, `one()` |
| `PercentageCalculator` | `$n->increaseByPercentage()`, `$n->decreaseByPercentage()`, `$n->toPercentage()`, `RationalNumber::fromPercentage()` |
| `Contract\ArithmeticOperations`, `Contract\Comparable`, `Contract\NumericValue` | type against `RationalNumber` |
| `$n->reduce()` | remove the call: numbers are always in their simplest form |
| `$divisor->divideFrom($dividend)` | `$dividend->divideBy($divisor)` |
| `$collection->clear()` | `new RationalCollection()` |
| `$collection[] = $n`, `$collection[$i] = $n` | `$collection = $collection->add($n)` or `new RationalCollection([...])` |
| `unset($collection[$i])` | `$collection = $collection->filter(...)` |

```php
// Creation: the factory is removed
$n = $factory->fromString('3/4');            // 2.x
$n = RationalNumber::fromString('3/4');      // 2.11 and 3.0

// Percentages: PercentageCalculator is removed
$price = (new PercentageCalculator())->increaseBy($price, '20%');   // 2.x
$price = $price->increaseByPercentage('20%');                        // 2.11 and 3.0

// Inverse division
$r = $divisor->divideFrom($dividend);        // 2.x
$r = $dividend->divideBy($divisor);          // 2.11 and 3.0

// Types: the interfaces are removed
function total(ArithmeticOperations $a) {}   // 2.x
function total(RationalNumber $a) {}         // 2.11 and 3.0
```

Method signatures change accordingly: `add()`, `subtract()`, `multiply()`, `divideBy()` and the
comparison methods take a `RationalNumber` instead of the removed interfaces.

## Changes 2.11 cannot warn about

### Overflow exceptions

In 2.x, overflows throw `RationalNumber\Exception\IntegerOverflowException`, which extends
`\ArithmeticError`. In 3.0 it extends `\OverflowException` instead, so `catch (\ArithmeticError)`
no longer catches it.

Catch the library type, which works with 2.11 and 3.0:

```php
use RationalNumber\Exception\IntegerOverflowException;
use RationalNumber\Exception\RationalNumberException;

try { $a->multiply($b); } catch (\ArithmeticError $e) {}           // 2.x only
try { $a->multiply($b); } catch (IntegerOverflowException $e) {}   // 2.11 and 3.0
try { $a->multiply($b); } catch (RationalNumberException $e) {}    // 2.11 and 3.0: any library error
```

The other exceptions keep extending `\InvalidArgumentException`.

### Immutable collections

In 3.0, `RationalCollection::add()` returns a new collection and leaves the original unchanged.
Calling it without using the result no longer has any effect:

```php
$collection->add($n);                 // 2.x: modifies $collection — 3.0: does nothing
$collection = $collection->add($n);   // 2.11 and 3.0
```

2.11 cannot detect an ignored return value. Search your code for `->add(` calls on collections
whose result is not assigned.

Writes through array access (`$collection[] = $n`, `$collection[$i] = $n`, `unset($collection[$i])`)
throw a `RationalNumber\Exception\ImmutableCollectionException`, which extends `\LogicException`.
They were deprecated in 2.11.

### `sqrt()` signature

`sqrt()` now takes a required number of decimal places for the result, between 0 and 9. In 2.x
its optional `$precision` argument was a number of approximation iterations (default and maximum
10). The result is the square root rounded to the nearest `10^-$decimals`, computed without floats:

```php
$root = $n->sqrt();     // 2.x: approximation; 3.0: ArgumentCountError
$root = $n->sqrt(10);   // 2.x: 10 iterations; 3.0: InvalidArgumentException (max 9 decimals)
$root = $n->sqrt(6);    // 3.0: rounded to 6 decimal places, e.g. 707107/500000 for 2
```

It throws an `IntegerOverflowException` only when `sqrt(value) × 10^$decimals` exceeds about
4.6 × 10^17. With 9 decimals, that means values above about 2 × 10^17. For such values, use fewer
decimal places. With 0 decimals, any value works.

## Behaviour changes

- `round()`, `floor()`, `ceil()` and `toPercentage()` use exact integer arithmetic instead of floats.
  Results are unchanged for usual values. Only numbers above 2^53, which were wrong before, change.
- `round($denominator)` throws an `IntegerOverflowException` when the rounded numerator does not fit
  in a 64-bit integer.
- `toPercentage($decimals)` accepts 0 to 16 decimal places, and throws an `InvalidArgumentException`
  otherwise. It never prints `-0`: a negative value that rounds to zero gives `0.00%`.
- Methods that took one of the removed interfaces now take a `RationalNumber`. Passing anything
  else throws a native `TypeError` instead of an `InvalidArgumentException`.

## Automating the migration

No dedicated Rector rules are provided: the changes are few and simple to find. If you use
[Rector](https://getrector.com), its generic `RenameClassRector` rule covers the type renames.
The other changes are better made by hand: `divideFrom()` swaps its operands, for example.

```php
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

return RectorConfig::configure()
    ->withConfiguredRule(RenameClassRector::class, [
        'RationalNumber\Contract\ArithmeticOperations' => 'RationalNumber\RationalNumber',
        'RationalNumber\Contract\Comparable' => 'RationalNumber\RationalNumber',
        'RationalNumber\Contract\NumericValue' => 'RationalNumber\RationalNumber',
    ]);
```

For everything else, the deprecation messages of 2.11 point to the exact line to change.
