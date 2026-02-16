<?php

declare(strict_types=1);

namespace RationalNumber\Contract;

/**
 * Interface for comparable objects.
 */
interface Comparable
{
    /**
     * Check if this object equals another.
     * @param Comparable $other The object to compare with.
     * @return bool True if equal, false otherwise.
     */
    public function equals(Comparable $other): bool;

    /**
     * Compare this object to another.
     * @param Comparable $other The object to compare with.
     * @return int Returns -1 if this < other, 0 if equal, 1 if this > other.
     */
    public function compareTo(Comparable $other): int;

    /**
     * Check if this object is greater than another.
     * @param Comparable $other The object to compare with.
     * @return bool True if this > other.
     */
    public function isGreaterThan(Comparable $other): bool;

    /**
     * Check if this object is less than another.
     * @param Comparable $other The object to compare with.
     * @return bool True if this < other.
     */
    public function isLessThan(Comparable $other): bool;

    /**
     * Check if this object is greater than or equal to another.
     * @param Comparable $other The object to compare with.
     * @return bool True if this >= other.
     */
    public function isGreaterThanOrEqual(Comparable $other): bool;

    /**
     * Check if this object is less than or equal to another.
     * @param Comparable $other The object to compare with.
     * @return bool True if this <= other.
     */
    public function isLessThanOrEqual(Comparable $other): bool;
}
