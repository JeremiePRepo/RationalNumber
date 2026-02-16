<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\TestCase;
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
        $result = $number->sqrt();
        
        $this->assertEqualsWithDelta(2.0, $result->getFloat(), 0.001);
    }

    public function testSqrtOfFraction(): void
    {
        $number = new RationalNumber(1, 4);  // 0.25
        $result = $number->sqrt();
        
        $this->assertEqualsWithDelta(0.5, $result->getFloat(), 0.001);
    }

    public function testSqrtApproximation(): void
    {
        $number = RationalNumber::fromFloat(2);
        $result = $number->sqrt(10);  // Precision parameter (iterations)
        
        $this->assertEqualsWithDelta(1.41421356, $result->getFloat(), 0.0001);
    }

    public function testSqrtOfZero(): void
    {
        $number = RationalNumber::zero();
        $result = $number->sqrt();
        
        $this->assertTrue($result->isZero());
    }

    public function testSqrtOfNegativeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot calculate square root of a negative number");
        
        $number = RationalNumber::fromFloat(-4);
        $number->sqrt();
    }

    public function testSqrtImmutability(): void
    {
        $original = RationalNumber::fromFloat(9);
        $result = $original->sqrt();
        
        $this->assertEquals("9/1", $original->toString());
        $this->assertNotEquals($original->toString(), $result->toString());
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
        
        $minPrice = array_reduce($prices, fn($min, $p) => $min->min($p), $prices[0]);
        $maxPrice = array_reduce($prices, fn($max, $p) => $max->max($p), $prices[0]);
        
        $this->assertEqualsWithDelta(8.75, $minPrice->getFloat(), 1e-12);
        $this->assertEqualsWithDelta(12.00, $maxPrice->getFloat(), 1e-12);
    }

    public function testPowAndSqrtInverse(): void
    {
        $number = RationalNumber::fromFloat(16);
        $squared = $number->pow(2);
        $root = $squared->sqrt(15);
        
        // sqrt(16^2) should be close to 16
        $this->assertEqualsWithDelta(16.0, $root->getFloat(), 0.01);
    }
}
