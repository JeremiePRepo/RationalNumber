<?php

declare(strict_types=1);

namespace RationalNumber\Exception;

/**
 * Thrown when an operation would divide by zero (divideBy(), divideFrom(), reciprocal() of zero).
 *
 * Extends \InvalidArgumentException for backward compatibility with 2.x code that catches it.
 */
class DivisionByZeroException extends InvalidArgumentException
{
}
