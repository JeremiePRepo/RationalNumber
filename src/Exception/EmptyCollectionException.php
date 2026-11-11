<?php

declare(strict_types=1);

namespace RationalNumber\Exception;

/**
 * Thrown by RationalCollection::average(), min() and max() on an empty collection.
 *
 * Extends \InvalidArgumentException for backward compatibility with 2.x code that catches it.
 */
class EmptyCollectionException extends InvalidArgumentException
{
}
