# Migration Guide: v1.x → v2.x

This guide helps you migrate from RationalNumber v1.x to v2.x. Version 2.0 introduced significant architectural improvements following SOLID principles, which resulted in several breaking changes.

## Table of Contents

- [Why Migrate?](#why-migrate)
- [Breaking Changes Overview](#breaking-changes-overview)
- [Step-by-Step Migration](#step-by-step-migration)
- [Deprecation Notices](#deprecation-notices)
- [Support Timeline](#support-timeline)

---

## Why Migrate?

Version 2.x brings significant improvements:

- **SOLID Principles**: Cleaner architecture with interface-driven design
- **Type Safety**: Full PHP 8.3+ type hints and strict types
- **New Features**: Collections, advanced math, JSON serialization, rounding operations
- **Better Testing**: 231 tests with 499 assertions (was 14 tests, 27 assertions)
- **Critical Bug Fixes**: Negative number handling, division by zero protection
- **Performance**: Better overflow protection and error messages

---

## Breaking Changes Overview

### 1. Namespace Requirement

**Before (v1.x):**
```php
// No namespace
$number = new RationalNumber(1, 2);
```

**After (v2.x):**
```php
use RationalNumber\RationalNumber;

$number = new RationalNumber(1, 2);
```

**Migration:** Add `use RationalNumber\RationalNumber;` at the top of files using the class.

---

### 2. PHP Version Requirement

**Before:** PHP 7.0+  
**After:** PHP 8.3+

**Migration:** Ensure your project runs PHP 8.3 or later. Update `composer.json`:

```json
{
    "require": {
        "php": ">=8.3"
    }
}
```

---

### 3. Final Class

The `RationalNumber` class is now marked as `final` (value object best practice).

**Before (v1.x):**
```php
class MyCustomRational extends RationalNumber {
    // Custom methods
}
```

**After (v2.x):**
```php
// ❌ This will throw an error - cannot extend final class

// ✅ Use composition instead:
class MyCustomRational {
    private RationalNumber $number;
    
    public function __construct(RationalNumber $number) {
        $this->number = $number;
    }
    
    public function customMethod(): mixed {
        // Use $this->number...
    }
}
```

**Migration:** Refactor inheritance to composition. Wrap `RationalNumber` instead of extending it.

---

### 4. Percentage Methods (Deprecated in v2.x)

Percentage methods were moved to `PercentageCalculator` class in v2.0, then moved **back** to `RationalNumber` in v2.2+ with deprecation warnings.

**Before (v1.x):**
```php
$number = new RationalNumber(100, 1);
// Methods didn't exist or were basic
```

**v2.0-2.1 (Temporary):**
```php
use RationalNumber\Calculator\PercentageCalculator;

$number = new RationalNumber(100, 1);
$calculator = new PercentageCalculator();

// Had to use calculator
$result = $calculator->increaseBy($number, '20%');
```

**After (v2.2+):**
```php
use RationalNumber\RationalNumber;

$number = new RationalNumber(100, 1);

// ✅ RECOMMENDED: Use instance methods (native to RationalNumber)
$result = $number->increaseByPercentage('20%');      // 100 + 20% = 120
$result = $number->decreaseByPercentage('10%');      // 100 - 10% = 90
$result = $number->percentageOf('50%');               // 50% of 100 = 50
$percent = $number->toPercentage();                   // "10000%"

// ⚠️ DEPRECATED: PercentageCalculator still works but triggers warnings
// Will be removed in v3.0
$calculator = new PercentageCalculator();
$result = $calculator->increaseBy($number, '20%');  // Works but deprecated
```

**Migration:** If you were using `PercentageCalculator`, switch to instance methods on `RationalNumber`:

| PercentageCalculator (Deprecated) | RationalNumber (Current) |
|-----------------------------------|--------------------------|
| `$calc->increaseBy($num, '20%')` | `$num->increaseByPercentage('20%')` |
| `$calc->decreaseBy($num, '10%')` | `$num->decreaseByPercentage('10%')` |
| `$calc->percentageOf($num, '50%')` | `$num->percentageOf('50%')` |
| `$calc->toPercentage($num)` | `$num->toPercentage()` |
| `$calc->fromPercentage('25%')` | `RationalNumber::fromPercentage('25%')` |

---

### 5. Method Signatures (Interface Types)

Method parameters now accept interface types for better abstraction.

**Before (v1.x):**
```php
public function add(RationalNumber $number): RationalNumber
```

**After (v2.x):**
```php
use RationalNumber\Contract\ArithmeticOperations;

public function add(ArithmeticOperations $number): RationalNumber
```

**Impact:** This is mostly transparent. You can still pass `RationalNumber` instances (they implement `ArithmeticOperations`). However, custom implementations must implement the interfaces.

**Migration:** No changes needed for typical usage. If you have type checking in tests or custom implementations, update to accept interfaces.

---

### 6. Factory Pattern

Factory methods were added for better object creation patterns.

**Before (v1.x):**
```php
// Only constructor available
$number = new RationalNumber(1, 2);
```

**After (v2.x):**
```php
use RationalNumber\RationalNumber;
use RationalNumber\Factory\RationalNumberFactory;

// ✅ Option 1: Direct constructor (still works)
$number = new RationalNumber(1, 2);

// ✅ Option 2: Static factory methods (recommended)
$zero = RationalNumber::zero();
$one = RationalNumber::one();
$half = RationalNumber::fromFloat(0.5);
$quarter = RationalNumber::fromString('0.25');
$percent = RationalNumber::fromPercentage('25%');

// ✅ Option 3: Factory class (for dependency injection)
$factory = new RationalNumberFactory();
$number = $factory->create(1, 2);
$zero = $factory->zero();
```

**Migration:** No changes required, but consider using static factory methods for clarity and convenience.

---

## Step-by-Step Migration

### Step 1: Update Dependencies

Update your `composer.json`:

```json
{
    "require": {
        "php": ">=8.3",
        "jeremie-pasquis/rational-number": "^2.9"
    }
}
```

Run:
```bash
composer update jeremie-pasquis/rational-number
```

### Step 2: Add Namespace Imports

Add to files using `RationalNumber`:

```php
<?php

use RationalNumber\RationalNumber;
```

### Step 3: Replace Inheritance with Composition

If you extended `RationalNumber`, refactor to composition:

**Before:**
```php
class CustomRational extends RationalNumber {
    public function customMethod() {
        return $this->multiply(RationalNumber::fromFloat(2.5));
    }
}
```

**After:**
```php
use RationalNumber\RationalNumber;

class CustomRational {
    private RationalNumber $number;
    
    public function __construct(int $numerator, int $denominator) {
        $this->number = new RationalNumber($numerator, $denominator);
    }
    
    public function customMethod(): RationalNumber {
        return $this->number->multiply(RationalNumber::fromFloat(2.5));
    }
    
    // Delegate common methods if needed
    public function add(RationalNumber $other): RationalNumber {
        return $this->number->add($other);
    }
}
```

### Step 4: Migrate Percentage Calculator

Replace `PercentageCalculator` usage with instance methods:

**Before (if you used PercentageCalculator):**
```php
use RationalNumber\Calculator\PercentageCalculator;

$calc = new PercentageCalculator();
$price = new RationalNumber(100, 1);
$withTax = $calc->increaseBy($price, '20%');
```

**After:**
```php
use RationalNumber\RationalNumber;

$price = new RationalNumber(100, 1);
$withTax = $price->increaseByPercentage('20%');
```

### Step 5: Run Tests

Run your test suite to catch any remaining issues:

```bash
./vendor/bin/phpunit
```

### Step 6: Update Type Hints (Optional)

For better abstraction, update type hints to interfaces where appropriate:

```php
use RationalNumber\Contract\ArithmeticOperations;
use RationalNumber\Contract\Comparable;

function calculateTotal(ArithmeticOperations $price, ArithmeticOperations $quantity): RationalNumber {
    return $price->multiply($quantity);
}
```

---

## Deprecation Notices

### PercentageCalculator (Deprecated in v2.2+)

The `PercentageCalculator` class is deprecated and will be removed in v3.0.

**Current Status:**
- ✅ Still works in v2.x
- ⚠️ Triggers `E_USER_DEPRECATED` warnings
- ❌ Will be removed in v3.0

**Migration Path:**
```php
// OLD (works but deprecated)
$calc = new PercentageCalculator();
$result = $calc->increaseBy($number, '20%');

// NEW (recommended)
$result = $number->increaseByPercentage('20%');
```

---

## New Features in v2.x

Take advantage of new features:

### Collections (v2.9+)
```php
use RationalNumber\Collection\RationalCollection;

$prices = new RationalCollection([
    RationalNumber::fromFloat(10.50),
    RationalNumber::fromFloat(25.00),
    RationalNumber::fromFloat(15.75),
]);

$total = $prices->sum();                    // Sum all prices
$average = $prices->average();              // Calculate average
$withTax = $prices->map(fn($p) => $p->increaseByPercentage('20%'));
```

### Advanced Math (v2.9+)
```php
$base = RationalNumber::fromFloat(1.05);
$compound = $base->pow(10);              // Compound interest

$two = RationalNumber::fromFloat(2);
$sqrt = $two->sqrt(20);                  // √2 ≈ 1.414...
```

### Rounding (v2.9+)
```php
$price = RationalNumber::fromFloat(10.567);
$rounded = $price->round(100);           // Round to cents: 10.57
$floor = $price->floor();                 // 10
$ceil = $price->ceil();                   // 11
```

### JSON Serialization (v2.9+)
```php
$number = new RationalNumber(1, 2);

// Serialize
$json = json_encode($number);
$array = $number->toArray();

// Deserialize
$restored = RationalNumber::fromJson($json);
$restored = RationalNumber::fromArray($array);
```

---

## Support Timeline

| Version | Status | Support Until | Notes |
|---------|--------|---------------|-------|
| v1.x | **End of Life** | Not supported | Security fixes only on request |
| v2.x | **Active** | Ongoing | Current stable version |
| v3.0 | **Planned** | TBA | Will remove deprecated `PercentageCalculator` |

**Recommendations:**
- Migrate to v2.x immediately
- v1.x receives no updates or bug fixes
- Plan for v3.0 migration (remove `PercentageCalculator` usage now)

---

## Need Help?

If you encounter issues during migration:

1. **Check the documentation**: [docs/](.)
2. **Review the changelog**: [CHANGELOG.md](../CHANGELOG.md)
3. **Open an issue**: [GitHub Issues](https://github.com/jeremie-pasquis/rational-number/issues)
4. **Review tests**: [tests/](../tests/) directory has extensive examples

---

## Summary Checklist

- [ ] Update `composer.json` to require PHP 8.3+ and RationalNumber ^2.9
- [ ] Add `use RationalNumber\RationalNumber;` imports
- [ ] Replace class inheritance with composition (if applicable)
- [ ] Migrate `PercentageCalculator` to instance methods
- [ ] Run test suite
- [ ] Update type hints to interfaces (optional)
- [ ] Explore new features (Collections, JSON, Advanced Math)

---

**Last Updated:** February 17, 2026  
**Target Version:** RationalNumber 2.9.0
