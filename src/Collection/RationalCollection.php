<?php

declare(strict_types=1);

namespace RationalNumber\Collection;

use RationalNumber\Exception\EmptyCollectionException;
use RationalNumber\Exception\InvalidArgumentException;
use RationalNumber\RationalNumber;

/**
 * Collection class for managing multiple RationalNumber instances with aggregate operations.
 *
 * Provides convenient methods for working with collections of rational numbers,
 * including aggregations (sum, average, min, max) and functional operations (map, filter).
 *
 * Implements Countable, IteratorAggregate, and ArrayAccess for intuitive array-like behavior.
 *
 * Example:
 * <code>
 * $grades = new RationalCollection([
 *     RationalNumber::fromFloat(15.5),
 *     RationalNumber::fromFloat(17),
 *     RationalNumber::fromFloat(14.25)
 * ]);
 * $average = $grades->average();
 * </code>
 *
 * @implements \IteratorAggregate<int, RationalNumber>
 * @implements \ArrayAccess<int, RationalNumber>
 */
class RationalCollection implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @var RationalNumber[] Internal storage for RationalNumber instances
     */
    private array $numbers = [];

    /**
     * Constructor for RationalCollection.
     *
     * @param array<RationalNumber> $numbers Optional array of RationalNumber instances to initialize with.
     * @throws InvalidArgumentException if any element is not a RationalNumber instance.
     */
    public function __construct(array $numbers = [])
    {
        foreach ($numbers as $number) {
            $this->add($number);
        }
    }

    /**
     * Add a RationalNumber to the collection.
     *
     * In 3.0 collections become immutable and add() returns a new collection. Write
     * `$collection = $collection->add($number);`, which already works in 2.x, to be ready.
     * See UPGRADE-3.0.md.
     *
     * @param RationalNumber $number The RationalNumber to add.
     * @return self Returns $this for method chaining.
     */
    public function add(RationalNumber $number): self
    {
        $this->numbers[] = $number;
        return $this;
    }

    /**
     * Get a RationalNumber at a specific index.
     *
     * @param int $index The zero-based index.
     * @return RationalNumber The RationalNumber at the specified index.
     * @throws InvalidArgumentException if index is out of bounds.
     */
    public function get(int $index): RationalNumber
    {
        if (!isset($this->numbers[$index])) {
            throw new InvalidArgumentException("Index {$index} is out of bounds.");
        }

        return $this->numbers[$index];
    }

    /**
     * Calculate the sum of all RationalNumbers in the collection.
     *
     * Returns zero if the collection is empty.
     *
     * @return RationalNumber The sum of all numbers.
     */
    public function sum(): RationalNumber
    {
        $sum = RationalNumber::zero();

        foreach ($this->numbers as $n) {
            $sum = $sum->add($n);
        }

        return $sum;
    }

    /**
     * Calculate the average of all RationalNumbers in the collection.
     *
     * @return RationalNumber The average of all numbers.
     * @throws EmptyCollectionException if the collection is empty.
     */
    public function average(): RationalNumber
    {
        if (empty($this->numbers)) {
            throw new EmptyCollectionException("Cannot calculate average of an empty collection.");
        }

        $count = RationalNumber::fromFloat(count($this->numbers));
        return $this->sum()->divideBy($count);
    }

    /**
     * Find the minimum value in the collection.
     *
     * @return RationalNumber The smallest RationalNumber in the collection.
     * @throws EmptyCollectionException if the collection is empty.
     */
    public function min(): RationalNumber
    {
        if (empty($this->numbers)) {
            throw new EmptyCollectionException("Cannot find minimum of an empty collection.");
        }

        return array_reduce(
            array_slice($this->numbers, 1),
            fn (RationalNumber $min, RationalNumber $n) => $n->isLessThan($min) ? $n : $min,
            $this->numbers[0]
        );
    }

    /**
     * Find the maximum value in the collection.
     *
     * @return RationalNumber The largest RationalNumber in the collection.
     * @throws EmptyCollectionException if the collection is empty.
     */
    public function max(): RationalNumber
    {
        if (empty($this->numbers)) {
            throw new EmptyCollectionException("Cannot find maximum of an empty collection.");
        }

        return array_reduce(
            array_slice($this->numbers, 1),
            fn (RationalNumber $max, RationalNumber $n) => $n->isGreaterThan($max) ? $n : $max,
            $this->numbers[0]
        );
    }

    /**
     * Apply a callback to each element and return a new collection with the results.
     *
     * @param callable(RationalNumber): RationalNumber $callback Function that takes a RationalNumber
     *                                                          and returns a RationalNumber.
     * @return self A new RationalCollection with the mapped values.
     */
    public function map(callable $callback): self
    {
        $mapped = array_map($callback, $this->numbers);
        /** @var RationalNumber[] $mapped */
        $mapped = array_values($mapped);

        return new self($mapped);
    }

    /**
     * Filter elements using a callback predicate.
     *
     * @param callable(RationalNumber): bool $callback Function that takes a RationalNumber and returns a boolean.
     * @return self A new RationalCollection containing only elements where callback returned true.
     */
    public function filter(callable $callback): self
    {
        $filtered = array_filter($this->numbers, $callback);
        /** @var RationalNumber[] $filtered */
        $filtered = array_values($filtered);

        return new self($filtered);
    }

    /**
     * Get all RationalNumbers as an array.
     *
     * @return RationalNumber[] Array of all RationalNumber instances.
     */
    public function toArray(): array
    {
        return $this->numbers;
    }

    /**
     * Check if the collection is empty.
     *
     * @return bool True if collection contains no elements.
     */
    public function isEmpty(): bool
    {
        return empty($this->numbers);
    }

    /**
     * Clear all elements from the collection.
     *
     * @deprecated since 2.11, removed in 3.0 (collections become immutable): use new RationalCollection().
     *             See UPGRADE-3.0.md.
     * @return self Returns $this for method chaining.
     */
    public function clear(): self
    {
        self::deprecated('clear()', 'new RationalCollection()');
        $this->numbers = [];
        return $this;
    }

    // ========== Countable Interface ==========

    /**
     * Count the number of elements in the collection.
     *
     * @return int The number of RationalNumber instances in the collection.
     */
    public function count(): int
    {
        return count($this->numbers);
    }

    // ========== IteratorAggregate Interface ==========

    /**
     * Get an iterator for the collection.
     *
     * Allows foreach iteration over the collection.
     *
     * @return \ArrayIterator<int, RationalNumber> Iterator for the RationalNumber instances.
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->numbers);
    }

    // ========== ArrayAccess Interface ==========

    /**
     * Check if an offset exists.
     *
     * @param mixed $offset The offset to check.
     * @return bool True if offset exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        if (!is_int($offset) && !is_string($offset)) {
            return false;
        }

        return isset($this->numbers[$offset]);
    }

    /**
     * Get the value at an offset.
     *
     * @param mixed $offset The offset to retrieve.
     * @return RationalNumber|null The RationalNumber at the offset, or null if not found.
     */
    public function offsetGet(mixed $offset): ?RationalNumber
    {
        if (!is_int($offset) && !is_string($offset)) {
            return null;
        }

        return $this->numbers[$offset] ?? null;
    }

    /**
     * Set a value at an offset.
     *
     * The collection stays a list indexed from 0 to count() - 1: the offset must be null (append),
     * an existing index (replace) or count() (append).
     *
     * @deprecated since 2.11, removed in 3.0 (collections become immutable): use
     *             $collection = $collection->add($number) or build a new RationalCollection([...]).
     *             See UPGRADE-3.0.md.
     * @param mixed $offset The offset to set (null appends to end).
     * @param mixed $value The RationalNumber to set.
     * @throws InvalidArgumentException if value is not a RationalNumber or the offset is not a valid index.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        self::deprecated('$collection[] = / $collection[$i] =', '$collection = $collection->add($number)');

        if (!$value instanceof RationalNumber) {
            throw new InvalidArgumentException("Value must be a RationalNumber instance.");
        }

        if ($offset === null) {
            $this->numbers[] = $value;
            return;
        }

        $count = count($this->numbers);
        if (!is_int($offset) || $offset < 0 || $offset > $count) {
            throw new InvalidArgumentException("Offset must be an integer between 0 and {$count}.");
        }

        $this->numbers[$offset] = $value;
    }

    /**
     * Unset a value at an offset. The following elements are shifted down so that indexes stay
     * contiguous (after unset($collection[1]), the former element 2 is at index 1).
     *
     * @deprecated since 2.11, removed in 3.0 (collections become immutable): use filter().
     *             See UPGRADE-3.0.md.
     * @param mixed $offset The offset to unset.
     */
    public function offsetUnset(mixed $offset): void
    {
        self::deprecated('unset($collection[$i])', 'filter()');

        if (!is_int($offset) && !is_string($offset)) {
            return;
        }

        unset($this->numbers[$offset]);
        $this->numbers = array_values($this->numbers);
    }

    /**
     * Report the use of a mutating API that 3.0 removes.
     */
    private static function deprecated(string $usage, string $replacement): void
    {
        trigger_error(
            "RationalCollection: {$usage} is deprecated since 2.11 and will be removed in 3.0 "
            . "(collections become immutable). Use {$replacement} instead. See UPGRADE-3.0.md.",
            E_USER_DEPRECATED
        );
    }
}
