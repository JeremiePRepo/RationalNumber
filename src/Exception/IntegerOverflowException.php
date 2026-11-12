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
     * An overflow during the given operation, e.g. during('multiplication').
     */
    public static function during(string $operation): self
    {
        return new self("Integer overflow during {$operation}: the result does not fit in a 64-bit integer.");
    }
}
