<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\TestCase;
use RationalNumber\RationalNumber;

class RationalNumberTest extends TestCase
{
    public function testAddition() {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->add($number2);
        $this->assertEquals("5/4", $result->toString());
        $this->assertEqualsWithDelta(1.25, $result->getFloat(), 1e-12);
    }

    public function testMultiplication() {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->multiply($number2);
        $this->assertEquals("3/8", $result->toString());
        $this->assertEqualsWithDelta(0.375, $result->getFloat(), 1e-12);
    }

    public function testSubtraction() {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->subtract($number2);
        $this->assertEquals("1/4", $result->toString());
        $this->assertEqualsWithDelta(0.25, $result->getFloat(), 1e-12);
    }

    public function testReciprocal() {
        $number1 = new RationalNumber(3, 4);
        $reciprocal = $number1->reciprocal();
        $this->assertEquals("4/3", $reciprocal->toString());
        $this->assertEqualsWithDelta(1.3333333333333333, $reciprocal->getFloat(), 1e-12);
    }

    public function testDivision() {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->divideBy($number2);
        $this->assertEquals("3/2", $result->toString());
        $this->assertEqualsWithDelta(1.5, $result->getFloat(), 1e-12);
    }

    public function testDivisionFrom() {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->divideFrom($number2);
        $this->assertEquals("2/3", $result->toString());
        $this->assertEqualsWithDelta(0.6666666666666666, $result->getFloat(), 1e-12);
    }

    public function testFromFloat() {
        $value = 2.5;
        $number = RationalNumber::fromFloat($value);
        $this->assertEquals("5/2", $number->toString());
        $this->assertEqualsWithDelta(2.5, $number->getFloat(), 1e-12);
    }

    public function testFromInt() {
        $value = 5;
        $number = RationalNumber::fromFloat($value);
        $this->assertEquals("5/1", $number->toString());
        $this->assertEqualsWithDelta(5, $number->getFloat(), 1e-12);
    }

    public function testFromNegativeFloat() {
        $value = -3.75;
        $number = RationalNumber::fromFloat($value);
        $this->assertEquals("-15/4", $number->toString());
        $this->assertEqualsWithDelta(-3.75, $number->getFloat(), 1e-12);
    }

    public function testFromNegativeInt() {
        $value = -8;
        $number = RationalNumber::fromFloat($value);
        $this->assertEquals("-8/1", $number->toString());
        $this->assertEqualsWithDelta(-8, $number->getFloat(), 1e-12);
    }

    public function testToPercentage() {
        $number = new RationalNumber(1, 2);
        $percentage = $number->toPercentage(2);
        $this->assertEquals("50.00%", $percentage);
    }

    public function testFromPercentage() {
        $percentageValue = "75%";
        $number = RationalNumber::fromPercentage($percentageValue);
        $this->assertEquals("3/4", $number->toString());
        $this->assertEqualsWithDelta(0.75, $number->getFloat(), 1e-12);
    }

    public function testIncreaseByPercentage() {
        $number = new RationalNumber(100);
        $increasePercentage = "10%";
        $result = $number->increaseByPercentage($increasePercentage);
        $this->assertEquals("110/1", $result->toString());
        $this->assertEquals(110, $result->getFloat());
    }

    public function testDecreaseByPercentage() {
        $number = new RationalNumber(200);
        $decreasePercentage = "25%";
        $result = $number->decreaseByPercentage($decreasePercentage);
        $this->assertEquals("150/1", $result->toString());
        $this->assertEquals(150, $result->getFloat());
    }

    public function testZeroDenominatorThrowsException() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Denominator cannot be zero.");
        new RationalNumber(1, 0);
    }

    public function testReciprocalOfZeroThrowsException() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot get reciprocal of zero.");
        $zero = new RationalNumber(0, 1);
        $zero->reciprocal();
    }

    public function testDivideByZeroThrowsException() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot divide by zero.");
        $number = new RationalNumber(5, 1);
        $zero = new RationalNumber(0, 1);
        $number->divideBy($zero);
    }

    public function testDivideFromZeroThrowsException() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot divide by zero.");
        $zero = new RationalNumber(0, 1);
        $number = new RationalNumber(5, 1);
        $zero->divideFrom($number);
    }

    public function testNegativeDenominatorIsNormalized() {
        $number = new RationalNumber(3, -4);
        $this->assertEquals("-3/4", $number->toString());
        $this->assertEqualsWithDelta(-0.75, $number->getFloat(), 1e-12);
    }

    public function testNegativeNumbersAreNormalized() {
        $number = new RationalNumber(-6, -8);
        $this->assertEquals("3/4", $number->toString());
        $this->assertEqualsWithDelta(0.75, $number->getFloat(), 1e-12);
    }

    public function testIsZero() {
        $zero = new RationalNumber(0, 5);
        $notZero = new RationalNumber(1, 5);
        $this->assertTrue($zero->isZero());
        $this->assertFalse($notZero->isZero());
    }

    public function testIsInteger() {
        $integer = new RationalNumber(5, 1);
        $notInteger = new RationalNumber(5, 2);
        $this->assertTrue($integer->isInteger());
        $this->assertFalse($notInteger->isInteger());
    }

    public function testReduce() {
        $number = new RationalNumber(6, 8);
        // Note: already normalized in constructor, but reduce() returns new instance
        $this->assertEquals("3/4", $number->toString());
        $reduced = $number->reduce();
        $this->assertEquals("3/4", $reduced->toString());
    }

    public function testMagicToString() {
        $number = new RationalNumber(3, 4);
        $this->assertEquals("3/4", (string)$number);
    }

    // Tests for comparison methods (Comparable interface)
    
    public function testEquals() {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(6, 8); // Equivalent to 3/4 after normalization
        $number3 = new RationalNumber(1, 2);
        
        $this->assertTrue($number1->equals($number2));
        $this->assertFalse($number1->equals($number3));
    }

    public function testCompareTo() {
        $smaller = new RationalNumber(1, 4);
        $larger = new RationalNumber(3, 4);
        $equal = new RationalNumber(3, 4);
        
        $this->assertEquals(-1, $smaller->compareTo($larger));
        $this->assertEquals(1, $larger->compareTo($smaller));
        $this->assertEquals(0, $larger->compareTo($equal));
    }

    public function testIsGreaterThan() {
        $smaller = new RationalNumber(1, 4);
        $larger = new RationalNumber(3, 4);
        
        $this->assertTrue($larger->isGreaterThan($smaller));
        $this->assertFalse($smaller->isGreaterThan($larger));
        $this->assertFalse($larger->isGreaterThan($larger));
    }

    public function testIsLessThan() {
        $smaller = new RationalNumber(1, 4);
        $larger = new RationalNumber(3, 4);
        
        $this->assertTrue($smaller->isLessThan($larger));
        $this->assertFalse($larger->isLessThan($smaller));
        $this->assertFalse($smaller->isLessThan($smaller));
    }

    public function testIsGreaterThanOrEqual() {
        $smaller = new RationalNumber(1, 4);
        $larger = new RationalNumber(3, 4);
        $equal = new RationalNumber(3, 4);
        
        $this->assertTrue($larger->isGreaterThanOrEqual($smaller));
        $this->assertTrue($larger->isGreaterThanOrEqual($equal));
        $this->assertFalse($smaller->isGreaterThanOrEqual($larger));
    }

    public function testIsLessThanOrEqual() {
        $smaller = new RationalNumber(1, 4);
        $larger = new RationalNumber(3, 4);
        $equal = new RationalNumber(1, 4);
        
        $this->assertTrue($smaller->isLessThanOrEqual($larger));
        $this->assertTrue($smaller->isLessThanOrEqual($equal));
        $this->assertFalse($larger->isLessThanOrEqual($smaller));
    }

    // Tests for abs() and negate()
    
    public function testAbs() {
        $positive = new RationalNumber(3, 4);
        $negative = new RationalNumber(-3, 4);
        
        $this->assertEquals("3/4", $positive->abs()->toString());
        $this->assertEquals("3/4", $negative->abs()->toString());
    }

    public function testNegate() {
        $positive = new RationalNumber(3, 4);
        $negative = new RationalNumber(-3, 4);
        
        $this->assertEquals("-3/4", $positive->negate()->toString());
        $this->assertEquals("3/4", $negative->negate()->toString());
    }

    public function testNegateZero() {
        $zero = new RationalNumber(0, 1);
        $this->assertEquals("0/1", $zero->negate()->toString());
        $this->assertTrue($zero->negate()->isZero());
    }

    // Tests for factory methods
    
    public function testZeroFactory() {
        $zero = RationalNumber::zero();
        $this->assertTrue($zero->isZero());
        $this->assertEquals("0/1", $zero->toString());
        $this->assertEqualsWithDelta(0, $zero->getFloat(), 1e-12);
    }

    public function testOneFactory() {
        $one = RationalNumber::one();
        $this->assertTrue($one->isInteger());
        $this->assertEquals("1/1", $one->toString());
        $this->assertEqualsWithDelta(1, $one->getFloat(), 1e-12);
    }
}
