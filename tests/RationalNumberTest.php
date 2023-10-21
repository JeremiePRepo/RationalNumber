<?php

include_once dirname(__FILE__)."/../vendor/autoload.php";

use PHPUnit\Framework\TestCase;

require 'RationalNumber.php';

class RationalNumberTest extends TestCase {
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
}
