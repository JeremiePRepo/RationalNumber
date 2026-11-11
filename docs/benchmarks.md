# Performance Benchmarks

This document explains how to run and interpret performance benchmarks for the RationalNumber library.

## Running Benchmarks

Execute all benchmarks using the Composer script:

```bash
composer benchmark
```

Or run directly:

```bash
php tests/Benchmarks/BenchmarkRunner.php
```

## Benchmark Categories

### 1. Arithmetic Operations

Tests basic arithmetic performance:

- **Addition (10k operations)**: Accumulating `+ 1` into a running total
- **Subtraction (10k operations)**: Accumulating `- 1` from a running total
- **Multiplication (10k operations)**: Multiplying the same two operands (`123.45 × 1.01`)
- **Division (10k operations)**: Dividing the same two operands (`10000 ÷ 3/2`)
- **Mixed Operations (10k total)**: An add → subtract → multiply → divide chain on fixed operands (2,500 × 4 operations)

> Multiplication, division and mixed operations use fixed operands on purpose: feeding the
> result back into the loop (e.g. multiplying by `1.01 = 101/100` 10,000 times) makes the
> denominator grow as `100^n` and overflows 64-bit integers after a few iterations.
> These benchmarks measure the cost of one operation, not the growth of the numbers.

**Use Case:** Understand baseline performance for financial calculations, scientific computing, and general arithmetic.

### 2. Complex Operations

Tests advanced mathematical operations:

- **GCD Large Numbers (1k operations)**: Greatest Common Divisor on large integers
- **Power Operations (1k × 10^10)**: Raising to powers (e.g., compound interest)
- **Square Root (100 numbers, 10 iterations)**: Newton-Raphson approximation
- **Comparisons (40k operations)**: Greater than, less than, equals operations
- **Rounding Operations (30k total)**: Round, floor, ceil operations
- **Percentage Operations (30k total)**: `increaseByPercentage()`, `decreaseByPercentage()` and `toPercentage()`

**Use Case:** Identify bottlenecks in scientific computing, financial modeling, and data analysis scenarios.

### 3. Collection Operations

Tests batch processing performance:

- **Collection Sum (1000 elements)**: Summing all elements
- **Collection Average (1000 elements)**: Calculating average
- **Collection Filter (1000 elements)**: Filtering with callback
- **Collection Map (1000 elements)**: Transforming with callback
- **Collection Min/Max (1000 elements)**: Finding minimum and maximum
- **Collection Chained Operations**: Filter → Map → Sum chain

**Use Case:** Understand performance for batch price calculations, grade processing, and data aggregation.

## Understanding Results

### Output Format

```
==========================================================
   RationalNumber Performance Benchmarks
==========================================================

PHP Version: 8.4.15
Date: 2026-11-11 20:38:47

─────────────────────────────────────────────────────────
 Arithmetic Operations
─────────────────────────────────────────────────────────
Addition (10k operations)                       0.0129 s       775,861 ops/s
Subtraction (10k operations)                    0.0108 s       922,596 ops/s
Multiplication (10k operations)                 0.0187 s       535,391 ops/s
...
```

Collection benchmarks report `N/A ops/s`: they time a single operation over 1,000 elements,
so only the duration is meaningful.

### Metrics Explained

- **Duration (s)**: Time taken to complete the benchmark in seconds
- **Ops/Second**: Operations per second (higher is better)
  - Calculated as: `total_operations / duration`
  - Example: 10,000 operations in 0.02s = 500,000 ops/s

### Interpreting Performance

**Good Performance:**
- Arithmetic operations: >100,000 ops/s
- Complex operations: >10,000 ops/s
- Collection operations: <0.1s for 1000 elements

**Performance Considerations:**

1. **GCD Dominates Normalization**: Creating fractions with large co-primes is slower due to GCD calculation
2. **Overflow Checks**: Safety checks add overhead but prevent runtime errors
3. **Immutability**: Each operation creates a new instance (memory allocations)
4. **Collection Chaining**: Multiple passes through data (filter, then map, then sum)

## Baseline Performance (v2.10.0)

Measured with `composer benchmark`, **median of 5 runs**.

**Environment:** PHP 8.4.15 CLI (ZTS, x64), Windows 11, Intel Core Ultra 7 255HX,
Xdebug disabled (`XDEBUG_MODE=off`), OPcache and JIT disabled for the CLI (PHP defaults).

| Category | Operation | Ops/Second (median) | Duration (median) |
|----------|-----------|--------------------:|------------------:|
| Arithmetic | Addition (10k) | ~840,000 | 11.9 ms |
| Arithmetic | Subtraction (10k) | ~1,000,000 | 10.0 ms |
| Arithmetic | Multiplication (10k) | ~645,000 | 15.5 ms |
| Arithmetic | Division (10k) | ~595,000 | 16.8 ms |
| Arithmetic | Mixed Operations (10k) | ~830,000 | 12.1 ms |
| Complex | GCD Large Numbers (1k) | ~625,000 | 1.6 ms |
| Complex | Power Operations (1k × `pow(10)`) | ~43,000 | 23.2 ms |
| Complex | Square Root (100) | ~147,000 | 0.7 ms |
| Complex | Comparisons (40k) | ~6,300,000 | 6.4 ms |
| Complex | Rounding Operations (30k) | ~545,000 | 55.2 ms |
| Complex | Percentage Operations (30k) | ~247,000 | 121.3 ms |
| Collection | Sum (1000 elements) | N/A | 1.1 ms |
| Collection | Average (1000 elements) | N/A | 1.0 ms |
| Collection | Filter (1000 elements) | N/A | 0.6 ms |
| Collection | Map (1000 elements) | N/A | 1.3 ms |
| Collection | Min/Max (1000 elements) | N/A | 0.4 ms |
| Collection | Chained Operations | N/A | 6.6 ms |

**Observations:**

- Comparisons are the fastest operations: a cross-multiplication, without creating a new instance.
- Multiplication and division are ~25% slower than addition: they run more overflow checks and
  division also computes a reciprocal.
- `pow(10)` performs 9 chained multiplications, which explains ~43,000 calls/s.
- Percentage operations are the slowest: each call parses the percentage string exactly
  (no float conversion, since v2.10.0). See [Optimization Tips](#optimization-tips).
- Run-to-run variance on this machine was about ±15% (the best run was up to 45% faster than the median),
  so always compare medians of several runs.

**Note:** Actual performance varies by hardware, PHP version, and system load. Numbers published
before v2.10.0 were not produced by this suite, which could not run at the time.

## Regression Testing

To detect performance regressions:

1. **Run baseline benchmarks** on known-good version
2. **Save results** (automatically saved as `benchmark-results-YYYY-MM-DD-HHMMSS.md`)
3. **Compare with new versions** using saved results

**Acceptable Variance:** ±15% between runs (due to system load); compare medians of at least 5 runs  
**Regression Alert:** >20% slowdown of the median indicates potential issue

Disable Xdebug when benchmarking (`XDEBUG_MODE=off composer benchmark`): it slows execution
down significantly and makes results meaningless.

## Optimization Tips

If benchmarks show performance issues:

### For Arithmetic Operations
- Minimize GCD calculations (use pre-reduced fractions)
- Cache frequently-used values (zero, one, common fractions)
- Batch operations when possible

### For Complex Operations
- Reduce sqrt precision parameter if high accuracy isn't needed
- Use integer power operations when possible
- Pre-compute power tables for repeated exponents

### For Percentages
- Parse a percentage once and reuse it: `$rate = RationalNumber::fromPercentage('20%')`, then
  `$price->increaseByPercentage($rate)` (since 2.11), instead of calling
  `increaseByPercentage('20%')` in a loop (which parses the string on every call)

### For Collections
- Use array operations directly if type safety isn't critical
- Consider lazy evaluation for chained operations
- Filter early in chain to reduce subsequent processing

## CI/CD Integration

To automatically track performance:

```yaml
# .github/workflows/benchmark.yml (optional)
name: Performance Benchmark

on:
  schedule:
    - cron: '0 0 * * 0'  # Weekly
  workflow_dispatch:

jobs:
  benchmark:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v6
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          coverage: none
      - run: composer install
      - run: composer benchmark
      - name: Check for regressions
        run: |
          # Compare with baseline (implement comparison logic)
          echo "Benchmark completed"
```

## Comparing with Alternatives

When choosing between RationalNumber and alternatives:

- **RationalNumber**: Best for exact fractions within PHP_INT_MAX limits
- **brick/math with BCMath**: Better for arbitrary precision (but slower)
- **Native floats**: 10-100× faster but lose precision

**Trade-off:** Precision vs. Performance

## Frequently Asked Questions

**Q: Why are benchmarks slower than native PHP floats?**  
A: RationalNumber guarantees exact arithmetic through GCD normalization and overflow checks. This correctness comes at a performance cost.

**Q: Should I optimize for benchmark results?**  
A: Only if benchmarks reveal bottlenecks in your use case. Premature optimization is counterproductive.

**Q: Can I contribute benchmark improvements?**  
A: Yes! Submit PRs with new benchmark scenarios or optimization suggestions.

---

**Last Updated:** November 11, 2026  
**Version:** RationalNumber 2.10.0
