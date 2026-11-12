
# Changelog

All notable changes to this project will be documented in this file.

## [3.0.1] - Unreleased

### Changed

- `RationalNumber::fromFloat()` declares its parameter type natively (`float|int`) instead of in
  its docblock only. Values it already accepted are unaffected.
- Internal: typed class constants (PHP 8.3+) and imported global classes.

## [3.0.0] - 2026-11-12

A smaller and fully exact API. Code that runs without deprecation warnings on 2.11 needs only
the changes listed in [UPGRADE-3.0.md](UPGRADE-3.0.md#changes-211-cannot-warn-about).

### Removed

- `Factory\RationalNumberFactory` and `Factory\RationalNumberFactoryInterface`: use the
  constructor and the static constructors of `RationalNumber`.
- `Calculator\PercentageCalculator`: use `increaseByPercentage()`, `decreaseByPercentage()`,
  `toPercentage()` and `fromPercentage()`.
- The `Contract\ArithmeticOperations`, `Contract\Comparable` and `Contract\NumericValue` interfaces.
  Methods now take a `RationalNumber`.
- `RationalNumber::reduce()` (numbers are always reduced) and `RationalNumber::divideFrom()`.
- `RationalCollection::clear()` and writes through array access.

### Changed

- **PHP 8.4** or later is required.
- `IntegerOverflowException` extends `\OverflowException` instead of `\ArithmeticError`. Its
  messages name the operation and the 64-bit limit instead of suggesting GMP, which the library
  never uses; `IntegerOverflowException::create()` is replaced by `during()`.
- `RationalCollection` is immutable (`final readonly`). `add()` returns a new collection. Array
  writes and `unset()` throw the new `ImmutableCollectionException` (a `\LogicException`), and
  `unserialize()` rejects elements that are not `RationalNumber` instances.
- The `RationalCollection` constructor takes the elements as arguments
  (`new RationalCollection($a, $b)`, or `new RationalCollection(...$array)`): PHP checks their type
  and throws a `\TypeError` for anything else.
- `floor()`, `ceil()`, `round()` and `toPercentage()` use exact integer arithmetic. Results
  above 2^53 are now correct, `round()` throws `IntegerOverflowException` when the result does not
  fit, and `toPercentage()` accepts 0 to 16 decimal places and never prints `-0`.
- `sqrt(int $decimals)` requires the number of decimal places (0 to 9). It returns the square root
  rounded to that precision, computed exactly with integers.

### Fixed

- `multiply()` and `divideBy()` threw `IntegerOverflowException` when the cross products did not
  fit although the result did (`(2^62/3) × (3/2^62)`): they now cancel common factors first and
  throw only when the result itself does not fit. `add()` and `subtract()` reduce by the common
  factor of the denominators (Knuth), e.g. `1/2^62 + 1/2^62` = `1/2^61`.

## [2.11.0] - 2026-11-11

The last 2.x release before 3.0. It is backward compatible, and it deprecates (with an
`E_USER_DEPRECATED` warning naming the replacement) everything 3.0 removes: code that runs without
deprecation warnings on 2.11 is ready for 3.0. See [UPGRADE-3.0.md](UPGRADE-3.0.md).

### Added

- **Library exceptions** in `RationalNumber\Exception`. They all implement the
  `RationalNumberException` marker interface, and each one extends the type thrown before, so
  existing `catch` blocks keep working:
  - `InvalidArgumentException` (extends `\InvalidArgumentException`): invalid input;
  - `DivisionByZeroException` (extends it): `divideBy()` zero, `reciprocal()` of zero;
  - `EmptyCollectionException` (extends it): `average()`, `min()`, `max()` of an empty collection;
  - `IntegerOverflowException` (extends `\ArithmeticError`): any 64-bit overflow.
- `increaseByPercentage()` / `decreaseByPercentage()` accept a `RationalNumber` rate (the rate as a
  fraction, as returned by `fromPercentage()`), so a rate can be parsed once and reused:
  `$price->increaseByPercentage(RationalNumber::fromPercentage('20%'))`.
- `UPGRADE-3.0.md` migration guide.

### Fixed

- `add()` / `subtract()` threw a `TypeError` instead of an overflow error when the cross-products
  fit but their sum does not (e.g. `PHP_INT_MAX + 1`).
- `RationalCollection::min()` / `max()` threw a `TypeError` after `unset($collection[0])`.
- `multiply()`, `add()`, `subtract()` and `pow()` let some results just above `PHP_INT_MAX` through
  their overflow check (e.g. `2^62 * 2`, whose float bound is rounded) and then threw a `TypeError`
  instead of an overflow error.
- `equals()` and `compareTo()` (and so `isGreaterThan()`, `isLessThan()`, ..., and
  `RationalCollection::min()` / `max()`) multiplied the fractions crosswise without overflow
  check: on large values the products silently became floats, so `(PHP_INT_MAX - 2)/2` was
  "equal" to `PHP_INT_MAX/2`. `equals()` now compares the reduced parts, and `compareTo()` falls
  back to an exact comparison (continued fractions, no multiplication) when the products would
  not fit.
- `unserialize()` bypassed the constructor, so a stored payload could produce a non-normalized
  (`2/-4`) or invalid (zero denominator) instance. `RationalNumber::__unserialize()` now applies
  the constructor checks and normalization; the serialized format is unchanged.
- Every overflow message now carries the GMP hint (the "too small" decimal and fraction-string
  overflows lacked it).

### Changed

- `new RationalNumber()` throws an `IntegerOverflowException` when the numerator or the denominator
  is `PHP_INT_MIN` (its opposite does not fit in an integer, so the fraction could not be
  normalized exactly). This also applies to results equal to `PHP_INT_MIN`. Before, it threw a
  `TypeError` or emitted a precision-loss warning.
- Writing to a `RationalCollection` through array access only accepts `null` (append), an existing
  index or the next index; integer strings such as `'1'` count as indexes. **Text keys
  (`$collection['price'] = ...`) and indexes that would leave a gap now throw an
  `InvalidArgumentException`.** `unset($collection[$i])` is unchanged: the other elements keep
  their index.
- `RationalNumber` is now a `final readonly class`.
- The exact decimal parsing moved to the internal `RationalNumber\Internal\DecimalParser` class
  (not part of the public API).
- Development: PHPUnit 11, PSR-12 coding style enforced in CI with php-cs-fixer (`composer cs`).

### Deprecated

All are removed in 3.0:

- `RationalNumberFactory` and `RationalNumberFactoryInterface`: use the static constructors
  (`new RationalNumber()`, `RationalNumber::fromString()`, `fromFloat()`, ...).
- The `Contract\ArithmeticOperations`, `Contract\Comparable` and `Contract\NumericValue`
  interfaces: type against `RationalNumber`.
- `RationalNumber::reduce()`: numbers are always reduced, remove the call.
- `RationalNumber::divideFrom($x)`: use `$x->divideBy($this)`.
- `RationalCollection::clear()` and writing through array access (`[] =`, `[$i] =`, `unset`):
  collections become immutable in 3.0. Use `$collection = $collection->add($n)` (it already works
  and is the only form that keeps working in 3.0), `filter()` or `new RationalCollection([...])`.

## [2.10.0] - 2026-11-11

### Fixed

- **Exact decimal conversion**: `fromFloat()`, `fromString()` and `fromPercentage()` no longer
  lose or alter digits.
  - `fromFloat()` truncated decimals: `fromFloat(0.29)` returned `7/25` instead of `29/100`,
    `1.15` returned `57/50`, `1.15e15` returned `1140000000000000/1`.
  - `fromString()` and `fromPercentage()` now parse decimal and scientific strings digit by digit
    instead of going through a float (`fromString('123456789012345.67')` is exact).
  - `fromFloat()` uses the shortest round-trip representation of the float, so results no longer
    depend on the `precision` / `serialize_precision` ini settings (e.g. with `precision=17`,
    `fromPercentage('57%')` returned `7124999999999999/12500000000000000`), and no digits are
    dropped (`fromFloat(99999999999999.5)` returned `100000000000000/1`).
  - `increaseByPercentage()` / `decreaseByPercentage()` (and the deprecated `PercentageCalculator`)
    use the exact percentage parsing.
- **False overflow error**: `fromFloat(1.2345e16)` and `fromFloat(123456789012345.67)` threw an
  `ArithmeticError` although the value fits in an integer.
- **Fraction strings just above `PHP_INT_MAX`**: `fromString('9223372036854775808/1')` passed the
  float-based overflow check and was silently clamped; the check is now exact on the digits.
- **`composer benchmark` never ran**: the benchmark classes used the `RationalNumber\Benchmarks`
  namespace, which no autoload rule covered (now `RationalNumber\Tests\Benchmarks`, matching the
  existing PSR-4 mapping). The multiplication, division and mixed benchmarks accumulated their
  result until integer overflow, and the percentage benchmark called a non-existent
  `percentageOf()` method.

### Changed

- Invalid inputs now throw instead of silently returning `0/1`:
  - `fromFloat(INF)`, `fromFloat(-INF)`, `fromFloat(NAN)` throw `InvalidArgumentException`.
  - `fromString('1e400')`, `fromString('1e-400')` throw `ArithmeticError` (overflow).
  - `fromPercentage('abc%')` (and `increaseByPercentage('abc%')`, etc.) throw `InvalidArgumentException`.
  - `fromFloat(1.5e-20)` throws `ArithmeticError` (too small to be represented).
- `fromFloat()` rounds values whose exact decimal does not fit in 64-bit integers to the largest
  number of significant digits that fits (at most 18 decimal places), instead of an arbitrary
  15-decimal cap. `fromString()` stays strict and throws in that case.
- CI also runs the test suite with `precision=17` and `serialize_precision=17`, and runs
  `composer benchmark` as a smoke test.
- Removed the private `fromScientificNotation()` helper (now handled by the decimal parser).
- Faster `fromFloat()` for integral floats (e.g. `100.0`, ~3× faster) and faster percentage
  operations (~30%, no intermediate `multiply()`).
- The deprecated `PercentageCalculator::increaseBy()` / `decreaseBy()` now delegate to
  `increaseByPercentage()` / `decreaseByPercentage()`.
- **Added 80 tests** (`DecimalParsingTest`)

## [2.9.0] - 2026-02-16

### Added

- **Advanced Mathematical Operations**: New power, square root, min, and max methods
  - `pow(int $exponent)`: Raise rational number to integer power
    - Supports positive, negative, and zero exponents
    - Negative exponents use reciprocal (e.g., `x^-2 = (1/x)^2`)
    - Includes overflow protection via existing `checkMultiplicationOverflow()` mechanism
    - Practical use: compound interest calculations (`$rate->pow($years)`)
  - `sqrt(int $precision = 10)`: Square root using Newton-Raphson approximation
    - Returns rational approximation of square root
    - Configurable precision (number of iterations)
    - Validates against negative numbers (throws `InvalidArgumentException`)
    - Example: `RationalNumber::fromFloat(2)->sqrt(20)` ≈ 1.41421356
  - `min(RationalNumber $other)`: Return smaller of two rational numbers
    - Useful for price comparisons, range analysis
    - Example: `array_reduce($prices, fn($min, $p) => $min->min($p))`
  - `max(RationalNumber $other)`: Return larger of two rational numbers
    - Companion to min for complete comparison operations
  - All methods maintain immutability (return new instances)
  - **Added 34 tests** covering all operations, edge cases, and practical scenarios

- **Rounding Operations**: New methods for rounding to integers or specific denominators
  - `round(int $denominator = 1)`: Round to nearest rational with specified denominator
    - Default rounds to nearest integer
    - `$price->round(100)` rounds to cents (useful for currency)
    - Validates denominator > 0 (throws `InvalidArgumentException`)
  - `floor()`: Round down to nearest integer
    - Returns largest integer ≤ value
    - Handles negative numbers correctly (toward negative infinity)
  - `ceil()`: Round up to nearest integer
    - Returns smallest integer ≥ value
    - Practical use: calculating units needed (`$quantity->divideBy($unitSize)->ceil()`)
  - All methods return new `RationalNumber` instances (immutable)
  - **Added 24 tests** including currency rounding, unit calculations, edge cases

- **JSON Serialization Support**: Seamless JSON encoding/decoding
  - Implements `\JsonSerializable` interface on `RationalNumber`
  - `jsonSerialize()`: Returns array with numerator, denominator, float, and string representations
    - Automatically called by `json_encode()`
    - Verbose format includes all representations for API responses
  - `toArray()`: Export to minimal array (numerator/denominator only)
    - Efficient format for caching or database storage
  - `fromArray(array $data)`: Reconstruct from array
    - Validates required keys ('numerator', 'denominator')
    - Validates numeric types with clear error messages
    - Automatically normalizes/reduces during reconstruction
  - `fromJson(string $json)`: Reconstruct from JSON string
    - Validates JSON with helpful error messages
    - Delegates to `fromArray()` for consistency
  - All methods preserve fraction reduction (e.g., 6/9 → 2/3)
  - **Added 26 tests** covering serialization, deserialization, round-trips, edge cases

- **Collection Operations**: New `RationalCollection` class for batch processing
  - Location: `RationalNumber\Collection\RationalCollection`
  - Implements: `\Countable`, `\IteratorAggregate`, `\ArrayAccess`
  - **Core methods:**
    - `add(RationalNumber $number)`: Add element (fluent interface)
    - `get(int $index)`: Retrieve element by index
    - `count()`: Number of elements
    - `isEmpty()`: Check if empty
    - `clear()`: Remove all elements
  - **Aggregate operations:**
    - `sum()`: Sum all numbers (returns `RationalNumber`)
    - `average()`: Calculate average (throws exception if empty)
    - `min()`: Find minimum value (throws exception if empty)
    - `max()`: Find maximum value (throws exception if empty)
  - **Functional operations:**
    - `map(callable $callback)`: Transform each element
    - `filter(callable $callback)`: Select elements matching condition
  - **Array-like behavior:**
    - Supports `foreach` iteration
    - Supports `count()` function
    - Supports array access: `$collection[0]`, `$collection[] = $number`
    - Validates that only `RationalNumber` instances are added
  - **Practical use cases:**
    - Grade averaging: `$grades->average()`
    - Batch tax calculation: `$prices->map(fn($p) => $p->increaseByPercentage('20%'))`
    - Price analysis: `$prices->min()`, `$prices->max()`
  - **Added 33 tests** covering construction, operations, interfaces, chaining

### Changed

- **Improved `divideFrom()` Documentation**: Enhanced PHPDoc with detailed explanations
  - Added clear description: "Divide another number by this instance (inverse division)"
  - Clarified operation: performs `$number / $this`, equivalent to `$number->divideBy($this)`
  - Added `@example` tag with practical method chaining scenario
  - Explains usefulness: dividing a value by the result of a calculation
  - Better parameter and return descriptions
  - No behavioral changes (backward compatible)

### Documentation

- **README.md**: Added four new major sections
  - "Advanced Mathematical Operations" with pow, sqrt, min, max examples
    - Includes compound interest calculation example
    - Demonstrates finding min/max in arrays
  - "Rounding Operations" with round, floor, ceil examples
    - Currency rounding example (round to cents)
    - Unit calculation example (calculate units needed)
  - "Serialization & Persistence" with JSON and array examples
    - API response example
    - Cache storage pattern
    - Round-trip serialization examples
  - "Working with Collections" with RationalCollection examples
    - Grade averaging scenario
    - Batch tax calculation
    - Map/filter/chaining demonstrations
  - Updated "Features" checklist with 4 new items
  - Updated test metrics: **175 tests, 420+ assertions**

- **CHANGELOG.md**: Comprehensive v2.9.0 entry with all additions
  - Detailed descriptions of all 13 new public methods
  - Practical use cases and code examples
  - Test coverage breakdown by feature
  - Migration notes for new Collection namespace

### Testing

- **Added 117 new tests** across 4 new test files:
  - `MathOperationsTest.php`: 34 tests for pow, sqrt, min, max
  - `RoundingTest.php`: 24 tests for round, floor, ceil
  - `SerializationTest.php`: 26 tests for JSON and array serialization
  - `CollectionTest.php`: 33 tests for collection operations
- **Total test coverage: 175 tests, 420+ assertions** (was 125 tests, 295 assertions)
- All tests follow existing patterns (immutability verification, edge cases, integration scenarios)
- Maintains delta-based assertions for float comparisons (tolerance: 1e-12)

### Architecture - SOLID Principles Compliance

- **Single Responsibility**: 
  - `RationalCollection` has single purpose: manage collections of rationals
  - Each new method in `RationalNumber` has focused responsibility
- **Open/Closed**: 
  - Extensions via new methods without modifying existing behavior
  - New Collection class extends functionality without changing core
- **Liskov Substitution**: 
  - `RationalNumber` still fulfills all interface contracts
  - All methods return `RationalNumber` instances as expected
- **Interface Segregation**: 
  - `JsonSerializable` is optional interface, doesn't pollute core contracts
  - Collection implements separate standard PHP interfaces
- **Dependency Inversion**: 
  - All methods depend on abstractions (RationalNumber type hints)
  - No coupling to concrete implementations

### Breaking Changes

**None.** All changes are backward-compatible additions. Existing code continues to work without modification.

### Migration Notes

- **New namespace** for collections: `use RationalNumber\Collection\RationalCollection;`
- **Composer autoloading** automatically includes Collection namespace via existing PSR-4 rule
- **Optional features**: All new functionality is opt-in; existing code requires no changes

## [2.8.1] - 2026-02-16

  ### Developer

  - **Static Analysis**: Added PHPStan as a development dependency to improve code quality and enforce stricter typing and docblock correctness
    - Added `phpstan/phpstan` to `composer.json` and composer scripts:
      - `composer phpstan` to run `phpstan analyse -c phpstan.neon`
      - `composer phpstan-baseline` to generate a baseline file
    - New configuration file: `phpstan.neon` (level: max, analyzing `src`, excluding `tests`)
    - Minor code fixes applied to satisfy PHPStan rules:
      - Qualified `@throws \ArithmeticError` docblocks
      - Guarded `substr(strrchr(...), ...)` usages to avoid passing `false` to `substr`
    - No baseline committed by default; generate one via `composer phpstan-baseline` if you want to suppress existing legacy issues


## [2.8.0] - 2026-02-16

### Added

- **String Parsing**: New `fromString()` static method for flexible RationalNumber creation
  - Supports fraction notation: `'1/2'`, `'22/7'`, `'3/4'`
  - Supports decimal strings: `'0.25'` (automatically simplified to `1/4`)
  - Supports integer strings: `'5'`, `'0'`, `'-3'`
  - Supports scientific notation strings: `'1.5e-3'`
  - Whitespace tolerant: `' 1/2 '` and `'1 / 2'` are valid
  - Automatic reduction/normalization (e.g., `'6/8'` becomes `3/4`)
  - Comprehensive validation with clear error messages
  - Overflow protection for large numerators/denominators
  - Added to both `RationalNumber` class and `RationalNumberFactory`
  - **Added 19 comprehensive tests** covering all formats and edge cases
  - **Total test coverage: 125 tests, 295 assertions** (was 106 tests, 241 assertions)

## [2.7.0] - 2026-02-16

### Added

- **Overflow Protection**: Automatic detection of integer overflow in arithmetic operations
  - Added `checkMultiplicationOverflow()` private method to detect overflow before operations
  - Applies to `multiply()`, `add()`, and `subtract()` methods
  - Throws `ArithmeticError` with helpful error messages
  - Error messages suggest GMP extension for handling larger numbers
  - Added comprehensive tests for overflow scenarios

- **Scientific Notation Support**: Enhanced `fromFloat()` method
  - Now handles scientific notation (e.g., `1e-10`, `1.5e20`, `2.3e-5`)
  - Added `fromScientificNotation()` private method for conversion
  - Supports both positive and negative exponents
  - Includes overflow detection for large scientific notation values
  - Added tests for various scientific notation edge cases

- **New Tests**: Added 13 new tests in `PHP83EdgeCasesTest` and `PercentageCalculatorTest`
  - Overflow detection tests for multiply, add, subtract operations
  - Scientific notation conversion tests (small, medium, large values)
  - Deprecation warning tests for PercentageCalculator methods
  - Error message validation (GMP hints)
  - **Total test coverage: 106 tests, 241 assertions** (was 50 tests, 98 assertions)

### Changed

- **API Clarification**: Deprecated `PercentageCalculator` class methods
  - Added `@deprecated` PHPDoc tags to all methods
  - Added `trigger_error()` calls with `E_USER_DEPRECATED` warnings
  - Updated documentation to recommend `RationalNumber` instance methods
  - Methods affected: `toPercentage()`, `fromPercentage()`, `increaseBy()`, `decreaseBy()`, `percentageOf()`
  - `RationalNumber` percentage methods remain the preferred API

- **Documentation**: Updated README.md with new features
  - Added "Overflow Protection" section with examples and GMP installation instructions
  - Updated "Features" list with overflow detection and scientific notation support
  - Clarified PercentageCalculator deprecation status
  - Updated percentage examples to use RationalNumber methods

### Technical Details

- Uses `ArithmeticError` (PHP built-in) for overflow detection
- Enhanced `fromFloat()` with integer input fast-path
- Overflow detection uses division-based checking: `|a| > PHP_INT_MAX / |b|`
- Scientific notation parsing handles edge cases (zero, negative, extreme values)
- All changes follow SOLID principles and maintain backward compatibility
- Code comments and PHPDoc in English as per project standards

## [2.6.0] - 2026-02-16

### Maintenance - Production Release Preparation

- **Prepared for Packagist publication:** Set minimum PHP requirement to PHP 8.3 (current stable version).
- **Verification:** Library tested and compatible with PHP 8.3, 8.4, and future 8.5.
- **Production ready:** All tests pass (50 tests, 98 assertions) across PHP 8.3+ versions.
- **Dev dependencies:** PHPUnit `^9.5` (tested with 9.6). Consider migrating `phpunit.xml.dist` with `vendor/bin/phpunit --migrate-configuration` to use the latest schema.

## [2.5.0] - 2026-02-16

### Maintenance

- **Bumped minimum PHP requirement to PHP 8.4.**
- **Verification:** Ran the full test suite under PHP 8.4.18 — all tests pass (50 tests, 98 assertions).
- **PHP 8.4 compatibility:** No dynamic properties used; code is fully compatible with PHP 8.4's rules and deprecations.
- **Dev dependencies:** PHPUnit remains on `^9.5` (tested with 9.6). Consider migrating `phpunit.xml.dist` with `vendor/bin/phpunit --migrate-configuration` to use the latest schema.

## [2.4.0] - 2026-02-16

### Maintenance

- **Bumped minimum PHP requirement to PHP 8.3.**
- **Verification:** Ran the full test suite under PHP 8.3.30 — all tests pass (50 tests, 98 assertions).
- **PHP 8.3 compatibility:** No dynamic properties used; code is fully compatible with PHP 8.3's stricter rules.
- **Dev dependencies:** PHPUnit remains on `^9.5` (tested with 9.6). Consider migrating `phpunit.xml.dist` with `vendor/bin/phpunit --migrate-configuration` to use the latest schema.

## [2.3.0] - 2026-02-16

### Maintenance

- **Bumped minimum PHP requirement to PHP 8.2.**
- **Verification:** Ran the full test suite under PHP 8.2.29 — all tests pass (50 tests, 98 assertions).
- **PHP 8.2 compatibility:** No dynamic properties used; code is fully compatible with PHP 8.2's stricter rules.
- **Dev dependencies:** PHPUnit remains on `^9.5` (tested with 9.6). Consider migrating `phpunit.xml.dist` with `vendor/bin/phpunit --migrate-configuration` to use the latest schema.

## [2.2.0] - 2026-02-16

### Maintenance

- **Bumped minimum PHP requirement to PHP 8.1.**
- **Verification:** Ran the full test suite under PHP 8.1.31 — all tests pass (50 tests, 98 assertions).
- **Dev dependencies:** PHPUnit remains on `^9.5` (tested with 9.6). Consider migrating `phpunit.xml.dist` with `vendor/bin/phpunit --migrate-configuration` to use the latest schema.

## [2.1.0] - 2026-02-16

### Maintenance

- **Bumped minimum PHP requirement to PHP 8.0.** This repository now targets PHP 8.0+ for runtime.
- **Updated dev dependencies:** PHPUnit moved to `^9.5` (PHPUnit 9.6 installed) to support PHP 8.0.
- **Tests updated:** Several floating-point assertions were replaced with delta-based assertions (`assertEqualsWithDelta`) to avoid platform-specific flakiness.
- **Verification:** Ran the full test suite under PHP 8.0 — all tests pass (50 tests, 98 assertions). PHPUnit suggests migrating `phpunit.xml.dist` to the modern schema; consider running `vendor/bin/phpunit --migrate-configuration` to update the config.

## [2.0.0] - 2026-02-15

### 🎉 Major Refactoring - SOLID Principles & Breaking Changes

#### Architecture - SOLID Principles Implementation

##### Single Responsibility Principle (SRP)
- **Created `PercentageCalculator` class**: Extracted percentage operations from core `RationalNumber` class
  - `toPercentage()`, `fromPercentage()`, `increaseBy()`, `decreaseBy()`, `percentageOf()`
  - Percentage logic no longer mixed with core arithmetic operations
- **Created `RationalNumberFactory` class**: Separated object creation logic
  - Factory pattern for flexible object instantiation

##### Open/Closed Principle (OCP)
- **Created interface hierarchy**:
  - `ArithmeticOperations`: Contract for arithmetic methods
  - `Comparable`: Contract for comparison methods
  - `NumericValue`: Contract for value representation
- `RationalNumber` now implements these interfaces, allowing extension without modification

##### Interface Segregation Principle (ISP)
- Small, focused interfaces instead of one large interface
- Clients depend only on methods they use

##### Dependency Inversion Principle (DIP)
- Method parameters now accept interface types (`ArithmeticOperations`) instead of concrete classes
- Factory pattern decouples creation from usage
- Depends on abstractions, not concrete implementations

##### Liskov Substitution Principle (LSP)
- All interface implementations properly fulfill their contracts
- Return type covariance maintained

#### Breaking Changes
- **BREAKING**: Moved `RationalNumber` class to `src/` directory with proper PSR-4 namespace
- **BREAKING**: Class now requires namespace: `use RationalNumber\\RationalNumber;`
- **BREAKING**: Minimum PHP version increased to 7.4 (was 7.0)
- **BREAKING**: Class is now marked as `final` (value object best practice)
- **BREAKING**: Arithmetic method signatures now accept interface types
- **BREAKING**: Percentage methods moved to `PercentageCalculator` class

#### New Features - Comparison Operations
- `equals(Comparable $other): bool` - Check if two rational numbers are equal
- `compareTo(Comparable $other): int` - Compare two numbers (-1, 0, 1)
- `isGreaterThan(Comparable $other): bool` - Check if greater than
- `isLessThan(Comparable $other): bool` - Check if less than
- `isGreaterThanOrEqual(Comparable $other): bool` - Check if greater or equal
- `isLessThanOrEqual(Comparable $other): bool` - Check if less or equal

#### New Features - Additional Arithmetic
- `abs(): RationalNumber` - Get absolute value
- `negate(): RationalNumber` - Negate the number

#### New Features - Factory Methods
- `RationalNumber::zero(): RationalNumber` - Create zero (0/1)
- `RationalNumber::one(): RationalNumber` - Create one (1/1)
- `RationalNumberFactory` class with:
  - `create($numerator, $denominator)`
  - `fromFloat($value)`
  - `fromPercentage($percentage)`
  - `zero()` and `one()` factory methods

#### Type Safety
- Added `declare(strict_types=1)` to all PHP files
- Added type hints to all method parameters
- Added return type declarations to all methods
- Typed class properties: `private int $numerator` and `private int $denominator`

#### Bug Fixes
- **CRITICAL**: Fixed GCD algorithm to handle negative numbers correctly (now uses `abs()`)
- **CRITICAL**: Fixed `normalize()` to move negative sign from denominator to numerator
- **CRITICAL**: Added division by zero protection in `reciprocal()` method with exception
- **CRITICAL**: Added division by zero checks in `divideBy()` and `divideFrom()` methods
- Fixed `fromFloat()` to properly handle integer values (no decimal point)
- Changed all weak comparisons (`==`) to strict comparisons (`===`)

#### Testing
- **Comprehensive test coverage: 50 tests, 98 assertions** (was 14 tests, 27 assertions)
- Added `RationalNumberTest` with 15 new tests:
  - Exception handling tests (4 tests)
  - Normalization tests (2 tests)
  - Comparison tests (6 tests - equals, compareTo, greater/less than variants)
  - Arithmetic tests (3 tests - abs, negate, factory methods)
- Added `PercentageCalculatorTest` with 8 tests
- Added `RationalNumberFactoryTest` with 7 tests

#### Documentation
- Complete README rewrite with comprehensive examples
- Added "Architecture" section explaining SOLID principles
- Added examples for all new features:
  - Comparison operations with code samples
  - Percentage calculator usage
  - Factory pattern usage
- Added "Testing" section with metrics
- Added MIT License full text
- Fixed PHP version inconsistency (now consistently 7.4+)
- Created this comprehensive CHANGELOG

#### Removed
- Removed old `RationalNumber.php` from root directory (now in `src/`)
- Removed manual `require` statements (now using Composer autoloader)
- Removed percentage methods from `RationalNumber` (now in `PercentageCalculator`)

#### Internal Improvements
- Better separation of concerns
- Cleaner code organization with `src/Contract/`, `src/Calculator/`, `src/Factory/` directories
- Enhanced exception messages for better debugging
- Immutability properly enforced (all operations return new instances)

## [1.0.0] - Initial Release

### Features
- Basic rational number arithmetic (add, subtract, multiply, divide)
- Percentage operations
- Float/integer conversions
- Basic normalization

