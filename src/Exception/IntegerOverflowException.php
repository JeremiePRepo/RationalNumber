<?php

declare(strict_types=1);

namespace RationalNumber\Exception;

/**
 * Thrown when a value or an intermediate result does not fit in a 64-bit integer.
 *
 * In 2.x it extends \ArithmeticError, the type thrown before it existed. In 3.0 it will extend
 * \OverflowException instead: catch RationalNumberException (or this class) to work with both.
 */
class IntegerOverflowException extends \ArithmeticError implements RationalNumberException
{
    /**
     * Create the exception with a hint about the GMP extension appended to the message.
     */
    public static function create(string $message): self
    {
        return new self($message . ' ' . (extension_loaded('gmp')
            ? "Consider using the GMP extension for arbitrary precision arithmetic."
            : "Consider installing the GMP extension for handling larger numbers."));
    }
}
