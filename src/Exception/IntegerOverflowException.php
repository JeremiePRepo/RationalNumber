<?php

declare(strict_types=1);

namespace RationalNumber\Exception;

/**
 * Thrown when a value or an intermediate result does not fit in a 64-bit integer.
 *
 * Since 3.0 it extends \OverflowException (in 2.x it extended \ArithmeticError). Catch this class
 * or RationalNumberException to handle it.
 */
class IntegerOverflowException extends \OverflowException implements RationalNumberException
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
