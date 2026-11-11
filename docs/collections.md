# Working with Collections

The `RationalCollection` class provides powerful batch processing capabilities for handling multiple rational numbers at once.

## Table of Contents

- [Creating Collections](#creating-collections)
- [Aggregate Operations](#aggregate-operations)
- [Transformations](#transformations)
- [Filtering](#filtering)
- [Array Access and Iteration](#array-access-and-iteration)
- [Chaining Operations](#chaining-operations)

## Creating Collections

```php
use RationalNumber\RationalNumber;
use RationalNumber\Collection\RationalCollection;

// Create a collection
$grades = new RationalCollection([
    RationalNumber::fromFloat(15.5),
    RationalNumber::fromFloat(17),
    RationalNumber::fromFloat(14.25),
    RationalNumber::fromFloat(16)
]);

// Create empty collection
$collection = new RationalCollection();
```

## Aggregate Operations

### Calculate Average

```php
$average = $grades->average();
echo $average->getFloat();  // 15.6875
```

### Find Min and Max

```php
$min = $grades->min();
$max = $grades->max();
echo $min->getFloat();  // 14.25
echo $max->getFloat();  // 17.0
```

### Calculate Sum

```php
$total = $grades->sum();
echo $total->getFloat();  // 62.75
```

## Transformations

### Map: Apply Operation to All Elements

```php
use RationalNumber\RationalNumber;
use RationalNumber\Collection\RationalCollection;

$prices = new RationalCollection([
    RationalNumber::fromFloat(100),
    RationalNumber::fromFloat(50),
    RationalNumber::fromFloat(75)
]);

$withTax = $prices->map(fn($p) => $p->increaseByPercentage('20%'));
$totalWithTax = $withTax->sum();
echo $totalWithTax->getFloat();  // 270 (120 + 60 + 90)
```

## Filtering

### Filter: Select Elements Matching Condition

```php
use RationalNumber\RationalNumber;
use RationalNumber\Collection\RationalCollection;

$allPrices = new RationalCollection([
    RationalNumber::fromFloat(5),
    RationalNumber::fromFloat(15),
    RationalNumber::fromFloat(3),
    RationalNumber::fromFloat(20)
]);

$expensive = $allPrices->filter(
    fn($p) => $p->isGreaterThan(RationalNumber::fromFloat(10))
);
echo $expensive->count();  // 2
```

## Array Access and Iteration

### Array-like Access

```php
use RationalNumber\RationalNumber;
use RationalNumber\Collection\RationalCollection;

$collection = new RationalCollection([
    RationalNumber::fromFloat(1),
    RationalNumber::fromFloat(2),
]);
echo $collection[0]->getFloat();  // 1.0
echo isset($collection[5]) ? 'yes' : 'no';  // "no"
```

Collections are lists indexed from 0 to `count() - 1`. Reading by index (`$collection[0]`,
`isset()`) is the supported way to use array access.

> **Deprecated since 2.11:** writing through array access (`$collection[] = $n`,
> `$collection[0] = $n`, `unset($collection[0])`) and `clear()` trigger an `E_USER_DEPRECATED`
> warning. In 3.0 collections become immutable: use `$collection = $collection->add($n)`,
> `filter()` or `new RationalCollection([...])` instead. See [UPGRADE-3.0.md](../UPGRADE-3.0.md).

### Iteration

```php
foreach ($grades as $grade) {
    echo $grade->toString() . "\n";
}
```

### Check if Empty

```php
if (!$collection->isEmpty()) {
    echo "Collection has {$collection->count()} elements";
}
```

## Chaining Operations

Combine multiple operations for powerful data processing:

```php
use RationalNumber\RationalNumber;
use RationalNumber\Collection\RationalCollection;

$result = $collection
    ->filter(fn($n) => $n->isGreaterThan(RationalNumber::fromFloat(10)))
    ->map(fn($n) => $n->multiply(RationalNumber::fromFloat(2)))
    ->sum();
```

## Practical Examples

### Processing Student Grades

```php
use RationalNumber\RationalNumber;
use RationalNumber\Collection\RationalCollection;

$grades = new RationalCollection([
    RationalNumber::fromFloat(15.5),
    RationalNumber::fromFloat(17),
    RationalNumber::fromFloat(14.25),
    RationalNumber::fromFloat(16),
    RationalNumber::fromFloat(12)
]);

// Find students above average
$average = $grades->average();
$aboveAverage = $grades->filter(
    fn($g) => $g->isGreaterThan($average)
);

echo "Average: " . $average->getFloat() . "\n";
echo "Above average: " . $aboveAverage->count() . " students\n";
```

### Calculating Total Cost with Tax

```php
use RationalNumber\RationalNumber;
use RationalNumber\Collection\RationalCollection;

$cartItems = new RationalCollection([
    RationalNumber::fromFloat(29.99),
    RationalNumber::fromFloat(15.50),
    RationalNumber::fromFloat(42.00)
]);

$subtotal = $cartItems->sum();
$withTax = $subtotal->increaseByPercentage('20%');

echo "Subtotal: $" . $subtotal->getFloat() . "\n";
echo "With Tax: $" . $withTax->getFloat() . "\n";
```
