<?php

declare(strict_types=1);

namespace RationalNumber;

use RationalNumber\Exception\DivisionByZeroException;
use RationalNumber\Exception\IntegerOverflowException;
use RationalNumber\Exception\InvalidArgumentException;
use RationalNumber\Internal\DecimalParser;

/**
 * Represents an immutable rational number (fraction) with precise arithmetic operations.
 *
 * Implements JsonSerializable for seamless JSON encoding/decoding support.
 */
final readonly class RationalNumber implements \JsonSerializable
{
    /**
     * floor(sqrt(PHP_INT_MAX)): the product of two integers up to this value (in absolute value) fits in an int.
     */
    private const SQRT_INT_MAX = 3037000499;

    /**
     * Largest number of decimal places for toPercentage(): 100 * 10^16 still fits in an int.
     */
    private const MAX_PERCENTAGE_DECIMALS = 16;

    /**
     * Largest number of decimal places for sqrt(): 10^(2 * 9) still fits in an int.
     */
    private const MAX_SQRT_DECIMALS = 9;

    /**
     * Largest root before a sqrt() digit step: then 100 * rest + 99 (rest <= 2 * root) and
     * 10 * root + 9 fit in an int.
     */
    private const MAX_SQRT_STEP_ROOT = 46116860184273878; // intdiv(PHP_INT_MAX, 200) - 1

    /** intdiv(PHP_INT_MAX, 100): the largest value that can be multiplied by 100. */
    private const MAX_INT_DIV_100 = 92233720368547758;

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
            throw new IntegerOverflowException(
                "Integer overflow: PHP_INT_MIN cannot be used as a numerator or a denominator "
                . "because its opposite does not fit in an integer."
            );
        }

        // Normalize: positive denominator, reduced by the GCD. Each readonly property is assigned once.
        if ($denominator < 0) {
            $numerator = -$numerator;
            $denominator = -$denominator;
        }

        $gcd = self::gcd($numerator, $denominator);
        $this->numerator = $gcd === 1 ? $numerator : intdiv($numerator, $gcd);
        $this->denominator = $gcd === 1 ? $denominator : intdiv($denominator, $gcd);
    }

    /**
     * Restore an instance from unserialize() with the same checks and normalization as the
     * constructor, which unserialize() bypasses. The serialized format is unchanged.
     *
     * @param array<mixed> $data The serialized properties.
     * @throws InvalidArgumentException if the data does not describe a valid rational number.
     * @throws IntegerOverflowException if the numerator or the denominator is PHP_INT_MIN.
     */
    public function __unserialize(array $data): void
    {
        // Native serialization stores private properties under "\0Class\0name".
        $prefix = "\0" . self::class . "\0";
        $numerator = $data[$prefix . 'numerator'] ?? $data['numerator'] ?? null;
        $denominator = $data[$prefix . 'denominator'] ?? $data['denominator'] ?? null;

        if (!is_int($numerator) || !is_int($denominator)) {
            throw new InvalidArgumentException("Invalid serialized RationalNumber: integer numerator and denominator expected.");
        }

        $number = new self($numerator, $denominator);
        $this->numerator = $number->numerator;
        $this->denominator = $number->denominator;
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
                throw new IntegerOverflowException(
                    "Integer overflow in fraction string '{$input}'. Numerator exceeds PHP_INT_MAX."
                );
            }

            if (!DecimalParser::fitsInInt(ltrim($denominatorStr, '+-0'))) {
                throw new IntegerOverflowException(
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
     * @param RationalNumber $number The RationalNumber object to multiply with.
     * @return RationalNumber The result of the multiplication as a new RationalNumber object.
     * @throws IntegerOverflowException if the operation would cause integer overflow.
     */
    public function multiply(RationalNumber $number): RationalNumber
    {
        $a = $this->numerator;
        $b = $this->denominator;
        $c = $number->numerator;
        $d = $number->denominator;

        // Common case: both products fit (the constructor reduces the result).
        if (self::productFits($a, $c) && self::productFits($b, $d)) {
            return new self($a * $c, $b * $d);
        }

        // Cancel crosswise first. a/b and c/d are reduced, so the result is reduced too and the
        // products overflow only when the result itself does not fit in an int.
        $g1 = self::gcd($a, $d);
        $g2 = self::gcd($c, $b);

        return new self(
            self::mulChecked(intdiv($a, $g1), intdiv($c, $g2), 'multiplication'),
            self::mulChecked(intdiv($b, $g2), intdiv($d, $g1), 'multiplication')
        );
    }

    /**
     * Add the current rational number to another RationalNumber object.
     * @param RationalNumber $number The RationalNumber object to add.
     * @return RationalNumber The result of the addition as a new RationalNumber object.
     * @throws IntegerOverflowException if the result, or in rare cases an intermediate sum, does not fit in an int.
     */
    public function add(RationalNumber $number): RationalNumber
    {
        return $this->addFraction($number->numerator, $number->denominator, 'addition');
    }

    /**
     * Subtract another RationalNumber object from the current rational number.
     * @param RationalNumber $number The RationalNumber object to subtract.
     * @return RationalNumber The result of the subtraction as a new RationalNumber object.
     * @throws IntegerOverflowException if the result, or in rare cases an intermediate sum, does not fit in an int.
     */
    public function subtract(RationalNumber $number): RationalNumber
    {
        // -c always fits: PHP_INT_MIN is never stored.
        return $this->addFraction(-$number->numerator, $number->denominator, 'subtraction');
    }

    /**
     * This number plus c/d (d > 0, c/d reduced).
     *
     * @throws IntegerOverflowException if the result, or the sum a * (d / g) + c * (b / g), does not fit.
     */
    private function addFraction(int $c, int $d, string $operation): self
    {
        $a = $this->numerator;
        $b = $this->denominator;

        // Common case: a/b + c/d = (a * d + c * b) / (b * d) when everything fits.
        if (self::productFits($a, $d) && self::productFits($c, $b) && self::productFits($b, $d)) {
            $left = $a * $d;
            $right = $c * $b;
            if ($right > 0 ? $left <= PHP_INT_MAX - $right : $left >= -PHP_INT_MAX - $right) {
                return new self($left + $right, $b * $d);
            }
        }

        // Knuth (TAOCP 4.5.1): with g = gcd(b, d), t = a * (d / g) + c * (b / g) and g2 = gcd(t, g),
        // the reduced sum is (t / g2) / ((b / g) * (d / g2)). Only t can overflow when the result fits.
        $g = self::gcd($b, $d);
        $t = self::addChecked(
            self::mulChecked($a, intdiv($d, $g), $operation),
            self::mulChecked($c, intdiv($b, $g), $operation),
            $operation
        );
        $g2 = self::gcd($t, $g);

        return new self(intdiv($t, $g2), self::mulChecked(intdiv($b, $g), intdiv($d, $g2), $operation));
    }

    /**
     * Divide the current rational number by another RationalNumber object.
     * @param RationalNumber $number The RationalNumber object to divide by.
     * @return RationalNumber The result of the division as a new RationalNumber object.
     * @throws DivisionByZeroException if dividing by zero.
     */
    public function divideBy(RationalNumber $number): RationalNumber
    {
        if ($number->isZero()) {
            throw new DivisionByZeroException("Cannot divide by zero.");
        }
        // To divide by a number, we multiply by its reciprocal.
        $reciprocal = $number->reciprocal();
        return $this->multiply($reciprocal);
    }

    /**
     * Convert the rational number to a percentage with a specified number of decimal places.
     *
     * The result is exact for any value: the percentage is rounded half away from zero (as
     * number_format()) without going through a float.
     *
     * @param int $decimalPlaces The number of decimal places for the percentage, from 0 to 16 (default is 2).
     * @return string The rational number as a percentage string, e.g. "1,234.50%".
     * @throws InvalidArgumentException if $decimalPlaces is out of range.
     */
    public function toPercentage(int $decimalPlaces = 2): string
    {
        self::checkDecimalPlaces($decimalPlaces, self::MAX_PERCENTAGE_DECIMALS);

        // |x| = q + r / den, so |x| * 100 = 100 * q + r * 100 / den. The integer part q is only
        // written out (never multiplied, so no overflow); the fraction is scaled and rounded.
        $scale = 10 ** $decimalPlaces;
        $quotient = intdiv(abs($this->numerator), $this->denominator);
        $fraction = $this->roundedFraction(100 * $scale);   // 0 to 100 * scale

        // Percent units of the fraction (0 to 100): 100 carries into the integer part.
        $percent = intdiv($fraction, $scale);
        if ($percent === 100) {
            $quotient++;
            $percent = 0;
        }

        // At least one digit before the decimal point: 5 with 2 decimals is "0.05".
        $integerDigits = $quotient > 0
            ? $quotient . str_pad((string) $percent, 2, '0', STR_PAD_LEFT)
            : (string) $percent;
        $fractionDigits = $decimalPlaces > 0
            ? '.' . str_pad((string) ($fraction % $scale), $decimalPlaces, '0', STR_PAD_LEFT)
            : '';

        // Same format as number_format(): "," between thousands, "." before decimals, no "-0".
        $grouped = strlen($integerDigits) > 3
            ? strrev(implode(',', str_split(strrev($integerDigits), 3)))
            : $integerDigits;
        $negative = $this->numerator < 0 && ($quotient > 0 || $fraction > 0);

        return ($negative ? '-' : '') . $grouped . $fractionDigits . '%';
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
        return new self($value->numerator, self::mulChecked($value->denominator, 100, 'percentage conversion'));
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
     * @param RationalNumber $other The rational number to compare with.
     * @return bool True if equal, false otherwise.
     */
    public function equals(RationalNumber $other): bool
    {
        // Both fractions are always in their simplest form, so equal values have equal parts.
        return $this->numerator === $other->numerator && $this->denominator === $other->denominator;
    }

    /**
     * Compare this rational number to another.
     * @param RationalNumber $other The rational number to compare with.
     * @return int Returns -1 if this < other, 0 if equal, 1 if this > other.
     */
    public function compareTo(RationalNumber $other): int
    {
        $a = $this->numerator;
        $b = $this->denominator;
        $c = $other->numerator;
        $d = $other->denominator;

        // a/b vs c/d => a*d vs c*b. Common case: every term is below sqrt(PHP_INT_MAX), so both
        // products fit (no function call on this hot path). Otherwise check them, or compare exactly.
        if (
            ($b <= self::SQRT_INT_MAX && $d <= self::SQRT_INT_MAX
                && $a <= self::SQRT_INT_MAX && $a >= -self::SQRT_INT_MAX
                && $c <= self::SQRT_INT_MAX && $c >= -self::SQRT_INT_MAX)
            || (self::productFits($a, $d) && self::productFits($c, $b))
        ) {
            return $a * $d <=> $c * $b;
        }

        return self::compareFractions($a, $b, $c, $d);
    }

    /**
     * Compare a/b with c/d exactly (b, d > 0) without multiplying, by comparing their continued
     * fraction expansions: integer parts first, then the inverted remainders.
     *
     * @return int -1, 0 or 1.
     */
    private static function compareFractions(int $a, int $b, int $c, int $d): int
    {
        $sign = 1;
        while (true) {
            // Floor division: the remainders are in [0, b) and [0, d). Computing the remainder
            // with % rather than a - q * b avoids an overflow for large negative values.
            $quotientA = intdiv($a, $b);
            $remainderA = $a % $b;
            if ($remainderA < 0) {
                $remainderA += $b;
                $quotientA--;
            }
            $quotientC = intdiv($c, $d);
            $remainderC = $c % $d;
            if ($remainderC < 0) {
                $remainderC += $d;
                $quotientC--;
            }

            if ($quotientA !== $quotientC) {
                return $sign * ($quotientA <=> $quotientC);
            }
            if ($remainderA === 0 || $remainderC === 0) {
                return $sign * ($remainderA <=> $remainderC);
            }

            // rA/b < rC/d  <=>  b/rA > d/rC: compare the inverted remainders, with the sign flipped.
            [$a, $b, $c, $d] = [$b, $remainderA, $d, $remainderC];
            $sign = -$sign;
        }
    }

    /**
     * This number multiplied by $multiplier, rounded to the nearest integer, halves away from zero
     * (the rounding of PHP's round()). Exact: no float is involved.
     *
     * @param int $multiplier A positive multiplier.
     * @throws IntegerOverflowException if the result does not fit in an int.
     */
    private function roundedMultiple(int $multiplier): int
    {
        // |x| * m = (q + r / den) * m = q * m + round(r * m / den)
        $q = intdiv(abs($this->numerator), $this->denominator);
        $fraction = $this->roundedFraction($multiplier);

        $magnitude = self::addChecked(self::mulChecked($q, $multiplier, 'rounding'), $fraction, 'rounding');

        return $this->numerator < 0 ? -$magnitude : $magnitude;
    }

    /**
     * The fractional part of |x| multiplied by $multiplier, rounded to the nearest integer, halves
     * away from zero: round((|n| mod den) * m / den), between 0 and m. Exact, and it never overflows.
     *
     * @param int $multiplier A positive multiplier.
     */
    private function roundedFraction(int $multiplier): int
    {
        $r = abs($this->numerator) % $this->denominator;
        [$fraction, $remainder] = self::mulDivMod($r, $multiplier, $this->denominator);

        // Up when remainder / den >= 1/2 (2 * remainder could overflow). fraction < m, so +1 fits.
        return $remainder >= $this->denominator - $remainder ? $fraction + 1 : $fraction;
    }

    /**
     * floor($a * $b / $m) and ($a * $b) mod $m, exactly, for 0 <= $a < $m and $b >= 0.
     *
     * @return array{int, int} [quotient, remainder]
     * @throws IntegerOverflowException if the quotient does not fit in an int.
     */
    private static function mulDivMod(int $a, int $b, int $m): array
    {
        if (self::productFits($a, $b)) {
            $product = $a * $b;
            return [intdiv($product, $m), $product % $m];
        }

        // a * b / m = a * floor(b / m) + a * (b mod m) / m. The first term is the big one.
        $quotient = self::mulChecked($a, intdiv($b, $m), 'rounding');

        // a * (b mod m), both factors below m: binary multiplication modulo m, keeping the quotient
        // (it stays below b mod m) and the remainder below m at every step. Starting at the highest
        // set bit of the factor skips the steps that would only double (0, 0).
        $factor = $b % $m;
        $q = 0;
        $r = 0;
        for ($bit = strlen(decbin($factor)) - 1; $bit >= 0; $bit--) {
            // (q, r) <- 2 * (q, r)
            $q *= 2;
            if ($r >= $m - $r) {
                $r -= $m - $r;
                $q++;
            } else {
                $r += $r;
            }

            if (($factor >> $bit) & 1) {
                // (q, r) <- (q, r) + a
                if ($r >= $m - $a) {
                    $r -= $m - $a;
                    $q++;
                } else {
                    $r += $a;
                }
            }
        }

        return [self::addChecked($quotient, $q, 'rounding'), $r];
    }

    /**
     * Check whether $a * $b fits in an int (|a * b| <= PHP_INT_MAX).
     */
    private static function productFits(int $a, int $b): bool
    {
        // intdiv() keeps the bound exact: a float division rounds it up (PHP_INT_MAX / 2 gives 2^62).
        return $a === 0 || $b === 0 || abs($a) <= intdiv(PHP_INT_MAX, abs($b));
    }

    /**
     * Check if this rational number is greater than another.
     * @param RationalNumber $other The rational number to compare with.
     * @return bool True if this > other.
     */
    public function isGreaterThan(RationalNumber $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    /**
     * Check if this rational number is less than another.
     * @param RationalNumber $other The rational number to compare with.
     * @return bool True if this < other.
     */
    public function isLessThan(RationalNumber $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    /**
     * Check if this rational number is greater than or equal to another.
     * @param RationalNumber $other The rational number to compare with.
     * @return bool True if this >= other.
     */
    public function isGreaterThanOrEqual(RationalNumber $other): bool
    {
        return $this->compareTo($other) >= 0;
    }

    /**
     * Check if this rational number is less than or equal to another.
     * @param RationalNumber $other The rational number to compare with.
     * @return bool True if this <= other.
     */
    public function isLessThanOrEqual(RationalNumber $other): bool
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
     * Calculate the square root, rounded to a given number of decimal places.
     *
     * The result is exact: it is the square root rounded to the nearest multiple of 10^-$decimals
     * (halves up), computed with integers only. For example sqrt(2) with 6 decimals is
     * 1414214/1000000, and the square root of a perfect square is exact.
     *
     * @param int $decimals Number of decimal places, from 0 to 9.
     * @return RationalNumber The rounded square root, with denominator 10^$decimals (before reduction).
     * @throws InvalidArgumentException if the number is negative or $decimals is out of range.
     * @throws IntegerOverflowException if sqrt(x) * 10^$decimals is above about 4.6 * 10^17
     *                                  (never with 0 decimals; with 9 decimals, x above about 2 * 10^17).
     */
    public function sqrt(int $decimals): RationalNumber
    {
        if ($this->numerator < 0) {
            throw new InvalidArgumentException("Cannot calculate square root of a negative number.");
        }

        self::checkDecimalPlaces($decimals, self::MAX_SQRT_DECIMALS);

        // Digit by digit, as in long division: start from the integer square root of the integer
        // part of x, then append one decimal per step, bringing down the next two decimal digits
        // of x. Invariant after k steps: root^2 + rest + r / den = x * 100^k, with 0 <= rest <= 2 * root,
        // so the intermediate values stay of the order of the result.
        $integerPart = intdiv($this->numerator, $this->denominator);
        $r = $this->numerator % $this->denominator;
        $root = self::isqrt($integerPart);
        $rest = $integerPart - $root * $root;

        for ($i = 0; $i < $decimals; $i++) {
            if ($root > self::MAX_SQRT_STEP_ROOT) {
                throw IntegerOverflowException::during('square root');
            }

            // The next two decimal digits of x: floor(r * 100 / den), and the new remainder.
            if ($r <= self::MAX_INT_DIV_100) {
                $pair = intdiv($r * 100, $this->denominator);
                $r = $r * 100 % $this->denominator;
            } else {
                [$pair, $r] = self::mulDivMod($r, 100, $this->denominator);
            }
            $current = 100 * $rest + $pair;

            // Largest digit with (20 * root + digit) * digit <= current; the estimate is never too low.
            $twentyRoot = 20 * $root;
            $digit = min(9, $twentyRoot === 0 ? self::isqrt($current) : intdiv($current, $twentyRoot));
            while (($twentyRoot + $digit) * $digit > $current) {
                $digit--;
            }

            $rest = $current - ($twentyRoot + $digit) * $digit;
            $root = 10 * $root + $digit;
        }

        // Round to nearest: up when root^2 + rest + r / den >= (root + 1/2)^2 = root^2 + root + 1/4.
        $roundUp = $rest > $root
            || ($rest === $root && $r >= intdiv($this->denominator - 1, 4) + 1);   // r / den >= 1/4

        return new self($roundUp ? $root + 1 : $root, 10 ** $decimals);
    }

    /**
     * Integer square root: the largest integer whose square is at most $n (for $n >= 0).
     */
    private static function isqrt(int $n): int
    {
        // The float estimate is corrected to be exact.
        $root = (int) sqrt($n);
        while ($root * $root > $n) {
            $root--;
        }
        while ($root < self::SQRT_INT_MAX && ($root + 1) * ($root + 1) <= $n) {
            $root++;
        }

        return $root;
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
     * @throws IntegerOverflowException if the rounded numerator does not fit in an int.
     */
    public function round(int $denominator = 1): RationalNumber
    {
        if ($denominator <= 0) {
            throw new InvalidArgumentException("Denominator must be greater than zero.");
        }

        return new self($this->roundedMultiple($denominator), $denominator);
    }

    /**
     * Round down to the nearest integer (floor function).
     *
     * @return RationalNumber The largest integer less than or equal to this number.
     */
    public function floor(): RationalNumber
    {
        // intdiv() truncates towards zero, and % has the sign of the numerator: step down when negative.
        $quotient = intdiv($this->numerator, $this->denominator);

        return new self($this->numerator % $this->denominator < 0 ? $quotient - 1 : $quotient);
    }

    /**
     * Round up to the nearest integer (ceiling function).
     *
     * @return RationalNumber The smallest integer greater than or equal to this number.
     */
    public function ceil(): RationalNumber
    {
        // intdiv() truncates towards zero, and % has the sign of the numerator: step up when positive.
        $quotient = intdiv($this->numerator, $this->denominator);

        return new self($this->numerator % $this->denominator > 0 ? $quotient + 1 : $quotient);
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
     * $a * $b, or an IntegerOverflowException naming $operation if it does not fit in an int.
     */
    private static function mulChecked(int $a, int $b, string $operation): int
    {
        if (!self::productFits($a, $b)) {
            throw IntegerOverflowException::during($operation);
        }

        return $a * $b;
    }

    /**
     * $a + $b, or an IntegerOverflowException naming $operation if |$a + $b| > PHP_INT_MAX.
     */
    private static function addChecked(int $a, int $b, string $operation): int
    {
        if ($b > 0 ? $a > PHP_INT_MAX - $b : $a < -PHP_INT_MAX - $b) {
            throw IntegerOverflowException::during($operation);
        }

        return $a + $b;
    }

    /**
     * @throws InvalidArgumentException if $decimals is not between 0 and $max.
     */
    private static function checkDecimalPlaces(int $decimals, int $max): void
    {
        if ($decimals < 0 || $decimals > $max) {
            throw new InvalidArgumentException("Decimal places must be between 0 and {$max}.");
        }
    }
}
