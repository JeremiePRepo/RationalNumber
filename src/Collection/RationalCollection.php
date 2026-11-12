<?php

declare(strict_types=1);

namespace RationalNumber\Collection;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use RationalNumber\Exception\EmptyCollectionException;
use RationalNumber\Exception\ImmutableCollectionException;
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
 * $grades = new RationalCollection(
 *     RationalNumber::fromFloat(15.5),
 *     RationalNumber::fromFloat(17),
 *     RationalNumber::fromFloat(14.25)
 * );
 * $average = $grades->average();
 * </code>
 *
 * Collections are immutable: add(), map() and filter() return a new collection, and array access
 * is read-only.
 *
 * @implements IteratorAggregate<int, RationalNumber>
 * @implements ArrayAccess<int, RationalNumber>
 */
final readonly class RationalCollection implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var list<RationalNumber> Elements, indexed from 0 to count() - 1.
     */
    private array $numbers;

    /**
     * Constructor for RationalCollection.
     *
     * From an array, unpack it: new RationalCollection(...$numbers) (keys are ignored).
     *
     * @param RationalNumber ...$numbers The elements (the engine checks their type: \TypeError otherwise).
     */
    public function __construct(RationalNumber ...$numbers)
    {
        // Unpacking an array with string keys passes them as named arguments, which the variadic
        // parameter keeps: reindex.
        $this->numbers = array_values($numbers);
    }

    /**
     * Restore an instance from unserialize() with the same checks as the constructor, which
     * unserialize() bypasses. The serialized format is unchanged.
     *
     * @param array<mixed> $data The serialized properties.
     * @throws InvalidArgumentException if the data does not describe a list of RationalNumber instances.
     */
    public function __unserialize(array $data): void
    {
        // Native serialization stores private properties under "\0Class\0name".
        $numbers = $data["\0" . self::class . "\0numbers"] ?? $data['numbers'] ?? null;

        if (!is_array($numbers)) {
            throw new InvalidArgumentException("Invalid serialized RationalCollection: array of numbers expected.");
        }

        $list = [];
        foreach ($numbers as $number) {
            if (!$number instanceof RationalNumber) {
                throw new InvalidArgumentException("Invalid serialized RationalCollection: RationalNumber elements expected.");
            }
            $list[] = $number;
        }

        $this->numbers = $list;
    }

    /**
     * Return a new collection with a RationalNumber appended. The current collection is unchanged.
     *
     * @param RationalNumber $number The RationalNumber to add.
     * @return self A new collection.
     */
    public function add(RationalNumber $number): self
    {
        return new self(...$this->numbers, ...[$number]);
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
            throw new InvalidArgumentException("Index $index is out of bounds.");
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

        $count = new RationalNumber(count($this->numbers));
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

        $min = $this->numbers[0];
        foreach ($this->numbers as $n) {
            if ($n->compareTo($min) < 0) {
                $min = $n;
            }
        }

        return $min;
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

        $max = $this->numbers[0];
        foreach ($this->numbers as $n) {
            if ($n->compareTo($max) > 0) {
                $max = $n;
            }
        }

        return $max;
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
        return new self(...array_map($callback, $this->numbers));
    }

    /**
     * Filter elements using a callback predicate.
     *
     * @param callable(RationalNumber): bool $callback Function that takes a RationalNumber and returns a boolean.
     * @return self A new RationalCollection containing only elements where callback returned true.
     */
    public function filter(callable $callback): self
    {
        return new self(...array_filter($this->numbers, $callback));
    }

    /**
     * Get all RationalNumbers as an array.
     *
     * @return list<RationalNumber> All RationalNumber instances, indexed from 0.
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
     * @return ArrayIterator<int, RationalNumber> Iterator for the RationalNumber instances.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->numbers);
    }

    // ========== ArrayAccess Interface (read-only) ==========

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
     * Collections are immutable: use add() or new RationalCollection(...) instead.
     *
     * @throws ImmutableCollectionException always.
     */
    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new ImmutableCollectionException(
            'RationalCollection is immutable: use $collection = $collection->add($number) instead.'
        );
    }

    /**
     * Collections are immutable: use filter() instead.
     *
     * @throws ImmutableCollectionException always.
     */
    public function offsetUnset(mixed $offset): never
    {
        throw new ImmutableCollectionException(
            'RationalCollection is immutable: use $collection = $collection->filter(...) instead.'
        );
    }
}
