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

- **Addition (10k operations)**: Repeatedly adding numbers
- **Subtraction (10k operations)**: Repeatedly subtracting numbers
- **Multiplication (10k operations)**: Repeatedly multiplying numbers
- **Division (10k operations)**: Repeatedly dividing numbers
- **Mixed Operations (10k total)**: Combination of all four operations

**Use Case:** Understand baseline performance for financial calculations, scientific computing, and general arithmetic.

### 2. Complex Operations

Tests advanced mathematical operations:

- **GCD Large Numbers (1k operations)**: Greatest Common Divisor on large integers
- **Power Operations (1k × 10^10)**: Raising to powers (e.g., compound interest)
- **Square Root (100 numbers, 10 iterations)**: Newton-Raphson approximation
- **Comparisons (40k operations)**: Greater than, less than, equals operations
- **Rounding Operations (30k total)**: Round, floor, ceil operations
- **Percentage Operations (30k total)**: Increase, decrease, percentage-of calculations

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

PHP Version: 8.3.x
Date: 2026-02-17 10:30:00

─────────────────────────────────────────────────────────
 Arithmetic Operations
─────────────────────────────────────────────────────────
Addition (10k operations)                     0.0234 s       427,350 ops/s
Subtraction (10k operations)                  0.0198 s       505,051 ops/s
...
```

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

## Baseline Performance (v2.9.0)

Reference baseline on PHP 8.3:

| Category | Operation | Expected Ops/Second |
|----------|-----------|---------------------|
| Arithmetic | Addition | ~400,000 |
| Arithmetic | Multiplication | ~300,000 |
| Complex | GCD Large Numbers | ~10,000 |
| Complex | Power Operations | ~50,000 |
| Collection | Sum (1000 elements) | ~0.01s total |

**Note:** Actual performance varies by hardware, PHP version, and system load.

## Regression Testing

To detect performance regressions:

1. **Run baseline benchmarks** on known-good version
2. **Save results** (automatically saved as `benchmark-results-YYYY-MM-DD-HHMMSS.md`)
3. **Compare with new versions** using saved results

**Acceptable Variance:** ±10% between runs (due to system load)  
**Regression Alert:** >20% slowdown indicates potential issue

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
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
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

**Last Updated:** February 17, 2026  
**Version:** RationalNumber 2.9.0
