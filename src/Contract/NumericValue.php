<?php

declare(strict_types=1);

namespace RationalNumber\Contract;

/**
 * Interface for numeric values.
 *
 * @deprecated since 2.11, removed in 3.0: type against RationalNumber instead. See UPGRADE-3.0.md.
 */
interface NumericValue
{
    /**
     * Get the float representation of the numeric value.
     * @return float The value as a float.
     */
    public function getFloat(): float;

    /**
     * Get the string representation of the numeric value.
     * @return string The value as a string.
     */
    public function toString(): string;

    /**
     * Check if the value is zero.
     * @return bool True if zero, false otherwise.
     */
    public function isZero(): bool;

    /**
     * Check if the value is an integer.
     * @return bool True if integer, false otherwise.
     */
    public function isInteger(): bool;
}
