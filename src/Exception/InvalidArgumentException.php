<?php

declare(strict_types=1);

namespace RationalNumber\Exception;

/**
 * Thrown when an input cannot be turned into a rational number (invalid format, zero denominator,
 * INF or NAN, invalid array or JSON...).
 */
class InvalidArgumentException extends \InvalidArgumentException implements RationalNumberException
{
}
