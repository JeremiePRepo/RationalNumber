<?php

declare(strict_types=1);

namespace RationalNumber\Factory;

use RationalNumber\RationalNumber;

/**
 * Factory for creating RationalNumber instances.
 *
 * @deprecated since 2.11, removed in 3.0: use the static constructors of RationalNumber
 *             (new RationalNumber(), RationalNumber::fromString(), fromFloat(), ...). See UPGRADE-3.0.md.
 */
class RationalNumberFactory implements RationalNumberFactoryInterface
{
    public function __construct()
    {
        trigger_error(
            'RationalNumberFactory is deprecated since 2.11 and will be removed in 3.0. '
            . 'Use the static constructors of RationalNumber instead. See UPGRADE-3.0.md.',
            E_USER_DEPRECATED
        );
    }

    /**
     * Create a RationalNumber from numerator and denominator.
     * @param int $numerator The numerator.
     * @param int $denominator The denominator.
     * @return RationalNumber The created rational number.
     */
    public function create(int $numerator, int $denominator = 1): RationalNumber
    {
        return new RationalNumber($numerator, $denominator);
    }

    /**
     * Create a RationalNumber from a float value.
     * @param float|int $value The value to convert.
     * @return RationalNumber The created rational number.
     */
    public function fromFloat($value): RationalNumber
    {
        return RationalNumber::fromFloat($value);
    }

    /**
     * Create a RationalNumber from a percentage string.
     * @param string $percentage The percentage (e.g., "50%").
     * @return RationalNumber The created rational number.
     */
    public function fromPercentage(string $percentage): RationalNumber
    {
        return RationalNumber::fromPercentage($percentage);
    }

    /**
     * Create a RationalNumber from a string representation.
     * @param string $input The string to parse (e.g., "1/2", "0.25", "5").
     * @return RationalNumber The created rational number.
     */
    public function fromString(string $input): RationalNumber
    {
        return RationalNumber::fromString($input);
    }

    /**
     * Create a RationalNumber representing zero.
     * @return RationalNumber Zero (0/1).
     */
    public function zero(): RationalNumber
    {
        return RationalNumber::zero();
    }

    /**
     * Create a RationalNumber representing one.
     * @return RationalNumber One (1/1).
     */
    public function one(): RationalNumber
    {
        return RationalNumber::one();
    }
}
