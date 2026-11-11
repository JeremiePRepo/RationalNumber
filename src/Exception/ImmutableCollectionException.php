<?php

declare(strict_types=1);

namespace RationalNumber\Exception;

/**
 * Thrown when code tries to modify a RationalCollection through array access
 * ($collection[] = $n, $collection[0] = $n, unset($collection[0])).
 *
 * Collections are immutable: use add(), filter() or new RationalCollection([...]) instead.
 */
class ImmutableCollectionException extends \LogicException implements RationalNumberException
{
}
