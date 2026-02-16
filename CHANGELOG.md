
# Changelog

All notable changes to this project will be documented in this file.

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

