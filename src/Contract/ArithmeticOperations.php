<?php

declare(strict_types=1);

namespace RationalNumber\Contract;

/**
 * Interface for arithmetic operations on rational numbers.
 */
interface ArithmeticOperations
{
    /**
     * Add another rational number to this one.
     * @param ArithmeticOperations $number The number to add.
     * @return ArithmeticOperations The result of the addition.
     */
    public function add(ArithmeticOperations $number): ArithmeticOperations;

    /**
     * Subtract another rational number from this one.
     * @param ArithmeticOperations $number The number to subtract.
     * @return ArithmeticOperations The result of the subtraction.
     */
    public function subtract(ArithmeticOperations $number): ArithmeticOperations;

    /**
     * Multiply this rational number by another.
     * @param ArithmeticOperations $number The number to multiply by.
     * @return ArithmeticOperations The result of the multiplication.
     */
    public function multiply(ArithmeticOperations $number): ArithmeticOperations;

    /**
     * Divide this rational number by another.
     * @param ArithmeticOperations $number The number to divide by.
     * @return ArithmeticOperations The result of the division.
     */
    public function divideBy(ArithmeticOperations $number): ArithmeticOperations;

    /**
     * Get the reciprocal of this rational number.
     * @return ArithmeticOperations The reciprocal.
     */
    public function reciprocal(): ArithmeticOperations;

    /**
     * Get the absolute value of this rational number.
     * @return ArithmeticOperations The absolute value.
     */
    public function abs(): ArithmeticOperations;

    /**
     * Negate this rational number.
     * @return ArithmeticOperations The negated value.
     */
    public function negate(): ArithmeticOperations;
}
