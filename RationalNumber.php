<?php

include_once dirname(__FILE__)."/vendor/autoload.php";

class RationalNumber {
    private $numerator;
    private $denominator;

    /**
     * Constructor for the RationalNumber class.
     * @param int $numerator The numerator of the rational number.
     * @param int $denominator The denominator of the rational number (default is 1).
     * @throws InvalidArgumentException if the denominator is set to zero.
     */
    public function __construct($numerator, $denominator = 1) {
        if ($denominator == 0) {
            throw new InvalidArgumentException("Denominator cannot be zero.");
        }
        
        $this->numerator = $numerator;
        $this->denominator = $denominator;
        // Normalize to ensure the denominator is positive.
        $this->normalize();
    }
    
    /**
     * Create a RationalNumber object from a float or int value.
     * @param float|int $value The scalar value to create a RationalNumber object from.
     * @return RationalNumber The RationalNumber object created from the scalar value.
     */
    public static function fromFloat($value) {
        // Determine the denominator based on the number of decimal places.
        $denominator = 1;
        $decimalPlaces = strlen(substr(strrchr((string) $value, "."), 1));
        if ($decimalPlaces > 0) {
            $denominator = 10 ** $decimalPlaces;
        }

        // Convert the scalar value to a rational number.
        $numerator = $value * $denominator;
        return new RationalNumber($numerator, $denominator);
    }

    /**
     * Get the floating-point representation of the rational number.
     * @return float The rational number as a float.
     */
    public function getFloat() {
        return $this->numerator / $this->denominator;
    }

    /**
     * Multiply the current rational number by another RationalNumber object.
     * @param RationalNumber $number The RationalNumber object to multiply with.
     * @return RationalNumber The result of the multiplication as a new RationalNumber object.
     */
    public function multiply($number) {
        $newNumerator = $this->numerator * $number->getNumerator();
        $newDenominator = $this->denominator * $number->getDenominator();
        return new RationalNumber($newNumerator, $newDenominator);
    }

    /**
     * Add the current rational number to another RationalNumber object.
     * @param RationalNumber $number The RationalNumber object to add.
     * @return RationalNumber The result of the addition as a new RationalNumber object.
     */
    public function add($number) {
        $newNumerator = $this->numerator * $number->getDenominator() + $number->getNumerator() * $this->denominator;
        $newDenominator = $this->denominator * $number->getDenominator();
        return new RationalNumber($newNumerator, $newDenominator);
    }

    /**
     * Subtract another RationalNumber object from the current rational number.
     * @param RationalNumber $number The RationalNumber object to subtract.
     * @return RationalNumber The result of the subtraction as a new RationalNumber object.
     */
    public function subtract($number) {
        $newNumerator = $this->numerator * $number->getDenominator() - $number->getNumerator() * $this->denominator;
        $newDenominator = $this->denominator * $number->getDenominator();
        return new RationalNumber($newNumerator, $newDenominator);
    }
    
    /**
     * Divide the current rational number by another RationalNumber object.
     * @param RationalNumber $number The RationalNumber object to divide by.
     * @return RationalNumber The result of the division as a new RationalNumber object.
     */
    public function divideBy($number) {
        // To divide by a number, we multiply by its reciprocal.
        $reciprocal = $number->reciprocal();
        return $this->multiply($reciprocal);
    }

    /**
     * Divide another RationalNumber object by the current rational number.
     * @param RationalNumber $number The RationalNumber object to divide.
     * @return RationalNumber The result of the division as a new RationalNumber object.
     */
    public function divideFrom($number) {
        // To divide a number by this rational number, we multiply it by this number's reciprocal.
        $reciprocal = $this->reciprocal();
        return $number->multiply($reciprocal);
    }
    
    /**
     * Convert the rational number to a percentage with a specified number of decimal places.
     * @param int $decimalPlaces The number of decimal places for the percentage (default is 2).
     * @return string The rational number as a percentage string.
     */
    public function toPercentage($decimalPlaces = 2) {
        $percentage = $this->getFloat() * 100;
        return number_format($percentage, $decimalPlaces) . "%";
    }

    /**
     * Create a RationalNumber object from a percentage value.
     * @param string $percentage The percentage value as a string (e.g., "50%").
     * @return RationalNumber The RationalNumber object created from the percentage value.
     */
    public static function fromPercentage($percentage) {
        $percentage = rtrim($percentage, '%'); // Remove the percentage sign if present.
        $value = (float) $percentage / 100;
        return RationalNumber::fromFloat($value);
    }
    
    /**
     * Increase the current rational number by a specified percentage.
     * @param string $percentage The percentage value as a string (e.g., "50%") to increase by.
     * @return RationalNumber The result of the increase as a new RationalNumber object.
     */
    public function increaseByPercentage($percentage) {
        $percentage = rtrim($percentage, '%'); // Remove the percentage sign if present.
        $percentageValue = (float) $percentage / 100;

        // Calculate the increase as a fraction of the current value.
        $increaseFraction = $this->multiply(RationalNumber::fromFloat($percentageValue));

        // Add the increase to the current value.
        $increasedRationalNumber = $this->add($increaseFraction);

        return $increasedRationalNumber;
    }
    
    /**
     * Decrease the current rational number by a specified percentage.
     * @param string $percentage The percentage value as a string (e.g., "50%") to decrease by.
     * @return RationalNumber The result of the decrease as a new RationalNumber object.
     */
    public function decreaseByPercentage($percentage) {
        $percentage = rtrim($percentage, '%'); // Remove the percentage sign if present.
        $percentageValue = (float) $percentage / 100;

        // Calculate the decrease as a fraction of the current value.
        $decreaseFraction = $this->multiply(RationalNumber::fromFloat($percentageValue));

        // Subtract the decrease from the current value.
        $decreasedRationalNumber = $this->subtract($decreaseFraction);

        return $decreasedRationalNumber;
    }

    /**
     * Check if the rational number is equal to zero.
     * @return bool True if the rational number is zero, false otherwise.
     */
    public function isZero() {
        return $this->numerator == 0;
    }

    /**
     * Check if the rational number is an integer.
     * @return bool True if the rational number is an integer, false otherwise.
     */
    public function isInteger() {
        return $this->denominator == 1;
    }

    /**
     * Get the reciprocal of the rational number.
     * @return RationalNumber The reciprocal of the rational number as a new RationalNumber object.
     */
    public function reciprocal() {
        return new RationalNumber($this->denominator, $this->numerator);
    }

    /**
     * Reduce the rational number to its simplest form.
     * @return RationalNumber The reduced rational number as a new RationalNumber object.
     */
    public function reduce() {
        $gcd = $this->gcd($this->numerator, $this->denominator);
        $newNumerator = $this->numerator / $gcd;
        $newDenominator = $this->denominator / $gcd;
        return new RationalNumber($newNumerator, $newDenominator);
    }

    /**
     * Get the numerator of the rational number.
     * @return int The numerator of the rational number.
     */
    public function getNumerator() {
        return $this->numerator;
    }

    /**
     * Get the denominator of the rational number.
     * @return int The denominator of the rational number.
     */
    public function getDenominator() {
        return $this->denominator;
    }

    /**
     * Convert the rational number to a string representation.
     * @return string The rational number as a string in the format "numerator/denominator".
     */
    public function toString() {
        return $this->numerator . "/" . $this->denominator;
    }
    
    /**
     * Convert the rational number to a string representation.
     * @return string The rational number as a string in the format "numerator/denominator".
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Calculate the greatest common divisor (GCD) of two integers using the Euclidean algorithm.
     * @param int $a The first integer.
     * @param int $b The second integer.
     * @return int The GCD of the two integers.
     */
    private function gcd($a, $b) {
        return ($b === 0) ? $a : $this->gcd($b, $a % $b);
    }

    /**
     * Normalize the rational number to its simplest form by reducing the numerator and denominator
     * using their greatest common divisor (GCD).
     */
    private function normalize() {
        $gcd = $this->gcd($this->numerator, $this->denominator);
        if ($gcd != 1) {
            $this->numerator /= $gcd;
            $this->denominator /= $gcd;
        }
    }
}
