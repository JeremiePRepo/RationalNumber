<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use RationalNumber\Calculator\PercentageCalculator;
use RationalNumber\RationalNumber;

/**
 * PercentageCalculator is deprecated: every method triggers E_USER_DEPRECATED.
 */
#[IgnoreDeprecations]
class PercentageCalculatorTest extends TestCase
{
    private PercentageCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PercentageCalculator();
    }

    public function testToPercentage(): void
    {
        $number = new RationalNumber(1, 2);
        $result = $this->calculator->toPercentage($number, 2);
        $this->assertEquals("50.00%", $result);
    }

    public function testFromPercentage(): void
    {
        $number = $this->calculator->fromPercentage("75%");
        $this->assertEquals("3/4", $number->toString());
        $this->assertEqualsWithDelta(0.75, $number->getFloat(), 1e-12);
    }

    public function testIncreaseBy(): void
    {
        $number = new RationalNumber(100);
        $result = $this->calculator->increaseBy($number, "10%");
        $this->assertEquals("110/1", $result->toString());
        $this->assertEquals(110, $result->getFloat());
    }

    public function testDecreaseBy(): void
    {
        $number = new RationalNumber(200);
        $result = $this->calculator->decreaseBy($number, "25%");
        $this->assertEquals("150/1", $result->toString());
        $this->assertEquals(150, $result->getFloat());
    }

    public function testPercentageOf(): void
    {
        $part = new RationalNumber(3, 4);
        $whole = new RationalNumber(1, 1);
        $result = $this->calculator->percentageOf($part, $whole, 2);
        $this->assertEquals("75.00%", $result);
    }

    public function testPercentageOfThrowsExceptionOnZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot calculate percentage of zero.");

        $part = new RationalNumber(1, 2);
        $zero = RationalNumber::zero();
        $this->calculator->percentageOf($part, $zero);
    }

    public function testIncreaseByWithoutPercentSign(): void
    {
        $number = new RationalNumber(50);
        $result = $this->calculator->increaseBy($number, "20");
        $this->assertEquals("60/1", $result->toString());
    }

    public function testDecreaseByWithoutPercentSign(): void
    {
        $number = new RationalNumber(100);
        $result = $this->calculator->decreaseBy($number, "10");
        $this->assertEquals("90/1", $result->toString());
    }

    public function testToPercentageTriggersDeprecationWarning(): void
    {
        $this->expectUserDeprecationMessageMatches('/PercentageCalculator::toPercentage\(\) is deprecated/');

        $this->calculator->toPercentage(new RationalNumber(1, 2), 2);
    }

    public function testFromPercentageTriggersDeprecationWarning(): void
    {
        $this->expectUserDeprecationMessageMatches('/PercentageCalculator::fromPercentage\(\) is deprecated/');

        $this->calculator->fromPercentage("50%");
    }

    public function testIncreaseByTriggersDeprecationWarning(): void
    {
        $this->expectUserDeprecationMessageMatches('/PercentageCalculator::increaseBy\(\) is deprecated/');

        $this->calculator->increaseBy(new RationalNumber(100), "10%");
    }

    public function testDecreaseByTriggersDeprecationWarning(): void
    {
        $this->expectUserDeprecationMessageMatches('/PercentageCalculator::decreaseBy\(\) is deprecated/');

        $this->calculator->decreaseBy(new RationalNumber(100), "25%");
    }

    public function testPercentageOfTriggersDeprecationWarning(): void
    {
        $this->expectUserDeprecationMessageMatches('/PercentageCalculator::percentageOf\(\) is deprecated/');

        $this->calculator->percentageOf(new RationalNumber(1, 2), new RationalNumber(1, 1));
    }
}
