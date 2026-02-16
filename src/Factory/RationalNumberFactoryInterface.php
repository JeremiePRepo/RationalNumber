<?php

declare(strict_types=1);

namespace RationalNumber\Factory;

use RationalNumber\RationalNumber;

/**
 * Interface for creating RationalNumber instances.
 */
interface RationalNumberFactoryInterface
{
    /**
     * Create a RationalNumber from numerator and denominator.
     * @param int $numerator The numerator.
     * @param int $denominator The denominator.
     * @return RationalNumber The created rational number.
     */
    public function create(int $numerator, int $denominator = 1): RationalNumber;

    /**
     * Create a RationalNumber from a float value.
     * @param float|int $value The value to convert.
     * @return RationalNumber The created rational number.
     */
    public function fromFloat($value): RationalNumber;

    /**
     * Create a RationalNumber from a percentage string.
     * @param string $percentage The percentage (e.g., "50%").
     * @return RationalNumber The created rational number.
     */
    public function fromPercentage(string $percentage): RationalNumber;

    /**
     * Create a RationalNumber from a string representation.
     * @param string $input The string to parse (e.g., "1/2", "0.25", "5").
     * @return RationalNumber The created rational number.
     */
    public function fromString(string $input): RationalNumber;

    /**
     * Create a RationalNumber representing zero.
     * @return RationalNumber Zero (0/1).
     */
    public function zero(): RationalNumber;

    /**
     * Create a RationalNumber representing one.
     * @return RationalNumber One (1/1).
     */
    public function one(): RationalNumber;
}
