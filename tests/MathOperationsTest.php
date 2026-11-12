<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RationalNumber\Exception\IntegerOverflowException;
use RationalNumber\RationalNumber;
use InvalidArgumentException;

/**
 * Test suite for advanced mathematical operations.
 *
 * Tests pow(), sqrt(), min(), and max() methods.
 */
class MathOperationsTest extends TestCase
{
    // ========== pow() Tests ==========

    public function testPowWithZeroExponent(): void
    {
        $number = RationalNumber::fromFloat(5);
        $result = $number->pow(0);

        $this->assertEquals("1/1", $result->toString());
        $this->assertEqualsWithDelta(1.0, $result->getFloat(), 1e-12);
    }

    public function testPowWithPositiveExponent(): void
    {
        $number = RationalNumber::fromFloat(2);
        $result = $number->pow(3);

        $this->assertEquals("8/1", $result->toString());
        $this->assertEqualsWithDelta(8.0, $result->getFloat(), 1e-12);
    }

    public function testPowWithFraction(): void
    {
        $number = new RationalNumber(3, 4);  // 0.75
        $result = $number->pow(2);

        $this->assertEquals("9/16", $result->toString());
        $this->assertEqualsWithDelta(0.5625, $result->getFloat(), 1e-12);
    }

    public function testPowWithNegativeExponent(): void
    {
        $number = RationalNumber::fromFloat(2);
        $result = $number->pow(-2);

        $this->assertEquals("1/4", $result->toString());
        $this->assertEqualsWithDelta(0.25, $result->getFloat(), 1e-12);
    }

    public function testPowWithNegativeExponentAndFraction(): void
    {
        $number = new RationalNumber(2, 3);
        $result = $number->pow(-1);

        $this->assertEquals("3/2", $result->toString());
        $this->assertEqualsWithDelta(1.5, $result->getFloat(), 1e-12);
    }

    public function testPowImmutability(): void
    {
        $original = RationalNumber::fromFloat(3);
        $result = $original->pow(2);

        $this->assertEquals("3/1", $original->toString());
        $this->assertEquals("9/1", $result->toString());
    }

    public function testPowCompoundInterest(): void
    {
        // Compound interest example: 5% rate over 10 years
        $principal = RationalNumber::fromFloat(1000);
        $rate = RationalNumber::fromPercentage('5%')->add(RationalNumber::one());  // 1.05
        $years = 10;

        $final = $principal->multiply($rate->pow($years));

        // 1000 * 1.05^10 ≈ 1628.89
        $this->assertEqualsWithDelta(1628.89, $final->getFloat(), 0.10);
    }

    // ========== sqrt() Tests ==========

    public function testSqrtOfPerfectSquare(): void
    {
        $number = RationalNumber::fromFloat(4);
        $result = $number->sqrt(6);

        $this->assertSame('2/1', $result->toString());
    }

    public function testSqrtOfFraction(): void
    {
        $number = new RationalNumber(1, 4);  // 0.25
        $result = $number->sqrt(6);

        $this->assertSame('1/2', $result->toString());
    }

    public function testSqrtApproximation(): void
    {
        $number = RationalNumber::fromFloat(2);
        $result = $number->sqrt(6);

        $this->assertSame('707107/500000', $result->toString());  // 1.414214
    }

    public function testSqrtOfZero(): void
    {
        $number = RationalNumber::zero();
        $result = $number->sqrt(6);

        $this->assertTrue($result->isZero());
    }

    public function testSqrtOfNegativeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot calculate square root of a negative number");

        $number = RationalNumber::fromFloat(-4);
        $number->sqrt(6);
    }

    public function testSqrtImmutability(): void
    {
        $original = RationalNumber::fromFloat(9);
        $result = $original->sqrt(6);

        $this->assertEquals("9/1", $original->toString());
        $this->assertNotEquals($original->toString(), $result->toString());
    }

    /**
     * @return iterable<string, array{RationalNumber, int, string}>
     */
    public static function sqrtProvider(): iterable
    {
        yield 'sqrt(2), 0 decimals' => [new RationalNumber(2), 0, '1/1'];
        yield 'sqrt(2), 9 decimals' => [new RationalNumber(2), 9, '707106781/500000000'];
        yield 'sqrt(1/3), 6 decimals' => [new RationalNumber(1, 3), 6, '11547/20000'];
        yield 'exact tie 1.5 rounds up' => [new RationalNumber(9, 4), 0, '2/1'];
        yield 'just below the tie' => [new RationalNumber(56, 25), 0, '1/1'];
        yield 'PHP_INT_MAX, beyond float precision' => [new RationalNumber(PHP_INT_MAX), 0, '3037000500/1'];
        yield 'sqrt(10), 9 decimals' => [new RationalNumber(10), 9, '158113883/50000000'];
        yield 'PHP_INT_MAX, 8 decimals' => [new RationalNumber(PHP_INT_MAX), 8, '303700049997604969/100000000'];
        yield '10^17 / 3, 9 decimals' => [new RationalNumber(10 ** 17, 3), 9, '182574185835055371/1000000000'];
        yield 'tiny value rounds to zero' => [new RationalNumber(1, 1000000000), 2, '0/1'];
    }

    #[DataProvider('sqrtProvider')]
    public function testSqrtIsRoundedToTheNearestDecimal(RationalNumber $value, int $decimals, string $expected): void
    {
        $this->assertSame($expected, $value->sqrt($decimals)->toString());
    }

    public function testSqrtRejectsInvalidDecimals(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Decimal places must be between 0 and 9.');

        (new RationalNumber(2))->sqrt(10);
    }

    public function testSqrtRejectsNegativeDecimals(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RationalNumber(2))->sqrt(-1);
    }

    public function testSqrtThrowsOverflowWhenTheScaledValueDoesNotFit(): void
    {
        $this->expectException(IntegerOverflowException::class);

        (new RationalNumber(PHP_INT_MAX))->sqrt(9);
    }

    // ========== min() Tests ==========

    public function testMinWithSmallerThis(): void
    {
        $a = RationalNumber::fromFloat(3);
        $b = RationalNumber::fromFloat(5);
        $result = $a->min($b);

        $this->assertTrue($result->equals($a));
        $this->assertEqualsWithDelta(3.0, $result->getFloat(), 1e-12);
    }

    public function testMinWithSmallerOther(): void
    {
        $a = RationalNumber::fromFloat(7);
        $b = RationalNumber::fromFloat(2);
        $result = $a->min($b);

        $this->assertTrue($result->equals($b));
        $this->assertEqualsWithDelta(2.0, $result->getFloat(), 1e-12);
    }

    public function testMinWithEqualValues(): void
    {
        $a = RationalNumber::fromFloat(4);
        $b = new RationalNumber(4, 1);
        $result = $a->min($b);

        $this->assertTrue($result->equals($a));
        $this->assertTrue($result->equals($b));
    }

    public function testMinWithNegativeNumbers(): void
    {
        $a = RationalNumber::fromFloat(-5);
        $b = RationalNumber::fromFloat(-2);
        $result = $a->min($b);

        $this->assertEqualsWithDelta(-5.0, $result->getFloat(), 1e-12);
    }

    public function testMinWithFractions(): void
    {
        $a = new RationalNumber(1, 3);  // 0.333...
        $b = new RationalNumber(1, 2);  // 0.5
        $result = $a->min($b);

        $this->assertEquals("1/3", $result->toString());
    }

    // ========== max() Tests ==========

    public function testMaxWithLargerThis(): void
    {
        $a = RationalNumber::fromFloat(7);
        $b = RationalNumber::fromFloat(3);
        $result = $a->max($b);

        $this->assertTrue($result->equals($a));
        $this->assertEqualsWithDelta(7.0, $result->getFloat(), 1e-12);
    }

    public function testMaxWithLargerOther(): void
    {
        $a = RationalNumber::fromFloat(2);
        $b = RationalNumber::fromFloat(9);
        $result = $a->max($b);

        $this->assertTrue($result->equals($b));
        $this->assertEqualsWithDelta(9.0, $result->getFloat(), 1e-12);
    }

    public function testMaxWithEqualValues(): void
    {
        $a = RationalNumber::fromFloat(6);
        $b = new RationalNumber(6, 1);
        $result = $a->max($b);

        $this->assertTrue($result->equals($a));
        $this->assertTrue($result->equals($b));
    }

    public function testMaxWithNegativeNumbers(): void
    {
        $a = RationalNumber::fromFloat(-5);
        $b = RationalNumber::fromFloat(-2);
        $result = $a->max($b);

        $this->assertEqualsWithDelta(-2.0, $result->getFloat(), 1e-12);
    }

    public function testMaxWithFractions(): void
    {
        $a = new RationalNumber(2, 3);  // 0.666...
        $b = new RationalNumber(3, 4);  // 0.75
        $result = $a->max($b);

        $this->assertEquals("3/4", $result->toString());
    }

    // ========== Integration Tests ==========

    public function testMinMaxCombination(): void
    {
        $prices = [
            RationalNumber::fromFloat(10.50),
            RationalNumber::fromFloat(8.75),
            RationalNumber::fromFloat(12.00),
            RationalNumber::fromFloat(9.25)
        ];

        $minPrice = array_reduce($prices, fn ($min, $p) => $min->min($p), $prices[0]);
        $maxPrice = array_reduce($prices, fn ($max, $p) => $max->max($p), $prices[0]);

        $this->assertEqualsWithDelta(8.75, $minPrice->getFloat(), 1e-12);
        $this->assertEqualsWithDelta(12.00, $maxPrice->getFloat(), 1e-12);
    }

    public function testPowAndSqrtInverse(): void
    {
        $number = RationalNumber::fromFloat(16);
        $squared = $number->pow(2);
        $root = $squared->sqrt(0);

        // sqrt(16^2) is exactly 16
        $this->assertSame('16/1', $root->toString());
    }
}
