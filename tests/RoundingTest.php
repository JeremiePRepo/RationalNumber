<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RationalNumber\Exception\IntegerOverflowException;
use RationalNumber\RationalNumber;
use InvalidArgumentException;

/**
 * Test suite for rounding operations.
 *
 * Tests round(), floor(), and ceil() methods.
 */
class RoundingTest extends TestCase
{
    // ========== round() Tests ==========

    public function testRoundToInteger(): void
    {
        $number = RationalNumber::fromFloat(12.6);
        $result = $number->round();

        $this->assertEquals("13/1", $result->toString());
        $this->assertEqualsWithDelta(13.0, $result->getFloat(), 1e-12);
    }

    public function testRoundToIntegerDown(): void
    {
        $number = RationalNumber::fromFloat(12.4);
        $result = $number->round();

        $this->assertEquals("12/1", $result->toString());
        $this->assertEqualsWithDelta(12.0, $result->getFloat(), 1e-12);
    }

    public function testRoundToHalfUp(): void
    {
        $number = RationalNumber::fromFloat(12.5);
        $result = $number->round();

        // PHP rounds half up
        $this->assertEquals("13/1", $result->toString());
        $this->assertEqualsWithDelta(13.0, $result->getFloat(), 1e-12);
    }

    public function testRoundToCents(): void
    {
        $price = RationalNumber::fromFloat(12.3456);
        $rounded = $price->round(100);

        // Note: Result is automatically normalized to simplest form (247/20 = 1235/100)
        $this->assertEquals("247/20", $rounded->toString());
        $this->assertEqualsWithDelta(12.35, $rounded->getFloat(), 1e-12);
    }

    public function testRoundToTenths(): void
    {
        $number = RationalNumber::fromFloat(7.846);
        $rounded = $number->round(10);

        // Note: Result is automatically normalized to simplest form (39/5 = 78/10)
        $this->assertEquals("39/5", $rounded->toString());
        $this->assertEqualsWithDelta(7.8, $rounded->getFloat(), 1e-12);
    }

    public function testRoundNegativeNumber(): void
    {
        $number = RationalNumber::fromFloat(-5.6);
        $result = $number->round();

        $this->assertEquals("-6/1", $result->toString());
        $this->assertEqualsWithDelta(-6.0, $result->getFloat(), 1e-12);
    }

    public function testRoundWithInvalidDenominator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Denominator must be greater than zero");

        $number = RationalNumber::fromFloat(5.5);
        $number->round(0);
    }

    public function testRoundImmutability(): void
    {
        $original = RationalNumber::fromFloat(7.8);
        $rounded = $original->round();

        $this->assertEquals("39/5", $original->toString());
        $this->assertEquals("8/1", $rounded->toString());
    }

    // ========== floor() Tests ==========

    public function testFloorPositiveNumber(): void
    {
        $number = RationalNumber::fromFloat(5.9);
        $result = $number->floor();

        $this->assertEquals("5/1", $result->toString());
        $this->assertEqualsWithDelta(5.0, $result->getFloat(), 1e-12);
    }

    public function testFloorNegativeNumber(): void
    {
        $number = RationalNumber::fromFloat(-3.2);
        $result = $number->floor();

        $this->assertEquals("-4/1", $result->toString());
        $this->assertEqualsWithDelta(-4.0, $result->getFloat(), 1e-12);
    }

    public function testFloorInteger(): void
    {
        $number = RationalNumber::fromFloat(7);
        $result = $number->floor();

        $this->assertEquals("7/1", $result->toString());
        $this->assertTrue($result->equals($number));
    }

    public function testFloorZero(): void
    {
        $number = RationalNumber::zero();
        $result = $number->floor();

        $this->assertTrue($result->isZero());
    }

    public function testFloorFraction(): void
    {
        $number = new RationalNumber(7, 3);  // 2.333...
        $result = $number->floor();

        $this->assertEquals("2/1", $result->toString());
        $this->assertEqualsWithDelta(2.0, $result->getFloat(), 1e-12);
    }

    public function testFloorImmutability(): void
    {
        $original = RationalNumber::fromFloat(9.7);
        $result = $original->floor();

        $this->assertEquals("97/10", $original->toString());
        $this->assertEquals("9/1", $result->toString());
    }

    // ========== ceil() Tests ==========

    public function testCeilPositiveNumber(): void
    {
        $number = RationalNumber::fromFloat(5.1);
        $result = $number->ceil();

        $this->assertEquals("6/1", $result->toString());
        $this->assertEqualsWithDelta(6.0, $result->getFloat(), 1e-12);
    }

    public function testCeilNegativeNumber(): void
    {
        $number = RationalNumber::fromFloat(-3.8);
        $result = $number->ceil();

        $this->assertEquals("-3/1", $result->toString());
        $this->assertEqualsWithDelta(-3.0, $result->getFloat(), 1e-12);
    }

    public function testCeilInteger(): void
    {
        $number = RationalNumber::fromFloat(4);
        $result = $number->ceil();

        $this->assertEquals("4/1", $result->toString());
        $this->assertTrue($result->equals($number));
    }

    public function testCeilZero(): void
    {
        $number = RationalNumber::zero();
        $result = $number->ceil();

        $this->assertTrue($result->isZero());
    }

    public function testCeilFraction(): void
    {
        $number = new RationalNumber(5, 3);  // 1.666...
        $result = $number->ceil();

        $this->assertEquals("2/1", $result->toString());
        $this->assertEqualsWithDelta(2.0, $result->getFloat(), 1e-12);
    }

    public function testCeilImmutability(): void
    {
        $original = RationalNumber::fromFloat(2.3);
        $result = $original->ceil();

        $this->assertEquals("23/10", $original->toString());
        $this->assertEquals("3/1", $result->toString());
    }

    // ========== Integration Tests ==========

    public function testPriceRoundingScenario(): void
    {
        // E-commerce scenario: price calculation
        $basePrice = RationalNumber::fromFloat(19.99);
        $tax = $basePrice->multiply(RationalNumber::fromFloat(0.20));  // 20% tax
        $total = $basePrice->add($tax);

        // Round to cents
        $finalPrice = $total->round(100);

        // 19.99 * 1.20 = 23.988, rounds to 23.99
        $this->assertEqualsWithDelta(23.99, $finalPrice->getFloat(), 0.01);
    }

    public function testUnitCalculationWithCeil(): void
    {
        // Calculate units needed: 100 items, 12 per unit
        $totalItems = RationalNumber::fromFloat(100);
        $itemsPerUnit = RationalNumber::fromFloat(12);
        $unitsNeeded = $totalItems->divideBy($itemsPerUnit)->ceil();

        // 100/12 = 8.333..., ceil = 9
        $this->assertEquals("9/1", $unitsNeeded->toString());
        $this->assertEqualsWithDelta(9.0, $unitsNeeded->getFloat(), 1e-12);
    }

    public function testFloorForDiscountCalculation(): void
    {
        // Floor for whole dollar discount
        $purchaseAmount = RationalNumber::fromFloat(127.50);
        $wholeDollars = $purchaseAmount->floor();

        $this->assertEquals("127/1", $wholeDollars->toString());
        $this->assertEqualsWithDelta(127.0, $wholeDollars->getFloat(), 1e-12);
    }

    public function testRoundingVerySmallNumbers(): void
    {
        $tiny = RationalNumber::fromFloat(0.004);
        $rounded = $tiny->round(100);  // Round to cents

        // 0.004 rounds to 0.00
        $this->assertTrue($rounded->isZero() || $rounded->getFloat() < 0.01);
    }

    // ========== Exact results beyond 2^53 ==========

    /**
     * @return iterable<string, array{RationalNumber, string, string, string}>
     */
    public static function largeValueProvider(): iterable
    {
        // [value, floor, ceil, round] — a float conversion rounds all of these to 2^62.
        yield 'MAX / 2' => [new RationalNumber(PHP_INT_MAX, 2), '4611686018427387903', '4611686018427387904', '4611686018427387904'];
        yield '-MAX / 2' => [new RationalNumber(-PHP_INT_MAX, 2), '-4611686018427387904', '-4611686018427387903', '-4611686018427387904'];
        yield 'MAX / 3' => [new RationalNumber(PHP_INT_MAX, 3), '3074457345618258602', '3074457345618258603', '3074457345618258602'];
        yield 'just below an integer' => [new RationalNumber(PHP_INT_MAX - 1, PHP_INT_MAX), '0', '1', '1'];
    }

    #[DataProvider('largeValueProvider')]
    public function testFloorCeilRoundAreExactForLargeValues(RationalNumber $value, string $floor, string $ceil, string $round): void
    {
        $this->assertSame($floor . '/1', $value->floor()->toString());
        $this->assertSame($ceil . '/1', $value->ceil()->toString());
        $this->assertSame($round . '/1', $value->round()->toString());
    }

    /**
     * @return iterable<string, array{RationalNumber, int, string}>
     */
    public static function tieProvider(): iterable
    {
        yield '2.5' => [new RationalNumber(5, 2), 1, '3/1'];
        yield '-2.5' => [new RationalNumber(-5, 2), 1, '-3/1'];
        yield '0.125 to tenths' => [new RationalNumber(1, 8), 100, '13/100'];
        yield '-0.125 to hundredths' => [new RationalNumber(-1, 8), 100, '-13/100'];
        yield '1.005 is exact (no float drift)' => [new RationalNumber(201, 200), 100, '101/100'];
    }

    #[DataProvider('tieProvider')]
    public function testRoundHalvesAwayFromZero(RationalNumber $value, int $denominator, string $expected): void
    {
        $this->assertSame($expected, $value->round($denominator)->toString());
    }

    public function testRoundWithLargeDenominatorIsExact(): void
    {
        // 1/3 to the nearest 1/(10^18): the 18 digits are exact.
        $rounded = (new RationalNumber(1, 3))->round(1000000000000000000);

        $this->assertSame('333333333333333333/1000000000000000000', $rounded->toString());
    }

    public function testRoundThrowsOverflowWhenTheResultDoesNotFit(): void
    {
        $this->expectException(IntegerOverflowException::class);

        (new RationalNumber(PHP_INT_MAX, 2))->round(4);
    }

    // ========== toPercentage() ==========

    /**
     * @return iterable<string, array{RationalNumber, int, string}>
     */
    public static function percentageProvider(): iterable
    {
        yield 'one third' => [new RationalNumber(1, 3), 2, '33.33%'];
        yield 'two thirds' => [new RationalNumber(2, 3), 2, '66.67%'];
        yield 'tie away from zero' => [new RationalNumber(-1, 800), 2, '-0.13%'];
        yield 'rounds to zero, no minus sign' => [new RationalNumber(-1, 100000), 2, '0.00%'];
        yield 'no decimals' => [new RationalNumber(1, 2), 0, '50%'];
        yield 'thousands separators' => [new RationalNumber(1234567, 100), 1, '1,234,567.0%'];
        yield 'beyond float precision' => [new RationalNumber(123456789012345678, 1000), 2, '12,345,678,901,234,567.80%'];
        yield 'small value, many decimals' => [new RationalNumber(1, 7), 10, '14.2857142857%'];
    }

    #[DataProvider('percentageProvider')]
    public function testToPercentageIsExact(RationalNumber $value, int $decimals, string $expected): void
    {
        $this->assertSame($expected, $value->toPercentage($decimals));
    }

    public function testToPercentageRejectsInvalidDecimals(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Decimal places must be between 0 and 16.');

        (new RationalNumber(1, 2))->toPercentage(17);
    }
}
