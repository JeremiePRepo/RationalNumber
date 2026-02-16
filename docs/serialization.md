# Serialization & Persistence

This document covers JSON serialization, array conversion, and persistence strategies for `RationalNumber` objects.

## Table of Contents

- [JSON Serialization](#json-serialization)
- [Array Conversion](#array-conversion)
- [Practical Use Cases](#practical-use-cases)

## JSON Serialization

The `RationalNumber` class implements `JsonSerializable` for seamless JSON encoding:

### Serializing to JSON

```php
use RationalNumber\RationalNumber;

$price = RationalNumber::fromFloat(99.99);
$json = json_encode($price);
echo $json;
// {"numerator":9999,"denominator":100,"float":99.99,"string":"9999/100"}
```

### Deserializing from JSON

```php
$jsonString = '{"numerator":3,"denominator":4}';
$number = RationalNumber::fromJson($jsonString);
echo $number->toString();  // "3/4"
```

## Array Conversion

### Export to Array

For minimal storage format:

```php
use RationalNumber\RationalNumber;

$number = new RationalNumber(5, 8);
$array = $number->toArray();
var_export($array);
// array('numerator' => 5, 'denominator' => 8)
```

### Restore from Array

```php
$restored = RationalNumber::fromArray($array);
echo $restored->toString();  // "5/8"
```

## Practical Use Cases

### Cache Storage

```php
use RationalNumber\RationalNumber;

// Store in cache
$price = RationalNumber::fromFloat(99.99);
$priceData = $price->toArray();
// ... store $priceData in cache/database ...

// Later, retrieve from cache
// ... load $priceData from cache/database ...
$cachedPrice = RationalNumber::fromArray($priceData);
```

### API Response

```php
use RationalNumber\RationalNumber;

$response = [
    'subtotal' => RationalNumber::fromFloat(150.00),
    'tax' => RationalNumber::fromFloat(30.00),
    'total' => RationalNumber::fromFloat(180.00)
];

echo json_encode($response, JSON_PRETTY_PRINT);
// {
//     "subtotal": {
//         "numerator": 150,
//         "denominator": 1,
//         "float": 150.0,
//         "string": "150/1"
//     },
//     "tax": {
//         "numerator": 30,
//         "denominator": 1,
//         "float": 30.0,
//         "string": "30/1"
//     },
//     "total": {
//         "numerator": 180,
//         "denominator": 1,
//         "float": 180.0,
//         "string": "180/1"
//     }
// }
```

### Database Storage

For database storage, use `toArray()` for compact storage:

```php
use RationalNumber\RationalNumber;

// Before saving to database
$price = RationalNumber::fromFloat(19.99);
$data = [
    'product_id' => 123,
    'price_numerator' => $price->getNumerator(),
    'price_denominator' => $price->getDenominator()
];
// ... INSERT INTO products ...

// After loading from database
$loaded = new RationalNumber(
    $row['price_numerator'],
    $row['price_denominator']
);
```

### Session Storage

```php
use RationalNumber\RationalNumber;

// Store in session
$_SESSION['cart_total'] = RationalNumber::fromFloat(99.99)->toArray();

// Retrieve from session
$cartTotal = RationalNumber::fromArray($_SESSION['cart_total']);
```
