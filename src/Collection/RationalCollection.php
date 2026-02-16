<?php

declare(strict_types=1);

namespace RationalNumber\Collection;

use InvalidArgumentException;
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
        if (empty($this->numbers)) {
            return RationalNumber::zero();
        }
        
        return array_reduce(
            $this->numbers,
            fn($sum, $n) => $sum->add($n),
            RationalNumber::zero()
        );
    }

    /**
     * Calculate the average of all RationalNumbers in the collection.
     * 
     * @return RationalNumber The average of all numbers.
     * @throws InvalidArgumentException if the collection is empty.
     */
    public function average(): RationalNumber
    {
        if (empty($this->numbers)) {
            throw new InvalidArgumentException("Cannot calculate average of an empty collection.");
        }
        
        $count = RationalNumber::fromFloat(count($this->numbers));
        return $this->sum()->divideBy($count);
    }

    /**
     * Find the minimum value in the collection.
     * 
     * @return RationalNumber The smallest RationalNumber in the collection.
     * @throws InvalidArgumentException if the collection is empty.
     */
    public function min(): RationalNumber
    {
        if (empty($this->numbers)) {
            throw new InvalidArgumentException("Cannot find minimum of an empty collection.");
        }
        
        return array_reduce(
            array_slice($this->numbers, 1),
            fn(RationalNumber $min, RationalNumber $n) => $n->isLessThan($min) ? $n : $min,
            $this->numbers[0]
        );
    }

    /**
     * Find the maximum value in the collection.
     * 
     * @return RationalNumber The largest RationalNumber in the collection.
     * @throws InvalidArgumentException if the collection is empty.
     */
    public function max(): RationalNumber
    {
        if (empty($this->numbers)) {
            throw new InvalidArgumentException("Cannot find maximum of an empty collection.");
        }
        
        return array_reduce(
            array_slice($this->numbers, 1),
            fn(RationalNumber $max, RationalNumber $n) => $n->isGreaterThan($max) ? $n : $max,
            $this->numbers[0]
        );
    }

    /**
     * Apply a callback to each element and return a new collection with the results.
     * 
     * @param callable $callback Function that takes a RationalNumber and returns a RationalNumber.
     * @return self A new RationalCollection with the mapped values.
     */
    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->numbers));
    }

    /**
     * Filter elements using a callback predicate.
     * 
     * @param callable $callback Function that takes a RationalNumber and returns a boolean.
     * @return self A new RationalCollection containing only elements where callback returned true.
     */
    public function filter(callable $callback): self
    {
        return new self(array_filter($this->numbers, $callback));
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
     * @return self Returns $this for method chaining.
     */
    public function clear(): self
    {
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
        return $this->numbers[$offset] ?? null;
    }

    /**
     * Set a value at an offset.
     * 
     * @param mixed $offset The offset to set (null appends to end).
     * @param mixed $value The RationalNumber to set.
     * @throws InvalidArgumentException if value is not a RationalNumber.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof RationalNumber) {
            throw new InvalidArgumentException("Value must be a RationalNumber instance.");
        }
        
        if ($offset === null) {
            $this->numbers[] = $value;
        } else {
            $this->numbers[$offset] = $value;
        }
    }

    /**
     * Unset a value at an offset.
     * 
     * @param mixed $offset The offset to unset.
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->numbers[$offset]);
    }
}
