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
        $this->assertEquals(1.25, $result->getFloat());
    }

    public function testMultiplication() {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->multiply($number2);
        $this->assertEquals("3/8", $result->toString());
        $this->assertEquals(0.375, $result->getFloat());
    }

    public function testSubtraction() {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->subtract($number2);
        $this->assertEquals("1/4", $result->toString());
        $this->assertEquals(0.25, $result->getFloat());
    }

    public function testReciprocal() {
        $number1 = new RationalNumber(3, 4);
        $reciprocal = $number1->reciprocal();
        $this->assertEquals("4/3", $reciprocal->toString());
        $this->assertEquals(1.3333333333333333, $reciprocal->getFloat());
    }

    public function testDivision() {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->divideBy($number2);
        $this->assertEquals("3/2", $result->toString());
        $this->assertEquals(1.5, $result->getFloat());
    }

    public function testDivisionFrom() {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->divideFrom($number2);
        $this->assertEquals("2/3", $result->toString());
        $this->assertEquals(0.6666666666666666, $result->getFloat());
    }

    public function testFromFloat() {
        $value = 2.5;
        $number = RationalNumber::fromFloat($value);
        $this->assertEquals("5/2", $number->toString());
        $this->assertEquals(2.5, $number->getFloat());
    }

    public function testFromInt() {
        $value = 5;
        $number = RationalNumber::fromFloat($value);
        $this->assertEquals("5/1", $number->toString());
        $this->assertEquals(5, $number->getFloat());
    }

    public function testFromNegativeFloat() {
        $value = -3.75;
        $number = RationalNumber::fromFloat($value);
        $this->assertEquals("-15/4", $number->toString());
        $this->assertEquals(-3.75, $number->getFloat());
    }

    public function testFromNegativeInt() {
        $value = -8;
        $number = RationalNumber::fromFloat($value);
        $this->assertEquals("-8/1", $number->toString());
        $this->assertEquals(-8, $number->getFloat());
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
        $this->assertEquals(0.75, $number->getFloat());
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
        $this->assertEquals(-0.75, $number->getFloat());
    }

    public function testNegativeNumbersAreNormalized() {
        $number = new RationalNumber(-6, -8);
        $this->assertEquals("3/4", $number->toString());
        $this->assertEquals(0.75, $number->getFloat());
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
}
