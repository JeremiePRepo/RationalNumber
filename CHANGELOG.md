# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2026-02-15

### 🎉 Major Refactoring - Breaking Changes

#### Architecture
- **BREAKING**: Moved `RationalNumber` class to `src/` directory with proper PSR-4 namespace
- **BREAKING**: Class now requires namespace: `use RationalNumber\RationalNumber;`
- **BREAKING**: Minimum PHP version increased to 7.4 (was 7.0)
- Class is now marked as `final` (value object best practice)

#### Type Safety
- Added `declare(strict_types=1)` for strict type checking
- Added type hints to all method parameters
- Added return type declarations to all methods
- Typed class properties: `private int $numerator` and `private int $denominator`

#### Bug Fixes
- **CRITICAL**: Fixed GCD algorithm to handle negative numbers correctly (now uses `abs()`)
- **CRITICAL**: Fixed `normalize()` to move negative sign from denominator to numerator
- **CRITICAL**: Added division by zero protection in `reciprocal()` method
- **CRITICAL**: Added division by zero checks in `divideBy()` and `divideFrom()` methods
- Fixed `fromFloat()` to properly handle integer values (no decimal point)
- Changed all weak comparisons (`==`) to strict comparisons (`===`)

#### New Features
- Comprehensive exception handling with meaningful error messages
- Automatic normalization of negative denominators
- Enhanced test coverage: 24 tests, 46 assertions (was 14 tests, 27 assertions)
- New tests for edge cases and exception handling

#### Documentation
- Complete README rewrite with namespace examples
- Added exception handling documentation
- Added installation instructions via Composer
- Added "Running Tests" section
- Fixed PHP version inconsistency (now consistently 7.4+)
- Added this CHANGELOG

#### Removed
- Removed old `RationalNumber.php` from root directory (now in `src/`)
- Removed manual `require` statements (now using Composer autoloader)

### Tests Added
- Test for zero denominator exception
- Test for reciprocal of zero exception
- Test for division by zero exceptions
- Test for negative denominator normalization
- Test for `isZero()` method
- Test for `isInteger()` method
- Test for `reduce()` method
- Test for `__toString()` magic method
- Test for normalizing double-negative numbers

## [1.0.0] - Initial Release

### Features
- Basic rational number arithmetic (add, subtract, multiply, divide)
- Percentage operations
- Float/integer conversions
- Basic normalization
