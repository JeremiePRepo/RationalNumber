<?php

declare(strict_types=1);

namespace RationalNumber;

use InvalidArgumentException;
use RationalNumber\Contract\ArithmeticOperations;
use RationalNumber\Contract\Comparable;
use RationalNumber\Contract\NumericValue;

final class RationalNumber implements ArithmeticOperations, Comparable, NumericValue
{
    private int $numerator;
    private int $denominator;

    /**
     * Constructor for the RationalNumber class.
     * @param int $numerator The numerator of the rational number.
     * @param int $denominator The denominator of the rational number (default is 1).
     * @throws InvalidArgumentException if the denominator is set to zero.
     */
    public function __construct(int $numerator, int $denominator = 1)
    {
        if ($denominator === 0) {
            throw new InvalidArgumentException("Denominator cannot be zero.");
        }
        
        $this->numerator = $numerator;
        $this->denominator = $denominator;
        // Normalize to ensure the denominator is positive.
        $this->normalize();
    }
    
    /**
     * Create a RationalNumber object from a float or int value.
     * Supports standard decimal notation and scientific notation (e.g., 1e-10, 1.5e20).
     * 
     * @param float|int $value The scalar value to create a RationalNumber object from.
     * @return RationalNumber The RationalNumber object created from the scalar value.
     * @throws ArithmeticError if the conversion would cause integer overflow.
     */
    public static function fromFloat($value): RationalNumber
    {
        // Handle integer input directly
        if (is_int($value)) {
            return new self($value, 1);
        }
        
        // Detect and handle scientific notation
        $stringValue = (string) $value;
        if (stripos($stringValue, 'e') !== false) {
            return self::fromScientificNotation($value);
        }
        
        // Handle standard decimal notation
        $denominator = 1;
        $decimalPart = strrchr($stringValue, ".");
        
        if ($decimalPart !== false) {
            $decimalPlaces = strlen(substr($decimalPart, 1));
            if ($decimalPlaces > 0) {
                $denominator = 10 ** $decimalPlaces;
            }
        }

        // Convert the scalar value to a rational number
        $numerator = (int)($value * $denominator);
        return new RationalNumber($numerator, $denominator);
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
     * @throws ArithmeticError if the conversion would cause integer overflow.
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
            
            // Validate for integer overflow before converting to int
            // PHP_INT_MAX is typically 9223372036854775807 on 64-bit systems
            if (abs((float)$numeratorStr) > PHP_INT_MAX) {
                throw new \ArithmeticError(
                    "Integer overflow in fraction string '{$input}'. Numerator exceeds PHP_INT_MAX."
                );
            }
            
            if (abs((float)$denominatorStr) > PHP_INT_MAX) {
                throw new \ArithmeticError(
                    "Integer overflow in fraction string '{$input}'. Denominator exceeds PHP_INT_MAX."
                );
            }
            
            $numerator = (int)$numeratorStr;
            $denominator = (int)$denominatorStr;
            
            // Constructor will handle division by zero validation
            return new self($numerator, $denominator);
        }
        
        // Try to parse as numeric (decimal, integer, or scientific notation)
        if (is_numeric($input)) {
            // Delegate to fromFloat which handles decimals, integers, and scientific notation
            return self::fromFloat((float)$input);
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
     * @throws ArithmeticError if the operation would cause integer overflow.
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
     * @throws ArithmeticError if the operation would cause integer overflow.
     */
    public function add(ArithmeticOperations $number): RationalNumber
    {
        if (!$number instanceof RationalNumber) {
            throw new InvalidArgumentException("Must be a RationalNumber instance.");
        }
        
        // Check for potential overflow in cross-multiplication
        $this->checkMultiplicationOverflow($this->numerator, $number->getDenominator(), 'addition cross-multiplication');
        $this->checkMultiplicationOverflow($number->getNumerator(), $this->denominator, 'addition cross-multiplication');
        $this->checkMultiplicationOverflow($this->denominator, $number->getDenominator(), 'denominator multiplication');
        
        $newNumerator = $this->numerator * $number->getDenominator() + $number->getNumerator() * $this->denominator;
        $newDenominator = $this->denominator * $number->getDenominator();
        return new RationalNumber($newNumerator, $newDenominator);
    }

    /**
     * Subtract another RationalNumber object from the current rational number.
     * @param ArithmeticOperations $number The RationalNumber object to subtract.
     * @return RationalNumber The result of the subtraction as a new RationalNumber object.
     * @throws ArithmeticError if the operation would cause integer overflow.
     */
    public function subtract(ArithmeticOperations $number): RationalNumber
    {
        if (!$number instanceof RationalNumber) {
            throw new InvalidArgumentException("Must be a RationalNumber instance.");
        }
        
        // Check for potential overflow in cross-multiplication
        $this->checkMultiplicationOverflow($this->numerator, $number->getDenominator(), 'subtraction cross-multiplication');
        $this->checkMultiplicationOverflow($number->getNumerator(), $this->denominator, 'subtraction cross-multiplication');
        $this->checkMultiplicationOverflow($this->denominator, $number->getDenominator(), 'denominator multiplication');
        
        $newNumerator = $this->numerator * $number->getDenominator() - $number->getNumerator() * $this->denominator;
        $newDenominator = $this->denominator * $number->getDenominator();
        return new RationalNumber($newNumerator, $newDenominator);
    }
    
    /**
     * Divide the current rational number by another RationalNumber object.
     * @param ArithmeticOperations $number The RationalNumber object to divide by.
     * @return RationalNumber The result of the division as a new RationalNumber object.
     * @throws InvalidArgumentException if dividing by zero.
     */
    public function divideBy(ArithmeticOperations $number): RationalNumber
    {
        if (!$number instanceof RationalNumber) {
            throw new InvalidArgumentException("Must be a RationalNumber instance.");
        }
        if ($number->isZero()) {
            throw new InvalidArgumentException("Cannot divide by zero.");
        }
        // To divide by a number, we multiply by its reciprocal.
        $reciprocal = $number->reciprocal();
        return $this->multiply($reciprocal);
    }

    /**
     * Divide another RationalNumber object by the current rational number.
     * @param ArithmeticOperations $number The RationalNumber object to divide.
     * @return RationalNumber The result of the division as a new RationalNumber object.
     * @throws InvalidArgumentException if dividing by zero.
     */
    public function divideFrom(ArithmeticOperations $number): RationalNumber
    {
        if (!$number instanceof RationalNumber) {
            throw new InvalidArgumentException("Must be a RationalNumber instance.");
        }
        if ($this->isZero()) {
            throw new InvalidArgumentException("Cannot divide by zero.");
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
     * @param string $percentage The percentage value as a string (e.g., "50%").
     * @return RationalNumber The RationalNumber object created from the percentage value.
     */
    public static function fromPercentage(string $percentage): RationalNumber
    {
        $percentage = rtrim($percentage, '%'); // Remove the percentage sign if present.
        $value = (float) $percentage / 100;
        return RationalNumber::fromFloat($value);
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
     * @param string $percentage The percentage value as a string (e.g., "50%") to increase by.
     * @return RationalNumber The result of the increase as a new RationalNumber object.
     */
    public function increaseByPercentage(string $percentage): RationalNumber
    {
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
    public function decreaseByPercentage(string $percentage): RationalNumber
    {
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
     * @throws InvalidArgumentException if the numerator is zero.
     */
    public function reciprocal(): RationalNumber
    {
        if ($this->numerator === 0) {
            throw new InvalidArgumentException("Cannot get reciprocal of zero.");
        }
        return new RationalNumber($this->denominator, $this->numerator);
    }

    /**
     * Reduce the rational number to its simplest form.
     * @return RationalNumber The reduced rational number as a new RationalNumber object.
     */
    public function reduce(): RationalNumber
    {
        $gcd = $this->gcd($this->numerator, $this->denominator);
        $newNumerator = $this->numerator / $gcd;
        $newDenominator = $this->denominator / $gcd;
        return new RationalNumber((int)$newNumerator, (int)$newDenominator);
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
     * Calculate the greatest common divisor (GCD) of two integers using the Euclidean algorithm.
     * @param int $a The first integer.
     * @param int $b The second integer.
     * @return int The GCD of the two integers.
     */
    private function gcd(int $a, int $b): int
    {
        $a = abs($a);
        $b = abs($b);
        return ($b === 0) ? $a : $this->gcd($b, $a % $b);
    }

    /**
     * Check if a multiplication operation would cause integer overflow.
     * 
     * @param int $a The first operand.
     * @param int $b The second operand.
     * @param string $operation A description of the operation for error message context.
     * @return void
     * @throws ArithmeticError if the multiplication would overflow.
     */
    private function checkMultiplicationOverflow(int $a, int $b, string $operation): void
    {
        // Zero is always safe
        if ($a === 0 || $b === 0) {
            return;
        }
        
        // Check if the result would exceed PHP_INT_MAX or go below PHP_INT_MIN
        // Use division to check: if a * b would overflow, then |a| > PHP_INT_MAX / |b|
        $absA = abs($a);
        $absB = abs($b);
        
        if ($absA > PHP_INT_MAX / $absB) {
            $message = sprintf(
                "Operation would cause integer overflow during %s. ",
                $operation
            );
            
            if (extension_loaded('gmp')) {
                $message .= "Consider using the GMP extension for arbitrary precision arithmetic.";
            } else {
                $message .= "Consider installing the GMP extension for handling larger numbers.";
            }
            
            throw new \ArithmeticError($message);
        }
    }

    /**
     * Convert a float in scientific notation to a RationalNumber.
     * 
     * @param float $value The float value in scientific notation.
     * @return RationalNumber The resulting rational number.
     * @throws ArithmeticError if conversion would cause overflow.
     */
    private static function fromScientificNotation(float $value): RationalNumber
    {
        // Handle zero specially
        if ($value == 0.0) {
            return new self(0, 1);
        }
        
        // Parse scientific notation: extract mantissa and exponent
        // e.g., "1.5e-10" or "2.3E20"
        $stringValue = strtolower((string)$value);
        $parts = explode('e', $stringValue);
        
        if (count($parts) !== 2) {
            // Fallback to standard conversion if parsing fails
            return self::fromFloat((float)$stringValue);
        }
        
        $mantissa = (float)$parts[0];
        $exponent = (int)$parts[1];
        
        // For negative exponents: mantissa / 10^|exponent|
        // For positive exponents: mantissa * 10^exponent
        if ($exponent < 0) {
            // Small numbers: 1.5e-10 = 15 / 10^11
            $decimalPlaces = abs($exponent);
            
            // Count decimal places in mantissa
            $mantissaStr = (string)$mantissa;
            if (strpos($mantissaStr, '.') !== false) {
                $mantissaDecimals = strlen(substr(strrchr($mantissaStr, '.'), 1));
                $decimalPlaces += $mantissaDecimals;
            }
            
            // Limit precision to avoid overflow
            $decimalPlaces = min($decimalPlaces, 15);
            $denominator = 10 ** $decimalPlaces;
            $numerator = (int)round($value * $denominator);
            
            return new self($numerator, $denominator);
        } else {
            // Large numbers: 1.5e20 = 15 * 10^19
            // Calculate numerator and check for overflow
            $multiplier = 10 ** $exponent;
            
            // Extract decimal places from mantissa
            $mantissaStr = (string)$mantissa;
            $denominator = 1;
            
            if (strpos($mantissaStr, '.') !== false) {
                $decimalPlaces = strlen(substr(strrchr($mantissaStr, '.'), 1));
                $denominator = 10 ** $decimalPlaces;
                $mantissa = $mantissa * $denominator;
            }
            
            $numerator = (int)$mantissa;
            
            // Check if multiplying by 10^exponent would overflow
            if ($numerator != 0 && abs($multiplier) > PHP_INT_MAX / abs($numerator)) {
                throw new \ArithmeticError(
                    "Scientific notation conversion would cause integer overflow. " .
                    "Value too large for integer representation. " .
                    (extension_loaded('gmp') 
                        ? "Consider using GMP extension for arbitrary precision."
                        : "Consider installing GMP extension for larger numbers.")
                );
            }
            
            return new self($numerator * $multiplier, $denominator);
        }
    }

    /**
     * Normalize the rational number to its simplest form by reducing the numerator and denominator
     * using their greatest common divisor (GCD), and ensuring the denominator is always positive.
     */
    private function normalize(): void
    {
        // Ensure denominator is always positive
        if ($this->denominator < 0) {
            $this->numerator = -$this->numerator;
            $this->denominator = -$this->denominator;
        }
        
        // Reduce to simplest form
        $gcd = $this->gcd($this->numerator, $this->denominator);
        if ($gcd !== 1) {
            $this->numerator = (int)($this->numerator / $gcd);
            $this->denominator = (int)($this->denominator / $gcd);
        }
    }
}
