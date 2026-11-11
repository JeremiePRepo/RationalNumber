<?php

declare(strict_types=1);

namespace RationalNumber;

use RationalNumber\Contract\ArithmeticOperations;
use RationalNumber\Contract\Comparable;
use RationalNumber\Contract\NumericValue;
use RationalNumber\Exception\DivisionByZeroException;
use RationalNumber\Exception\IntegerOverflowException;
use RationalNumber\Exception\InvalidArgumentException;
use RationalNumber\Internal\DecimalParser;

/**
 * Represents an immutable rational number (fraction) with precise arithmetic operations.
 *
 * Implements JsonSerializable for seamless JSON encoding/decoding support.
 */
final readonly class RationalNumber implements ArithmeticOperations, Comparable, NumericValue, \JsonSerializable
{
    private int $numerator;
    private int $denominator;

    /**
     * Constructor for the RationalNumber class.
     * @param int $numerator The numerator of the rational number.
     * @param int $denominator The denominator of the rational number (default is 1).
     * @throws InvalidArgumentException if the denominator is set to zero.
     * @throws IntegerOverflowException if the numerator or the denominator is PHP_INT_MIN.
     */
    public function __construct(int $numerator, int $denominator = 1)
    {
        if ($denominator === 0) {
            throw new InvalidArgumentException("Denominator cannot be zero.");
        }

        // -PHP_INT_MIN does not fit in an int, so normalization (sign, GCD) could not be exact.
        if ($numerator === PHP_INT_MIN || $denominator === PHP_INT_MIN) {
            throw IntegerOverflowException::create(
                "Integer overflow: PHP_INT_MIN cannot be used as a numerator or a denominator "
                . "because its opposite does not fit in an integer."
            );
        }

        [$this->numerator, $this->denominator] = self::normalize($numerator, $denominator);
    }

    /**
     * Create a RationalNumber object from a float or int value.
     *
     * The float is converted from its shortest decimal representation (the one PHP uses with
     * serialize_precision = -1, e.g. 0.29 => 29/100), independently of the `precision` and
     * `serialize_precision` ini settings. Scientific notation (e.g. 1e-10, 1.5e20) is supported.
     *
     * If that exact decimal value does not fit in 64-bit integers (e.g. 1.2345678901234568e-5),
     * it is rounded to the largest number of significant digits that fits.
     *
     * @param float|int $value The scalar value to create a RationalNumber object from.
     * @return RationalNumber The RationalNumber object created from the scalar value.
     * @throws InvalidArgumentException if the value is INF or NAN.
     * @throws IntegerOverflowException if the value is too large, or too small to be represented without becoming zero.
     */
    public static function fromFloat($value): RationalNumber
    {
        // Handle integer input directly
        if (is_int($value)) {
            return new self($value, 1);
        }

        if (!is_finite($value)) {
            throw new InvalidArgumentException("Cannot create RationalNumber from INF or NAN.");
        }

        return new self(...DecimalParser::fromFloat($value));
    }

    /**
     * Create a RationalNumber object from a string representation.
     * Supports fraction notation (e.g., '1/2', '3/4'), decimal strings (e.g., '0.25'),
     * integer strings (e.g., '5'), and scientific notation (e.g., '1.5e-3').
     * Whitespace around the input and around the fraction separator is tolerated.
     *
     * @param string $input The string to parse into a RationalNumber.
     * @return RationalNumber The RationalNumber object created from the string.
     * @throws InvalidArgumentException if the string format is invalid or empty.
     * @throws IntegerOverflowException if the conversion would cause integer overflow.
     */
    public static function fromString(string $input): RationalNumber
    {
        // Trim whitespace from the entire input
        $input = trim($input);

        // Check for empty string
        if ($input === '') {
            throw new InvalidArgumentException("Cannot create RationalNumber from empty string.");
        }

        // Try to match fraction notation: "numerator/denominator" with optional whitespace
        if (preg_match('/^([+-]?\d+)\s*\/\s*([+-]?\d+)$/', $input, $matches)) {
            $numeratorStr = $matches[1];
            $denominatorStr = $matches[2];

            // Validate for integer overflow before converting to int (exact check on the digits)
            if (!DecimalParser::fitsInInt(ltrim($numeratorStr, '+-0'))) {
                throw IntegerOverflowException::create(
                    "Integer overflow in fraction string '{$input}'. Numerator exceeds PHP_INT_MAX."
                );
            }

            if (!DecimalParser::fitsInInt(ltrim($denominatorStr, '+-0'))) {
                throw IntegerOverflowException::create(
                    "Integer overflow in fraction string '{$input}'. Denominator exceeds PHP_INT_MAX."
                );
            }

            $numerator = (int)$numeratorStr;
            $denominator = (int)$denominatorStr;

            // Constructor will handle division by zero validation
            return new self($numerator, $denominator);
        }

        // Try to parse as an exact decimal (decimal, integer, or scientific notation),
        // without going through a float.
        $fraction = DecimalParser::parse($input);
        if ($fraction !== null) {
            return new self(...$fraction);
        }

        // Invalid format
        throw new InvalidArgumentException(
            "Invalid string format: '{$input}'. Expected formats: '3/4', '0.25', '5', or scientific notation."
        );
    }

    /**
     * Get the floating-point representation of the rational number.
     * @return float The rational number as a float.
     */
    public function getFloat(): float
    {
        return $this->numerator / $this->denominator;
    }

    /**
     * Multiply the current rational number by another RationalNumber object.
     * @param ArithmeticOperations $number The RationalNumber object to multiply with.
     * @return RationalNumber The result of the multiplication as a new RationalNumber object.
     * @throws IntegerOverflowException if the operation would cause integer overflow.
     */
    public function multiply(ArithmeticOperations $number): RationalNumber
    {
        if (!$number instanceof RationalNumber) {
            throw new InvalidArgumentException("Must be a RationalNumber instance.");
        }

        // Check for potential overflow before multiplication
        $this->checkMultiplicationOverflow($this->numerator, $number->getNumerator(), 'numerator multiplication');
        $this->checkMultiplicationOverflow($this->denominator, $number->getDenominator(), 'denominator multiplication');

        $newNumerator = $this->numerator * $number->getNumerator();
        $newDenominator = $this->denominator * $number->getDenominator();
        return new RationalNumber($newNumerator, $newDenominator);
    }

    /**
     * Add the current rational number to another RationalNumber object.
     * @param ArithmeticOperations $number The RationalNumber object to add.
     * @return RationalNumber The result of the addition as a new RationalNumber object.
     * @throws IntegerOverflowException if the operation would cause integer overflow.
     */
    public function add(ArithmeticOperations $number): RationalNumber
    {
        if (!$number instanceof RationalNumber) {
            throw new InvalidArgumentException("Must be a RationalNumber instance.");
        }

        $otherNumerator = $number->getNumerator();
        $otherDenominator = $number->getDenominator();

        // Check for potential overflow in cross-multiplication
        $this->checkMultiplicationOverflow($this->numerator, $otherDenominator, 'addition cross-multiplication');
        $this->checkMultiplicationOverflow($otherNumerator, $this->denominator, 'addition cross-multiplication');
        $this->checkMultiplicationOverflow($this->denominator, $otherDenominator, 'denominator multiplication');

        $left = $this->numerator * $otherDenominator;
        $right = $otherNumerator * $this->denominator;
        if ($right > 0 ? $left > PHP_INT_MAX - $right : $left < PHP_INT_MIN - $right) {
            throw IntegerOverflowException::create("Operation would cause integer overflow during addition.");
        }

        $newNumerator = $left + $right;
        $newDenominator = $this->denominator * $otherDenominator;
        return new RationalNumber($newNumerator, $newDenominator);
    }

    /**
     * Subtract another RationalNumber object from the current rational number.
     * @param ArithmeticOperations $number The RationalNumber object to subtract.
     * @return RationalNumber The result of the subtraction as a new RationalNumber object.
     * @throws IntegerOverflowException if the operation would cause integer overflow.
     */
    public function subtract(ArithmeticOperations $number): RationalNumber
    {
        if (!$number instanceof RationalNumber) {
            throw new InvalidArgumentException("Must be a RationalNumber instance.");
        }

        $otherNumerator = $number->getNumerator();
        $otherDenominator = $number->getDenominator();

        // Check for potential overflow in cross-multiplication
        $this->checkMultiplicationOverflow($this->numerator, $otherDenominator, 'subtraction cross-multiplication');
        $this->checkMultiplicationOverflow($otherNumerator, $this->denominator, 'subtraction cross-multiplication');
        $this->checkMultiplicationOverflow($this->denominator, $otherDenominator, 'denominator multiplication');

        $left = $this->numerator * $otherDenominator;
        $right = $otherNumerator * $this->denominator;
        if ($right > 0 ? $left < PHP_INT_MIN + $right : $left > PHP_INT_MAX + $right) {
            throw IntegerOverflowException::create("Operation would cause integer overflow during subtraction.");
        }

        $newNumerator = $left - $right;
        $newDenominator = $this->denominator * $otherDenominator;
        return new RationalNumber($newNumerator, $newDenominator);
    }

    /**
     * Divide the current rational number by another RationalNumber object.
     * @param ArithmeticOperations $number The RationalNumber object to divide by.
     * @return RationalNumber The result of the division as a new RationalNumber object.
     * @throws DivisionByZeroException if dividing by zero.
     */
    public function divideBy(ArithmeticOperations $number): RationalNumber
    {
        if (!$number instanceof RationalNumber) {
            throw new InvalidArgumentException("Must be a RationalNumber instance.");
        }
        if ($number->isZero()) {
            throw new DivisionByZeroException("Cannot divide by zero.");
        }
        // To divide by a number, we multiply by its reciprocal.
        $reciprocal = $number->reciprocal();
        return $this->multiply($reciprocal);
    }

    /**
     * Divide another RationalNumber object by the current rational number (inverse division).
     *
     * This method performs the operation: $number / $this
     * Equivalent to: $number->divideBy($this)
     *
     * Useful for method chaining where you want to divide a value by the result of a calculation.
     *
     * Example:
     * <code>
     * // Divide 100 by the result of a calculation
     * $divisor = $a->add($b)->multiply($c);
     * $result = $divisor->divideFrom(RationalNumber::fromFloat(100));
     * // Instead of: RationalNumber::fromFloat(100)->divideBy($divisor)
     * </code>
     *
     * @deprecated since 2.11, removed in 3.0: use $number->divideBy($this). See UPGRADE-3.0.md.
     * @param ArithmeticOperations $number The dividend (the number to be divided).
     * @return RationalNumber The result of $number / $this as a new RationalNumber object.
     * @throws DivisionByZeroException if the current instance is zero (division by zero).
     */
    public function divideFrom(ArithmeticOperations $number): RationalNumber
    {
        trigger_error(
            'RationalNumber::divideFrom() is deprecated since 2.11 and will be removed in 3.0. '
            . 'Use $number->divideBy($this) instead. See UPGRADE-3.0.md.',
            E_USER_DEPRECATED
        );

        if (!$number instanceof RationalNumber) {
            throw new InvalidArgumentException("Must be a RationalNumber instance.");
        }
        if ($this->isZero()) {
            throw new DivisionByZeroException("Cannot divide by zero.");
        }
        // To divide a number by this rational number, we multiply it by this number's reciprocal.
        $reciprocal = $this->reciprocal();
        return $number->multiply($reciprocal);
    }

    /**
     * Convert the rational number to a percentage with a specified number of decimal places.
     * @param int $decimalPlaces The number of decimal places for the percentage (default is 2).
     * @return string The rational number as a percentage string.
     */
    public function toPercentage(int $decimalPlaces = 2): string
    {
        $percentage = $this->getFloat() * 100;
        return number_format($percentage, $decimalPlaces) . "%";
    }

    /**
     * Create a RationalNumber object from a percentage value.
     *
     * The value is parsed exactly (no floating point): "57%" => 57/100, "12.5 %" => 1/8.
     * The percentage sign is optional.
     *
     * @param string $percentage The percentage value as a string (e.g., "50%").
     * @return RationalNumber The RationalNumber object created from the percentage value.
     * @throws InvalidArgumentException if the value is not a valid number.
     * @throws IntegerOverflowException if the conversion would cause integer overflow.
     */
    public static function fromPercentage(string $percentage): RationalNumber
    {
        // Remove the percentage sign if present (fromString() trims the rest).
        $value = self::fromString(rtrim(trim($percentage), '%'));

        // Divide by 100 directly rather than through multiply(): one allocation and one GCD fewer.
        $value->checkMultiplicationOverflow($value->denominator, 100, 'denominator multiplication');
        return new self($value->numerator, $value->denominator * 100);
    }

    /**
     * Create a RationalNumber representing zero.
     * @return RationalNumber The zero rational number (0/1).
     */
    public static function zero(): RationalNumber
    {
        return new RationalNumber(0, 1);
    }

    /**
     * Create a RationalNumber representing one.
     * @return RationalNumber The one rational number (1/1).
     */
    public static function one(): RationalNumber
    {
        return new RationalNumber(1, 1);
    }

    /**
     * Increase the current rational number by a specified percentage.
     *
     * The rate is either a percentage string ("20%") or a RationalNumber holding the rate as a
     * fraction (1/5 for 20%, as returned by fromPercentage()). Pass a RationalNumber to parse a
     * rate once and reuse it:
     * <code>
     * $vat = RationalNumber::fromPercentage('20%');
     * $priceWithVat = $price->increaseByPercentage($vat);
     * </code>
     *
     * @param RationalNumber|string $percentage The rate to increase by (e.g., "50%" or 1/2).
     * @return RationalNumber The result of the increase as a new RationalNumber object.
     */
    public function increaseByPercentage(RationalNumber|string $percentage): RationalNumber
    {
        return $this->add($this->multiply(self::toRate($percentage)));
    }

    /**
     * Decrease the current rational number by a specified percentage.
     *
     * The rate is either a percentage string ("20%") or a RationalNumber holding the rate as a
     * fraction (1/5 for 20%, as returned by fromPercentage()).
     *
     * @param RationalNumber|string $percentage The rate to decrease by (e.g., "50%" or 1/2).
     * @return RationalNumber The result of the decrease as a new RationalNumber object.
     */
    public function decreaseByPercentage(RationalNumber|string $percentage): RationalNumber
    {
        return $this->subtract($this->multiply(self::toRate($percentage)));
    }

    /**
     * Turn a percentage argument into the rate as a fraction ("20%" => 1/5).
     */
    private static function toRate(RationalNumber|string $percentage): RationalNumber
    {
        return $percentage instanceof self ? $percentage : self::fromPercentage($percentage);
    }

    /**
     * Check if the rational number is equal to zero.
     * @return bool True if the rational number is zero, false otherwise.
     */
    public function isZero(): bool
    {
        return $this->numerator === 0;
    }

    /**
     * Check if the rational number is an integer.
     * @return bool True if the rational number is an integer, false otherwise.
     */
    public function isInteger(): bool
    {
        return $this->denominator === 1;
    }

    /**
     * Check if this rational number equals another.
     * @param Comparable $other The rational number to compare with.
     * @return bool True if equal, false otherwise.
     */
    public function equals(Comparable $other): bool
    {
        if (!$other instanceof RationalNumber) {
            return false;
        }

        // Compare by cross-multiplication to avoid division
        return $this->numerator * $other->getDenominator() === $other->getNumerator() * $this->denominator;
    }

    /**
     * Compare this rational number to another.
     * @param Comparable $other The rational number to compare with.
     * @return int Returns -1 if this < other, 0 if equal, 1 if this > other.
     */
    public function compareTo(Comparable $other): int
    {
        if (!$other instanceof RationalNumber) {
            throw new InvalidArgumentException("Can only compare with another RationalNumber.");
        }

        // Cross multiply to compare: a/b vs c/d => a*d vs c*b
        $left = $this->numerator * $other->getDenominator();
        $right = $other->getNumerator() * $this->denominator;

        if ($left < $right) {
            return -1;
        } elseif ($left > $right) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Check if this rational number is greater than another.
     * @param Comparable $other The rational number to compare with.
     * @return bool True if this > other.
     */
    public function isGreaterThan(Comparable $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    /**
     * Check if this rational number is less than another.
     * @param Comparable $other The rational number to compare with.
     * @return bool True if this < other.
     */
    public function isLessThan(Comparable $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    /**
     * Check if this rational number is greater than or equal to another.
     * @param Comparable $other The rational number to compare with.
     * @return bool True if this >= other.
     */
    public function isGreaterThanOrEqual(Comparable $other): bool
    {
        return $this->compareTo($other) >= 0;
    }

    /**
     * Check if this rational number is less than or equal to another.
     * @param Comparable $other The rational number to compare with.
     * @return bool True if this <= other.
     */
    public function isLessThanOrEqual(Comparable $other): bool
    {
        return $this->compareTo($other) <= 0;
    }

    /**
     * Get the absolute value of this rational number.
     * @return RationalNumber The absolute value.
     */
    public function abs(): RationalNumber
    {
        return new RationalNumber(abs($this->numerator), $this->denominator);
    }

    /**
     * Negate this rational number.
     * @return RationalNumber The negated value.
     */
    public function negate(): RationalNumber
    {
        return new RationalNumber(-$this->numerator, $this->denominator);
    }

    /**
     * Get the reciprocal of the rational number.
     * @return RationalNumber The reciprocal of the rational number as a new RationalNumber object.
     * @throws DivisionByZeroException if the numerator is zero.
     */
    public function reciprocal(): RationalNumber
    {
        if ($this->numerator === 0) {
            throw new DivisionByZeroException("Cannot get reciprocal of zero.");
        }
        return new RationalNumber($this->denominator, $this->numerator);
    }

    /**
     * Raise the rational number to an integer power.
     *
     * Supports positive, negative, and zero exponents.
     * For negative exponents, calculates the power of the reciprocal.
     *
     * @param int $exponent The integer exponent to raise to.
     * @return RationalNumber The result of the exponentiation as a new RationalNumber object.
     * @throws IntegerOverflowException if the operation would cause integer overflow.
     */
    public function pow(int $exponent): RationalNumber
    {
        // Any number to the power of 0 is 1
        if ($exponent === 0) {
            return self::one();
        }

        // Negative exponent: (a/b)^(-n) = (b/a)^n
        if ($exponent < 0) {
            return $this->reciprocal()->pow(-$exponent);
        }

        // Positive exponent: multiply by itself (exponent - 1) times
        $result = $this;
        for ($i = 1; $i < $exponent; $i++) {
            $result = $result->multiply($this);
        }

        return $result;
    }

    /**
     * Calculate the square root of the rational number using Newton-Raphson method.
     *
     * Returns a rational approximation of the square root.
     * The precision parameter controls the number of iterations (higher = more accurate).
     * Note: Precision is limited to prevent overflow with very large fractions.
     *
     * @param int $precision Number of iterations for the approximation (default: 10, max: 10).
     * @return RationalNumber A rational approximation of the square root.
     * @throws InvalidArgumentException if attempting to take square root of a negative number.
     */
    public function sqrt(int $precision = 10): RationalNumber
    {
        if ($this->numerator < 0) {
            throw new InvalidArgumentException("Cannot calculate square root of a negative number.");
        }

        if ($this->isZero()) {
            return self::zero();
        }

        // Limit precision to avoid overflow (max 10 iterations)
        $precision = min($precision, 10);

        // Use float-based Newton-Raphson for stability
        $floatSqrt = sqrt($this->getFloat());
        $x = $floatSqrt;
        $thisFloat = $this->getFloat();

        // Perform Newton-Raphson iterations in float space
        for ($i = 0; $i < $precision; $i++) {
            $x = ($x + $thisFloat / $x) / 2;
        }

        // Convert final result to RationalNumber
        return RationalNumber::fromFloat($x);
    }

    /**
     * Return the minimum of this rational number and another.
     *
     * @param RationalNumber $other The rational number to compare with.
     * @return RationalNumber The smaller of the two rational numbers.
     */
    public function min(RationalNumber $other): RationalNumber
    {
        return $this->isLessThan($other) ? $this : $other;
    }

    /**
     * Return the maximum of this rational number and another.
     *
     * @param RationalNumber $other The rational number to compare with.
     * @return RationalNumber The larger of the two rational numbers.
     */
    public function max(RationalNumber $other): RationalNumber
    {
        return $this->isGreaterThan($other) ? $this : $other;
    }

    /**
     * Round the rational number to a specified denominator.
     *
     * Rounds to the nearest rational number with the given denominator.
     * Useful for rounding prices to cents (denominator = 100).
     *
     * @param int $denominator The target denominator (default: 1 for integer rounding).
     * @return RationalNumber The rounded rational number.
     * @throws InvalidArgumentException if denominator is less than or equal to zero.
     */
    public function round(int $denominator = 1): RationalNumber
    {
        if ($denominator <= 0) {
            throw new InvalidArgumentException("Denominator must be greater than zero.");
        }

        $numerator = (int) round(
            $this->numerator * $denominator / $this->denominator
        );

        return new self($numerator, $denominator);
    }

    /**
     * Round down to the nearest integer (floor function).
     *
     * @return RationalNumber The largest integer less than or equal to this number.
     */
    public function floor(): RationalNumber
    {
        $integerPart = (int) floor($this->getFloat());
        return new self($integerPart, 1);
    }

    /**
     * Round up to the nearest integer (ceiling function).
     *
     * @return RationalNumber The smallest integer greater than or equal to this number.
     */
    public function ceil(): RationalNumber
    {
        $integerPart = (int) ceil($this->getFloat());
        return new self($integerPart, 1);
    }

    /**
     * Reduce the rational number to its simplest form.
     *
     * @deprecated since 2.11, removed in 3.0: every RationalNumber is already in its simplest form,
     *             remove the call. See UPGRADE-3.0.md.
     * @return RationalNumber The same (already reduced) rational number.
     */
    public function reduce(): RationalNumber
    {
        trigger_error(
            'RationalNumber::reduce() is deprecated since 2.11 and will be removed in 3.0. '
            . 'Numbers are always in their simplest form: remove the call. See UPGRADE-3.0.md.',
            E_USER_DEPRECATED
        );

        return $this;
    }

    /**
     * Get the numerator of the rational number.
     * @return int The numerator of the rational number.
     */
    public function getNumerator(): int
    {
        return $this->numerator;
    }

    /**
     * Get the denominator of the rational number.
     * @return int The denominator of the rational number.
     */
    public function getDenominator(): int
    {
        return $this->denominator;
    }

    /**
     * Convert the rational number to a string representation.
     * @return string The rational number as a string in the format "numerator/denominator".
     */
    public function toString(): string
    {
        return $this->numerator . "/" . $this->denominator;
    }

    /**
     * Convert the rational number to a string representation.
     * @return string The rational number as a string in the format "numerator/denominator".
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Serialize the rational number for JSON encoding.
     *
     * Returns an array containing the numerator, denominator, and convenient representations.
     * Automatically called by json_encode().
     *
     * @return array{numerator: int, denominator: int, float: float, string: string} The serialized representation.
     */
    public function jsonSerialize(): array
    {
        return [
            'numerator' => $this->numerator,
            'denominator' => $this->denominator,
            'float' => $this->getFloat(),
            'string' => $this->toString()
        ];
    }

    /**
     * Export the rational number to an array (minimal format).
     *
     * Returns only the essential numerator and denominator for storage.
     * Useful for caching or database storage.
     *
     * @return array{numerator: int, denominator: int} Array with numerator and denominator.
     */
    public function toArray(): array
    {
        return [
            'numerator' => $this->numerator,
            'denominator' => $this->denominator
        ];
    }

    /**
     * Create a RationalNumber from an array representation.
     *
     * Expects an array with 'numerator' and 'denominator' keys.
     *
     * @param array<mixed, mixed> $data Array containing 'numerator' and 'denominator' keys.
     * @return RationalNumber The reconstructed RationalNumber object.
     * @throws InvalidArgumentException if required keys are missing or invalid.
     */
    public static function fromArray(array $data): RationalNumber
    {
        if (!isset($data['numerator']) || !isset($data['denominator'])) {
            throw new InvalidArgumentException(
                "Array must contain 'numerator' and 'denominator' keys."
            );
        }

        if (!is_int($data['numerator']) && !is_numeric($data['numerator'])) {
            throw new InvalidArgumentException("Numerator must be an integer or numeric value.");
        }

        if (!is_int($data['denominator']) && !is_numeric($data['denominator'])) {
            throw new InvalidArgumentException("Denominator must be an integer or numeric value.");
        }

        return new self((int)$data['numerator'], (int)$data['denominator']);
    }

    /**
     * Create a RationalNumber from a JSON string.
     *
     * Parses JSON and extracts numerator and denominator.
     *
     * @param string $json JSON string containing rational number data.
     * @return RationalNumber The reconstructed RationalNumber object.
     * @throws InvalidArgumentException if JSON is invalid or required keys are missing.
     */
    public static function fromJson(string $json): RationalNumber
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                "Invalid JSON: " . json_last_error_msg()
            );
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException("JSON must decode to an array.");
        }

        return self::fromArray($data);
    }

    /**
     * Calculate the greatest common divisor (GCD) of two integers using the Euclidean algorithm.
     * @param int $a The first integer.
     * @param int $b The second integer.
     * @return int The GCD of the two integers.
     */
    private static function gcd(int $a, int $b): int
    {
        $a = abs($a);
        $b = abs($b);
        return ($b === 0) ? $a : self::gcd($b, $a % $b);
    }

    /**
     * Check if a multiplication operation would cause integer overflow.
     *
     * @param int $a The first operand.
     * @param int $b The second operand.
     * @param string $operation A description of the operation for error message context.
     * @return void
     * @throws IntegerOverflowException if the multiplication would overflow.
     */
    private function checkMultiplicationOverflow(int $a, int $b, string $operation): void
    {
        // Zero is always safe
        if ($a === 0 || $b === 0) {
            return;
        }

        // |a * b| > PHP_INT_MAX  <=>  |a| > floor(PHP_INT_MAX / |b|). intdiv() keeps the bound exact:
        // a float division rounds it up (PHP_INT_MAX / 2 gives 2^62) and lets 2^62 * 2 through.
        if (abs($a) > intdiv(PHP_INT_MAX, abs($b))) {
            throw IntegerOverflowException::create(
                sprintf("Operation would cause integer overflow during %s.", $operation)
            );
        }
    }

    /**
     * Normalize a fraction to its simplest form: reduce it by the greatest common divisor (GCD)
     * and make the denominator positive.
     *
     * @return array{int, int} [numerator, denominator]
     */
    private static function normalize(int $numerator, int $denominator): array
    {
        if ($denominator < 0) {
            $numerator = -$numerator;
            $denominator = -$denominator;
        }

        $gcd = self::gcd($numerator, $denominator);
        if ($gcd !== 1) {
            $numerator = intdiv($numerator, $gcd);
            $denominator = intdiv($denominator, $gcd);
        }

        return [$numerator, $denominator];
    }
}
