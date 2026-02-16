<?php

declare(strict_types=1);

namespace RationalNumber\Calculator;

use RationalNumber\RationalNumber;

/**
 * Calculator for percentage operations on rational numbers.
 * 
 * @deprecated This class is deprecated. Use instance methods on RationalNumber instead.
 *             For example: $number->toPercentage(), $number->increaseByPercentage(), etc.
 * 
 * This class was created to respect the Single Responsibility Principle by separating
 * percentage-specific logic from core rational number arithmetic. However, the convenience
 * methods on RationalNumber are now the preferred API.
 */
class PercentageCalculator
{
    /**
     * Convert a rational number to a percentage string.
     * 
     * @deprecated Use RationalNumber::toPercentage() instead.
     * @param RationalNumber $number The rational number to convert.
     * @param int $decimalPlaces The number of decimal places (default: 2).
     * @return string The percentage representation (e.g., "50.00%").
     */
    public function toPercentage(RationalNumber $number, int $decimalPlaces = 2): string
    {
        trigger_error(
            'PercentageCalculator::toPercentage() is deprecated. Use $number->toPercentage() instead.',
            E_USER_DEPRECATED
        );
        
        $percentage = $number->getFloat() * 100;
        return number_format($percentage, $decimalPlaces) . "%";
    }

    /**
     * Create a rational number from a percentage string.
     * 
     * @deprecated Use RationalNumber::fromPercentage() instead.
     * @param string $percentage The percentage value (e.g., "50%").
     * @return RationalNumber The rational number representation.
     */
    public function fromPercentage(string $percentage): RationalNumber
    {
        trigger_error(
            'PercentageCalculator::fromPercentage() is deprecated. Use RationalNumber::fromPercentage() instead.',
            E_USER_DEPRECATED
        );
        
        $percentage = rtrim($percentage, '%');
        $value = (float) $percentage / 100;
        return RationalNumber::fromFloat($value);
    }

    /**
     * Increase a rational number by a percentage.
     * 
     * @deprecated Use RationalNumber::increaseByPercentage() instead.
     * @param RationalNumber $number The number to increase.
     * @param string $percentage The percentage to increase by (e.g., "10%").
     * @return RationalNumber The increased number.
     */
    public function increaseBy(RationalNumber $number, string $percentage): RationalNumber
    {
        trigger_error(
            'PercentageCalculator::increaseBy() is deprecated. Use $number->increaseByPercentage() instead.',
            E_USER_DEPRECATED
        );
        
        $percentage = rtrim($percentage, '%');
        $percentageValue = (float) $percentage / 100;
        
        $increaseFraction = $number->multiply(RationalNumber::fromFloat($percentageValue));
        return $number->add($increaseFraction);
    }

    /**
     * Decrease a rational number by a percentage.
     * 
     * @deprecated Use RationalNumber::decreaseByPercentage() instead.
     * @param RationalNumber $number The number to decrease.
     * @param string $percentage The percentage to decrease by (e.g., "25%").
     * @return RationalNumber The decreased number.
     */
    public function decreaseBy(RationalNumber $number, string $percentage): RationalNumber
    {
        trigger_error(
            'PercentageCalculator::decreaseBy() is deprecated. Use $number->decreaseByPercentage() instead.',
            E_USER_DEPRECATED
        );
        
        $percentage = rtrim($percentage, '%');
        $percentageValue = (float) $percentage / 100;
        
        $decreaseFraction = $number->multiply(RationalNumber::fromFloat($percentageValue));
        return $number->subtract($decreaseFraction);
    }

    /**
     * Calculate what percentage one rational number is of another.
     * 
     * @deprecated Use $part->divideBy($whole)->toPercentage() instead.
     * @param RationalNumber $part The part value.
     * @param RationalNumber $whole The whole value.
     * @param int $decimalPlaces The number of decimal places (default: 2).
     * @return string The percentage (e.g., "75.00%").
     */
    public function percentageOf(RationalNumber $part, RationalNumber $whole, int $decimalPlaces = 2): string
    {
        trigger_error(
            'PercentageCalculator::percentageOf() is deprecated. Use $part->divideBy($whole)->toPercentage() instead.',
            E_USER_DEPRECATED
        );
        
        if ($whole->isZero()) {
            throw new \InvalidArgumentException("Cannot calculate percentage of zero.");
        }
        
        $ratio = $part->divideBy($whole);
        return $this->toPercentage($ratio, $decimalPlaces);
    }
}
