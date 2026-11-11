<?php

declare(strict_types=1);

namespace RationalNumber\Internal;

use RationalNumber\Exception\IntegerOverflowException;

/**
 * Exact conversion of decimal strings and floats to a numerator / denominator pair.
 *
 * Decimal strings are parsed digit by digit, never through a float, so "0.1" is exactly 1/10.
 *
 * @internal This class is not part of the public API and may change without notice.
 */
final class DecimalParser
{
    /**
     * Decimal digits of PHP_INT_MAX, used for exact overflow checks on digit strings.
     */
    private const INT_MAX_DIGITS = PHP_INT_MAX . '';

    /**
     * Largest power of ten that fits in a 64-bit integer (10^18).
     */
    private const MAX_POWER_OF_TEN = 18;

    /**
     * Floats below 2^53 in absolute value that have no fractional part are exact integers.
     */
    private const MAX_EXACT_FLOAT_INTEGER = 9007199254740992.0;

    /**
     * Parse a decimal string (e.g. "-12.50", ".5", "1.5e-3", "1.0E+25") exactly.
     *
     * @return array{int, int}|null [numerator, denominator] (not reduced), or null if the format is invalid.
     * @throws IntegerOverflowException if the value cannot be represented exactly with 64-bit integers.
     */
    public static function parse(string $input): ?array
    {
        $parts = self::split($input);

        return $parts === null ? null : self::toFraction(...$parts);
    }

    /**
     * Convert a finite float from its shortest decimal representation (the one PHP uses with
     * serialize_precision = -1, e.g. 0.29 => 29/100), independently of the ini settings.
     *
     * If that decimal value does not fit in 64-bit integers, it is rounded (half up) to the largest
     * number of significant digits that fits.
     *
     * @return array{int, int} [numerator, denominator] (not reduced).
     * @throws IntegerOverflowException if the value is too large, or too small to be represented without becoming zero.
     */
    public static function fromFloat(float $value): array
    {
        // Fast path for the common case of integral floats (e.g. 100.0, -0.0).
        if (abs($value) < self::MAX_EXACT_FLOAT_INTEGER && $value === floor($value)) {
            return [(int) $value, 1];
        }

        [$sign, $digits, $exponent] = self::split(self::shortestRepresentation($value))
            ?? throw new \LogicException("Unexpected float representation for {$value}.");
        [$digits, $exponent] = self::roundToFit($digits, $exponent);

        return self::toFraction($sign, $digits, $exponent);
    }

    /**
     * Check whether a string of decimal digits (no sign, no leading zeros) fits in a PHP integer.
     */
    public static function fitsInInt(string $digits): bool
    {
        $length = strlen($digits);
        $maxLength = strlen(self::INT_MAX_DIGITS);

        return $length < $maxLength || ($length === $maxLength && strcmp($digits, self::INT_MAX_DIGITS) <= 0);
    }

    /**
     * Shortest decimal string that round-trips to $value.
     */
    private static function shortestRepresentation(float $value): string
    {
        // var_export() follows serialize_precision; -1 gives the shortest round-trip representation.
        // It is already -1 by default, so the setting is only switched when needed.
        $previousPrecision = ini_get('serialize_precision');
        $switchPrecision = $previousPrecision !== '-1' && $previousPrecision !== false;
        if ($switchPrecision) {
            ini_set('serialize_precision', '-1');
        }
        try {
            return var_export($value, true);
        } finally {
            if ($switchPrecision) {
                ini_set('serialize_precision', $previousPrecision);
            }
        }
    }

    /**
     * Split a decimal string into an exact sign / significant digits / base-10 exponent triple.
     *
     * The value is $sign * $digits * 10^$exponent. Leading and trailing zeros are removed
     * from $digits (an empty $digits means zero).
     *
     * @return array{int, string, int}|null [sign, digits, exponent], or null if the format is invalid.
     */
    private static function split(string $input): ?array
    {
        if (!preg_match('/^([+-]?)(\d*)(?:\.(\d*))?(?:[eE]([+-]?\d+))?$/', $input, $matches)) {
            return null;
        }

        $integerPart = $matches[2];
        $fractionalPart = $matches[3] ?? '';
        $exponentPart = $matches[4] ?? '';

        // At least one digit is required in the mantissa ("." or "e5" are invalid).
        if ($integerPart === '' && $fractionalPart === '') {
            return null;
        }

        $sign = $matches[1] === '-' ? -1 : 1;

        // Clamp absurd exponents so the arithmetic below stays in integer range;
        // any value this large or small overflows anyway (or is zero).
        // (int) saturates at PHP_INT_MAX / PHP_INT_MIN for oversized digit strings.
        $exponent = max(-1000000, min(1000000, (int) $exponentPart));

        $digits = ltrim($integerPart . $fractionalPart, '0');
        $exponent -= strlen($fractionalPart);

        // Trailing zeros only scale the value: move them into the exponent.
        $trimmedDigits = rtrim($digits, '0');
        $exponent += strlen($digits) - strlen($trimmedDigits);

        return [$sign, $trimmedDigits, $exponent];
    }

    /**
     * Build the fraction from the exact decimal parts returned by split().
     *
     * @return array{int, int} [numerator, denominator]
     * @throws IntegerOverflowException if the value cannot be represented exactly with 64-bit integers.
     */
    private static function toFraction(int $sign, string $digits, int $exponent): array
    {
        if ($digits === '') {
            return [0, 1];
        }

        if ($exponent >= 0) {
            // Integer value: digits followed by $exponent zeros (short-circuit before building huge strings).
            if (strlen($digits) + $exponent > strlen(self::INT_MAX_DIGITS)
                || !self::fitsInInt($integerDigits = $digits . str_repeat('0', $exponent))
            ) {
                throw IntegerOverflowException::create(
                    "Integer overflow: the value is too large for integer representation."
                );
            }

            return [$sign * (int) $integerDigits, 1];
        }

        if (-$exponent > self::MAX_POWER_OF_TEN || !self::fitsInInt($digits)) {
            throw IntegerOverflowException::create(
                "Integer overflow: the value has too many digits to be represented exactly."
            );
        }

        return [$sign * (int) $digits, 10 ** -$exponent];
    }

    /**
     * Round decimal parts (half up) to the largest number of significant digits for which both the
     * numerator and the denominator fit in a 64-bit integer. Parts that already fit are unchanged.
     *
     * @return array{string, int} [digits, exponent]
     * @throws IntegerOverflowException if the value would become zero.
     */
    private static function roundToFit(string $digits, int $exponent): array
    {
        // Up to 18 digits always fit, and the denominator 10^-exponent fits up to 10^18.
        if ($exponent >= 0 || (-$exponent <= self::MAX_POWER_OF_TEN && strlen($digits) <= self::MAX_POWER_OF_TEN)) {
            return [$digits, $exponent];
        }

        $dropped = max(-$exponent, strlen($digits)) - self::MAX_POWER_OF_TEN;
        $rounded = (int) substr($digits, 0, -$dropped) + (($digits[-$dropped] ?? '0') >= '5' ? 1 : 0);
        if ($rounded === 0) {
            throw IntegerOverflowException::create(
                "Integer overflow: the value is too small to be represented without becoming zero."
            );
        }

        return [(string) $rounded, $exponent + $dropped];
    }
}
