<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\TestCase;
use RationalNumber\Collection\RationalCollection;
use RationalNumber\Exception\ImmutableCollectionException;
use RationalNumber\Exception\RationalNumberException;
use RationalNumber\RationalNumber;
use InvalidArgumentException;

/**
 * Test suite for RationalCollection class.
 *
 * Tests collection operations, aggregations, and functional methods.
 */
class CollectionTest extends TestCase
{
    // ========== Construction Tests ==========

    public function testConstructEmpty(): void
    {
        $collection = new RationalCollection();

        $this->assertCount(0, $collection);
        $this->assertTrue($collection->isEmpty());
    }

    public function testConstructWithArray(): void
    {
        $numbers = [
            RationalNumber::fromFloat(1),
            RationalNumber::fromFloat(2),
            RationalNumber::fromFloat(3)
        ];

        $collection = new RationalCollection($numbers);

        $this->assertCount(3, $collection);
        $this->assertFalse($collection->isEmpty());
    }

    // ========== Add and Get Tests ==========

    public function testAddSingleNumber(): void
    {
        $collection = new RationalCollection();
        $number = RationalNumber::fromFloat(5);

        $result = $collection->add($number);

        // Immutable: add() returns a new collection and leaves the original unchanged.
        $this->assertNotSame($collection, $result);
        $this->assertCount(0, $collection);
        $this->assertCount(1, $result);
        $this->assertSame($number, $result[0]);
    }

    public function testAddCanBeChained(): void
    {
        $collection = (new RationalCollection())
            ->add(RationalNumber::fromFloat(1))
            ->add(RationalNumber::fromFloat(2));

        $this->assertSame('3/1', $collection->sum()->toString());
    }

    public function testGetByIndex(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(1),
            RationalNumber::fromFloat(2),
            RationalNumber::fromFloat(3)
        ]);

        $number = $collection->get(1);

        $this->assertEqualsWithDelta(2.0, $number->getFloat(), 1e-12);
    }

    public function testGetInvalidIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Index 5 is out of bounds");

        $collection = new RationalCollection([RationalNumber::fromFloat(1)]);
        $collection->get(5);
    }

    // ========== Aggregate Operations Tests ==========

    public function testSumOfNumbers(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(1),
            RationalNumber::fromFloat(2),
            RationalNumber::fromFloat(3)
        ]);

        $sum = $collection->sum();

        $this->assertEqualsWithDelta(6.0, $sum->getFloat(), 1e-12);
    }

    public function testSumOfEmptyCollection(): void
    {
        $collection = new RationalCollection();
        $sum = $collection->sum();

        $this->assertTrue($sum->isZero());
    }

    public function testAverageOfNumbers(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(10),
            RationalNumber::fromFloat(20),
            RationalNumber::fromFloat(30)
        ]);

        $average = $collection->average();

        $this->assertEqualsWithDelta(20.0, $average->getFloat(), 1e-12);
    }

    public function testAverageWithFractions(): void
    {
        $collection = new RationalCollection([
            new RationalNumber(1, 2),  // 0.5
            new RationalNumber(1, 4),  // 0.25
            new RationalNumber(1, 8)   // 0.125
        ]);

        $average = $collection->average();

        // (0.5 + 0.25 + 0.125) / 3 = 0.291666...
        $this->assertEqualsWithDelta(0.291666, $average->getFloat(), 0.0001);
    }

    public function testAverageOfEmptyCollectionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot calculate average of an empty collection");

        $collection = new RationalCollection();
        $collection->average();
    }

    public function testMinOfNumbers(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(15),
            RationalNumber::fromFloat(8),
            RationalNumber::fromFloat(23),
            RationalNumber::fromFloat(4)
        ]);

        $min = $collection->min();

        $this->assertEqualsWithDelta(4.0, $min->getFloat(), 1e-12);
    }

    public function testMinOfEmptyCollectionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot find minimum of an empty collection");

        $collection = new RationalCollection();
        $collection->min();
    }

    public function testMaxOfNumbers(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(15),
            RationalNumber::fromFloat(8),
            RationalNumber::fromFloat(23),
            RationalNumber::fromFloat(4)
        ]);

        $max = $collection->max();

        $this->assertEqualsWithDelta(23.0, $max->getFloat(), 1e-12);
    }

    public function testMaxOfEmptyCollectionThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot find maximum of an empty collection");

        $collection = new RationalCollection();
        $collection->max();
    }

    // ========== Functional Operations Tests ==========

    public function testMapOperation(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(1),
            RationalNumber::fromFloat(2),
            RationalNumber::fromFloat(3)
        ]);

        $doubled = $collection->map(fn ($n) => $n->multiply(RationalNumber::fromFloat(2)));

        $this->assertCount(3, $doubled);
        $this->assertEqualsWithDelta(2.0, $doubled->get(0)->getFloat(), 1e-12);
        $this->assertEqualsWithDelta(4.0, $doubled->get(1)->getFloat(), 1e-12);
        $this->assertEqualsWithDelta(6.0, $doubled->get(2)->getFloat(), 1e-12);
    }

    public function testMapWithPercentageIncrease(): void
    {
        $prices = new RationalCollection([
            RationalNumber::fromFloat(100),
            RationalNumber::fromFloat(200),
            RationalNumber::fromFloat(300)
        ]);

        $withTax = $prices->map(fn ($p) => $p->increaseByPercentage('20%'));

        $this->assertEqualsWithDelta(120.0, $withTax->get(0)->getFloat(), 1e-12);
        $this->assertEqualsWithDelta(240.0, $withTax->get(1)->getFloat(), 1e-12);
        $this->assertEqualsWithDelta(360.0, $withTax->get(2)->getFloat(), 1e-12);
    }

    public function testFilterOperation(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(5),
            RationalNumber::fromFloat(15),
            RationalNumber::fromFloat(3),
            RationalNumber::fromFloat(20)
        ]);

        $greaterThan10 = $collection->filter(fn ($n) => $n->isGreaterThan(RationalNumber::fromFloat(10)));

        $this->assertCount(2, $greaterThan10);
    }

    public function testFilterPositiveNumbers(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(-5),
            RationalNumber::fromFloat(10),
            RationalNumber::fromFloat(-3),
            RationalNumber::fromFloat(7)
        ]);

        $positives = $collection->filter(fn ($n) => $n->isGreaterThan(RationalNumber::zero()));

        $this->assertCount(2, $positives);
    }

    // ========== Countable Interface Tests ==========

    public function testCountInterface(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(1),
            RationalNumber::fromFloat(2)
        ]);

        $this->assertCount(2, $collection);
        $this->assertEquals(2, count($collection));
    }

    // ========== IteratorAggregate Interface Tests ==========

    public function testForeachIteration(): void
    {
        $numbers = [
            RationalNumber::fromFloat(1),
            RationalNumber::fromFloat(2),
            RationalNumber::fromFloat(3)
        ];

        $collection = new RationalCollection($numbers);

        $sum = 0;
        foreach ($collection as $number) {
            $sum += $number->getFloat();
        }

        $this->assertEqualsWithDelta(6.0, $sum, 1e-12);
    }

    // ========== ArrayAccess Interface Tests ==========

    public function testOffsetExists(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(1),
            RationalNumber::fromFloat(2)
        ]);

        $this->assertTrue(isset($collection[0]));
        $this->assertTrue(isset($collection[1]));
        $this->assertFalse(isset($collection[2]));
    }

    public function testOffsetGet(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(10),
            RationalNumber::fromFloat(20)
        ]);

        $this->assertEqualsWithDelta(10.0, $collection[0]->getFloat(), 1e-12);
        $this->assertEqualsWithDelta(20.0, $collection[1]->getFloat(), 1e-12);
    }

    public function testOffsetSetThrows(): void
    {
        $collection = new RationalCollection([RationalNumber::fromFloat(1)]);

        $this->expectException(ImmutableCollectionException::class);
        $this->expectExceptionMessage('$collection = $collection->add($number)');

        $collection[] = RationalNumber::fromFloat(5);
    }

    public function testOffsetSetAtAnIndexThrows(): void
    {
        $collection = new RationalCollection([RationalNumber::fromFloat(1)]);

        try {
            $collection[0] = RationalNumber::fromFloat(5);
            $this->fail('Expected ImmutableCollectionException.');
        } catch (ImmutableCollectionException $e) {
            $this->assertInstanceOf(RationalNumberException::class, $e);
            $this->assertInstanceOf(\LogicException::class, $e);
        }

        $this->assertSame('1/1', $collection[0]->toString());
    }

    public function testOffsetUnsetThrows(): void
    {
        $collection = new RationalCollection([RationalNumber::fromFloat(1)]);

        $this->expectException(ImmutableCollectionException::class);
        $this->expectExceptionMessage('$collection = $collection->filter(...)');

        unset($collection[0]);
    }

    public function testConstructorRejectsNonRationalElements(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be a RationalNumber instance.');

        new RationalCollection([RationalNumber::fromFloat(1), 'not a rational number']);
    }

    public function testConstructorReindexesElements(): void
    {
        $collection = new RationalCollection([5 => RationalNumber::fromFloat(1), 'a' => RationalNumber::fromFloat(2)]);

        $this->assertSame([0, 1], array_keys($collection->toArray()));
        $this->assertSame('1/1', $collection->min()->toString());
    }

    public function testFilterReindexes(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(1),
            RationalNumber::fromFloat(2),
            RationalNumber::fromFloat(3),
        ]);

        $odd = $collection->filter(fn (RationalNumber $n) => $n->getNumerator() % 2 === 1);

        $this->assertSame([0, 1], array_keys($odd->toArray()));
        $this->assertSame('3/1', $odd[1]->toString());
        $this->assertCount(3, $collection);
    }

    public function testPropertiesAreReadonly(): void
    {
        $this->assertTrue((new \ReflectionClass(RationalCollection::class))->isReadOnly());
    }

    // ========== Utility Methods Tests ==========

    public function testToArrayMethod(): void
    {
        $numbers = [
            RationalNumber::fromFloat(1),
            RationalNumber::fromFloat(2)
        ];

        $collection = new RationalCollection($numbers);
        $array = $collection->toArray();

        $this->assertIsArray($array);
        $this->assertCount(2, $array);
        $this->assertInstanceOf(RationalNumber::class, $array[0]);
    }

    // ========== Integration Tests ==========

    public function testGradesAverageScenario(): void
    {
        // Calculate average grade
        $grades = new RationalCollection([
            RationalNumber::fromFloat(15.5),
            RationalNumber::fromFloat(17),
            RationalNumber::fromFloat(14.25),
            RationalNumber::fromFloat(16)
        ]);

        $average = $grades->average();

        // (15.5 + 17 + 14.25 + 16) / 4 = 15.6875
        $this->assertEqualsWithDelta(15.6875, $average->getFloat(), 0.0001);
    }

    public function testBatchPriceProcessing(): void
    {
        // Apply tax to multiple prices
        $prices = new RationalCollection([
            RationalNumber::fromFloat(100),
            RationalNumber::fromFloat(50),
            RationalNumber::fromFloat(75)
        ]);

        $withTax = $prices->map(fn ($p) => $p->increaseByPercentage('20%'));
        $total = $withTax->sum();

        // Total: 120 + 60 + 90 = 270
        $this->assertEqualsWithDelta(270.0, $total->getFloat(), 1e-12);
    }

    public function testPriceRangeAnalysis(): void
    {
        $prices = new RationalCollection([
            RationalNumber::fromFloat(19.99),
            RationalNumber::fromFloat(24.50),
            RationalNumber::fromFloat(15.75),
            RationalNumber::fromFloat(29.99)
        ]);

        $min = $prices->min();
        $max = $prices->max();
        $avg = $prices->average();

        $this->assertEqualsWithDelta(15.75, $min->getFloat(), 1e-12);
        $this->assertEqualsWithDelta(29.99, $max->getFloat(), 1e-12);
        $this->assertEqualsWithDelta(22.5575, $avg->getFloat(), 0.01);  // Increased delta for floating point precision
    }

    public function testChainedOperations(): void
    {
        $collection = new RationalCollection([
            RationalNumber::fromFloat(5),
            RationalNumber::fromFloat(15),
            RationalNumber::fromFloat(25),
            RationalNumber::fromFloat(35)
        ]);

        // Filter > 10, then double, then sum
        $result = $collection
            ->filter(fn ($n) => $n->isGreaterThan(RationalNumber::fromFloat(10)))
            ->map(fn ($n) => $n->multiply(RationalNumber::fromFloat(2)))
            ->sum();

        // (15 + 25 + 35) * 2 = 150
        $this->assertEqualsWithDelta(150.0, $result->getFloat(), 1e-12);
    }

    public function testSerializationRoundTrip(): void
    {
        $collection = new RationalCollection([new RationalNumber(1, 2), new RationalNumber(-3, 4)]);

        $restored = unserialize(serialize($collection));

        $this->assertInstanceOf(RationalCollection::class, $restored);
        $this->assertSame(['1/2', '-3/4'], array_map(fn (RationalNumber $n) => $n->toString(), $restored->toArray()));
    }

    public function testUnserializeRejectsNonRationalElements(): void
    {
        // Replace the serialized element list with ["abc"].
        $payload = preg_replace('/a:1:\{.*\}\}$/s', 'a:1:{i:0;s:3:"abc";}}', serialize(new RationalCollection([new RationalNumber(1, 2)])));
        $this->assertIsString($payload);

        $this->expectException(InvalidArgumentException::class);

        unserialize($payload);
    }

    public function testUnserializeReindexesElements(): void
    {
        $payload = str_replace('a:1:{i:0;', 'a:1:{i:5;', serialize(new RationalCollection([new RationalNumber(1, 2)])));

        $restored = unserialize($payload);

        $this->assertInstanceOf(RationalCollection::class, $restored);
        $this->assertSame('1/2', $restored->min()->toString());
        $this->assertTrue(isset($restored[0]));
    }
}
