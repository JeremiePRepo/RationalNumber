<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\TestCase;
use RationalNumber\Calculator\PercentageCalculator;
use RationalNumber\RationalNumber;

class PercentageCalculatorTest extends TestCase
{
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PercentageCalculator();
    }

    public function testToPercentage() {
        $number = new RationalNumber(1, 2);
        $result = $this->calculator->toPercentage($number, 2);
        $this->assertEquals("50.00%", $result);
    }

    public function testFromPercentage() {
        $number = $this->calculator->fromPercentage("75%");
        $this->assertEquals("3/4", $number->toString());
        $this->assertEquals(0.75, $number->getFloat());
    }

    public function testIncreaseBy() {
        $number = new RationalNumber(100);
        $result = $this->calculator->increaseBy($number, "10%");
        $this->assertEquals("110/1", $result->toString());
        $this->assertEquals(110, $result->getFloat());
    }

    public function testDecreaseBy() {
        $number = new RationalNumber(200);
        $result = $this->calculator->decreaseBy($number, "25%");
        $this->assertEquals("150/1", $result->toString());
        $this->assertEquals(150, $result->getFloat());
    }

    public function testPercentageOf() {
        $part = new RationalNumber(3, 4);
        $whole = new RationalNumber(1, 1);
        $result = $this->calculator->percentageOf($part, $whole, 2);
        $this->assertEquals("75.00%", $result);
    }

    public function testPercentageOfThrowsExceptionOnZero() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot calculate percentage of zero.");
        
        $part = new RationalNumber(1, 2);
        $zero = RationalNumber::zero();
        $this->calculator->percentageOf($part, $zero);
    }

    public function testIncreaseByWithoutPercentSign() {
        $number = new RationalNumber(50);
        $result = $this->calculator->increaseBy($number, "20");
        $this->assertEquals("60/1", $result->toString());
    }

    public function testDecreaseByWithoutPercentSign() {
        $number = new RationalNumber(100);
        $result = $this->calculator->decreaseBy($number, "10");
        $this->assertEquals("90/1", $result->toString());
    }
}
