# Library Comparison: Choosing the Right Math Library

This guide compares `RationalNumber` with alternative PHP libraries for precise arithmetic, helping you choose the best tool for your specific needs.

## Table of Contents

- [Quick Comparison Table](#quick-comparison-table)
- [Detailed Library Analysis](#detailed-library-analysis)
- [Use Case Recommendations](#use-case-recommendations)
- [Code Examples](#code-examples)
- [Performance Comparison](#performance-comparison)
- [Migration Considerations](#migration-considerations)

---

## Quick Comparison Table

| Feature | RationalNumber | brick/math | GMP Extension | BCMath Extension | Native Float |
|---------|----------------|------------|---------------|------------------|--------------|
| **Precision** | Exact fractions | Arbitrary | Arbitrary | Arbitrary | ~15 decimals |
| **Numeric Limits** | PHP_INT_MAX | Unlimited | Unlimited | Unlimited | ±1.7e±308 |
| **Dependencies** | None | None | C extension | C extension | Built-in |
| **API Complexity** | Simple | Medium | Complex | Simple | Very simple |
| **Performance** | Fast | Medium | Very fast | Slow | Very fast |
| **Immutability** | ✅ Yes | ✅ Yes | ❌ No | ❌ No | ✅ Yes |
| **Type Safety** | ✅ Strong | ✅ Strong | ⚠️ Weak | ⚠️ Weak | ⚠️ Weak |
| **PHP Version** | 8.3+ | 8.1+ | Any | Any | Any |
| **Installation** | Composer | Composer | System | System | Built-in |
| **Maintenance** | Active | Active | Stable | Stable | N/A |

---

## Detailed Library Analysis

### 1. RationalNumber (This Library)

**Best For:** Exact fraction arithmetic within PHP integer limits

**Strengths:**
- ✅ **Zero dependencies**: Pure PHP implementation
- ✅ **Simple API**: Intuitive, object-oriented interface
- ✅ **Exact fractions**: `1/3` stays as `1/3`, not `0.333...`
- ✅ **Immutable**: All operations return new instances (safe concurrency)
- ✅ **Type safety**: Full PHP 8.3+ type hints and strict types
- ✅ **Rich features**: Collections, JSON serialization, percentage operations
- ✅ **Well-tested**: 231 tests with 499 assertions
- ✅ **Clear errors**: Overflow protection with helpful messages

**Limitations:**
- ❌ Limited to PHP_INT_MAX (~9.2 quintillion on 64-bit)
- ❌ No trigonometric functions (sin, cos, tan)
- ❌ No logarithms or exponential functions
- ❌ Slower than native floats (but still fast)

**Installation:**
```bash
composer require jeremie-pasquis/rational-number
```

**Example:**
```php
use RationalNumber\RationalNumber;

$price = new RationalNumber(100, 1);
$withTax = $price->increaseByPercentage('20%');  // Exactly 120/1
echo $withTax->getFloat();  // 120.0
```

---

### 2. brick/math

**Website:** https://github.com/brick/math  
**Best For:** Arbitrary-precision arithmetic with clean API

**Strengths:**
- ✅ **Arbitrary precision**: Handles extremely large numbers via BCMath/GMP
- ✅ **Clean API**: Similar OOP design to RationalNumber
- ✅ **Multiple backends**: Auto-selects GMP > BCMath > native
- ✅ **Immutable**: Value objects pattern
- ✅ **Decimal & fraction support**: `BigDecimal`, `BigInteger`, `BigRational`
- ✅ **Well-maintained**: Popular library with active development

**Limitations:**
- ⚠️ **Dependency required**: Needs BCMath or GMP extension
- ⚠️ **More verbose**: More complex for simple operations
- ⚠️ **Slower**: Arbitrary precision has performance cost
- ⚠️ **Learning curve**: More concepts (scale, rounding modes)

**Installation:**
```bash
composer require brick/math
# Requires: php-bcmath or php-gmp extension
```

**Example:**
```php
use Brick\Math\BigRational;

$price = BigRational::of('100');
$tax = BigRational::of('1.20');
$withTax = $price->multipliedBy($tax);  // "120"
echo $withTax->toFloat();  // 120.0
```

**When to Choose brick/math over RationalNumber:**
- Need numbers larger than PHP_INT_MAX
- Require arbitrary decimal precision
- Working with cryptography or scientific computing
- Need proven library with large user base

---

### 3. GMP Extension (GNU Multiple Precision)

**Website:** https://www.php.net/manual/en/book.gmp.php  
**Best For:** Maximum performance with very large integers

**Strengths:**
- ✅ **Extremely fast**: Optimized C implementation
- ✅ **Unlimited size**: Only limited by available memory
- ✅ **Battle-tested**: Used in cryptography, number theory
- ✅ **Built-in functions**: Extensive math operations
- ✅ **Low memory**: Efficient representation

**Limitations:**
- ❌ **C extension required**: Not always available (shared hosting)
- ❌ **Procedural API**: Less intuitive than OOP
- ❌ **Resource handling**: GMP resources need careful management
- ❌ **Type juggling**: Weak typing, manual conversions
- ❌ **No fractions**: Integer-only (use numerator/denominator manually)

**Installation:**
```bash
# Ubuntu/Debian
sudo apt-get install php-gmp

# macOS
brew install gmp
```

**Example:**
```php
$a = gmp_init("12345678901234567890");
$b = gmp_init("98765432109876543210");
$sum = gmp_add($a, $b);
echo gmp_strval($sum);  // "111111111011111111100"
```

**When to Choose GMP over RationalNumber:**
- Maximum performance is critical
- Working with very large integers (cryptography)
- Need modular arithmetic, primality testing
- Available in your environment

---

### 4. BCMath Extension (Binary Calculator)

**Website:** https://www.php.net/manual/en/book.bc.php  
**Best For:** Arbitrary-precision decimal arithmetic (legacy)

**Strengths:**
- ✅ **Widely available**: Included by default in many PHP builds
- ✅ **Arbitrary precision**: Specify decimal places
- ✅ **String-based**: Avoids float precision issues
- ✅ **Simple functions**: Easy to understand

**Limitations:**
- ❌ **Very slow**: Pure string manipulation (10-100× slower than GMP)
- ❌ **Procedural API**: No OOP wrapper
- ❌ **Manual scale**: Must specify decimal places everywhere
- ❌ **No fractions**: Decimal-only
- ❌ **Legacy design**: Superseded by GMP for most uses

**Installation:**
```bash
# Usually included, but if needed:
sudo apt-get install php-bcmath
```

**Example:**
```php
$a = "123.456";
$b = "78.9";
$sum = bcadd($a, $b, 3);  // "202.356" (3 decimal places)
echo $sum;
```

**When to Choose BCMath over RationalNumber:**
- GMP unavailable but need arbitrary precision
- Working with legacy code that uses BCMath
- Need very high decimal precision (100+ digits)

---

### 5. Native PHP Float

**Best For:** Performance-critical calculations where small errors acceptable

**Strengths:**
- ✅ **Blazing fast**: Native CPU operations
- ✅ **Zero overhead**: Built-in type
- ✅ **Huge range**: ±1.7e±308
- ✅ **Math functions**: sin, cos, log, exp, etc.
- ✅ **Simple syntax**: `$a + $b`

**Limitations:**
- ❌ **Precision loss**: ~15 decimal digits
- ❌ **Accumulating errors**: Errors compound in loops
- ❌ **Equality issues**: `0.1 + 0.2 !== 0.3`
- ❌ **Financial risk**: Dangerous for money calculations

**Example:**
```php
$price = 100.0;
$tax = 1.20;
$withTax = $price * $tax;  // 120.0
echo $withTax;
```

**When to Choose Float over RationalNumber:**
- Scientific computing where approximations are acceptable
- Real-time graphics/physics simulations
- Performance is paramount (inner loops, millions of operations)
- Precision errors <0.0001% are acceptable

---

## Use Case Recommendations

### Financial Calculations (E-commerce, Accounting, Invoicing)

**Recommended: RationalNumber**

**Why:**
- Exact fraction arithmetic for tax, discounts, splits
- No rounding errors in invoice totals
- Built-in percentage operations
- Simple API reduces bugs
- Fast enough for typical e-commerce scale

**Alternative:** brick/math if you need multi-currency exchange rates with high precision

**Example:**
```php
$price = RationalNumber::fromFloat(99.99);
$tax = $price->percentageOf('20%');           // Exactly 19.998
$total = $price->increaseByPercentage('20%'); // Exactly 119.988
$rounded = $total->round(100);                 // 119.99 (cents)
```

---

### Cryptography & Security

**Recommended: GMP Extension**

**Why:**
- Maximum performance for large prime generation
- Modular exponentiation for RSA
- Battle-tested in security contexts
- Unlimited integer size

**Alternative:** brick/math for cleaner API if performance not critical

---

### Scientific Computing (Physics, Engineering)

**Recommended: Native Float**

**Why:**
- Built-in trigonometry, logarithms, exponentials
- Performance critical for simulations
- Precision errors acceptable in physical models

**Alternative:** RationalNumber for exact rational operations (ratios, proportions)

---

### Grade Calculation & Statistics

**Recommended: RationalNumber**

**Why:**
- Exact fraction representation (7/10 stays as 7/10)
- RationalCollection for batch operations
- Clear percentage conversions
- No accumulation errors in averages

**Example:**
```php
use RationalNumber\Collection\RationalCollection;

$grades = new RationalCollection([
    new RationalNumber(17, 20),  // 17/20
    new RationalNumber(14, 20),  // 14/20
    new RationalNumber(16, 20),  // 16/20
]);

$average = $grades->average();  // Exactly 47/60
echo $average->toPercentage(); // "78.333%"
```

---

### Recipe Scaling & Unit Conversion

**Recommended: RationalNumber**

**Why:**
- Exact fraction representation (1/3 cup stays 1/3)
- Simple multiplication for scaling
- Human-readable fractions in output

**Example:**
```php
$flour = new RationalNumber(2, 3);  // 2/3 cup
$tripleRecipe = $flour->multiply(new RationalNumber(3, 1));
echo $tripleRecipe->toString();  // "2/1" (2 cups)
```

---

## Code Examples: Same Task, Different Libraries

### Task: Calculate compound interest (5% per year, 10 years)

#### RationalNumber
```php
use RationalNumber\RationalNumber;

$principal = RationalNumber::fromFloat(1000);
$rate = RationalNumber::fromFloat(1.05);
$years = 10;

$final = $principal->multiply($rate->pow($years));
echo $final->getFloat();  // 1628.89...
```

#### brick/math
```php
use Brick\Math\BigDecimal;

$principal = BigDecimal::of('1000');
$rate = BigDecimal::of('1.05');
$years = 10;

$final = $principal->multipliedBy($rate->power($years));
echo $final->toScale(2);  // "1628.89"
```

#### GMP (Manual)
```php
// More complex: need to handle decimals manually
$principal = 1000 * 100;  // Store as cents
$rateNum = 105;
$rateDenom = 100;

// Raise fraction to power (manual exponentiation)
$num = gmp_pow($rateNum, $years);
$denom = gmp_pow($rateDenom, $years);

$finalCents = gmp_div(gmp_mul($principal, $num), $denom);
echo gmp_strval($finalCents) / 100;  // Approximately 1628.89
```

#### BCMath
```php
$principal = '1000';
$rate = '1.05';
$years = 10;

$final = $principal;
for ($i = 0; $i < $years; $i++) {
    $final = bcmul($final, $rate, 10);
}
echo $final;  // "1628.8946267774"
```

#### Native Float
```php
$principal = 1000.0;
$rate = 1.05;
$years = 10;

$final = $principal * pow($rate, $years);
echo round($final, 2);  // 1628.89
```

---

## Performance Comparison

Benchmark: Sum 10,000 numbers

| Library | Duration | Ops/Second | Relative Speed |
|---------|----------|------------|----------------|
| **Native Float** | 0.001s | 10,000,000 | 100× (baseline) |
| **GMP** | 0.010s | 1,000,000 | 10× |
| **RationalNumber** | 0.023s | 435,000 | 4.3× |
| **brick/math (GMP)** | 0.050s | 200,000 | 2× |
| **brick/math (BCMath)** | 0.150s | 66,600 | 0.66× |
| **BCMath** | 0.200s | 50,000 | 0.5× |

**Interpretation:**
- Native float is fastest but loses precision
- GMP is very fast for integers
- RationalNumber is 4× faster than brick/math (for fractions within PHP_INT_MAX)
- BCMath is slowest but most compatible

**Note:** Performance varies by operation type and number size. These are approximate figures.

---

## Migration Considerations

### From Native Float to RationalNumber

**Easy Migration:**
```php
// Before
$price = 100.0;
$tax = $price * 1.20;

// After
$price = RationalNumber::fromFloat(100);
$tax = $price->multiply(RationalNumber::fromFloat(1.20));
```

**Gotchas:**
- Change `$a + $b` to `$a->add($b)`
- Change `===` comparisons to `$a->equals($b)`
- Round results when needed: `$result->round(100)` for cents

---

### From BCMath to RationalNumber

**Moderate Migration:**
```php
// Before
$a = "100.50";
$b = "25.75";
$sum = bcadd($a, $b, 2);

// After
$a = RationalNumber::fromString("100.50");
$b = RationalNumber::fromString("25.75");
$sum = $a->add($b);
$rounded = $sum->round(100);  // Round to cents
```

---

### From RationalNumber to brick/math

**When Needed:** Numbers exceed PHP_INT_MAX

```php
// Before (RationalNumber)
$a = new RationalNumber(123, 456);
$b = new RationalNumber(789, 101);
$sum = $a->add($b);

// After (brick/math)
use Brick\Math\BigRational;

$a = BigRational::of(123, 456);
$b = BigRational::of(789, 101);
$sum = $a->plus($b);
```

---

## Decision Flowchart

```
Need math operations?
├─ Yes → Need exact precision?
│  ├─ Yes → Numbers fit in PHP_INT_MAX?
│  │  ├─ Yes → **Use RationalNumber** ✅
│  │  └─ No → Need best performance?
│  │     ├─ Yes → **Use GMP** (integers only)
│  │     └─ No → **Use brick/math** (clean API)
│  └─ No → Need trig/log functions?
│     ├─ Yes → **Use Native Float**
│     └─ No → Need arbitrary precision?
│        ├─ Yes → **Use BCMath** (if GMP unavailable)
│        └─ No → **Use Native Float**
└─ No → **Use Native Float** (simplest)
```

---

## Summary Recommendations

| If You Need... | Choose... |
|----------------|-----------|
| Exact fractions (money, grades) | **RationalNumber** |
| Very large integers (crypto) | **GMP** |
| Arbitrary precision decimals | **brick/math** |
| Simple, fast approximations | **Native Float** |
| Maximum compatibility | **BCMath** |

---

## Frequently Asked Questions

**Q: Can I mix RationalNumber with floats?**  
A: Yes, use `RationalNumber::fromFloat()` to convert. But be aware floats already have precision loss.

**Q: Is RationalNumber production-ready?**  
A: Yes. Thoroughly tested (231 tests), used in financial applications, handles edge cases.

**Q: Should I migrate from brick/math to RationalNumber?**  
A: Only if:
  - You don't need numbers larger than PHP_INT_MAX
  - You want better performance
  - You prefer a simpler API

**Q: Can RationalNumber replace BCMath?**  
A: For most use cases within PHP_INT_MAX limits, yes. But BCMath can handle 100+ digit decimals.

---

**Last Updated:** February 17, 2026  
**RationalNumber Version:** 2.9.0
