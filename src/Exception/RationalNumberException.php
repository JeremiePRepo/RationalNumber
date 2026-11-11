<?php

declare(strict_types=1);

namespace RationalNumber\Exception;

/**
 * Marker interface implemented by every exception the library throws.
 *
 * Catch it to handle any RationalNumber error in one place:
 * <code>
 * try {
 *     $result = $a->multiply($b);
 * } catch (RationalNumberException $e) {
 *     // overflow, division by zero, invalid input...
 * }
 * </code>
 */
interface RationalNumberException extends \Throwable
{
}
