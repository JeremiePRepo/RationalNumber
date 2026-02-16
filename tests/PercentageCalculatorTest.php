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
        $this->assertEqualsWithDelta(0.75, $number->getFloat(), 1e-12);
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

    /**
     * Test that toPercentage triggers deprecation warning
     */
    public function testToPercentageTriggersDeprecationWarning(): void
    {
        $deprecationTriggered = false;
        $originalHandler = set_error_handler(function($errno, $errstr) use (&$deprecationTriggered) {
            if ($errno === E_USER_DEPRECATED && strpos($errstr, 'toPercentage') !== false) {
                $deprecationTriggered = true;
            }
            return true;
        });

        $number = new RationalNumber(1, 2);
        $this->calculator->toPercentage($number, 2);

        if ($originalHandler !== null) {
            set_error_handler($originalHandler);
        } else {
            restore_error_handler();
        }

        $this->assertTrue($deprecationTriggered, 'Deprecation warning should be triggered for toPercentage()');
    }

    /**
     * Test that fromPercentage triggers deprecation warning
     */
    public function testFromPercentageTriggersDeprecationWarning(): void
    {
        $deprecationTriggered = false;
        $originalHandler = set_error_handler(function($errno, $errstr) use (&$deprecationTriggered) {
            if ($errno === E_USER_DEPRECATED && strpos($errstr, 'fromPercentage') !== false) {
                $deprecationTriggered = true;
            }
            return true;
        });

        $this->calculator->fromPercentage("50%");

        if ($originalHandler !== null) {
            set_error_handler($originalHandler);
        } else {
            restore_error_handler();
        }

        $this->assertTrue($deprecationTriggered, 'Deprecation warning should be triggered for fromPercentage()');
    }

    /**
     * Test that increaseBy triggers deprecation warning
     */
    public function testIncreaseByTriggersDeprecationWarning(): void
    {
        $deprecationTriggered = false;
        $originalHandler = set_error_handler(function($errno, $errstr) use (&$deprecationTriggered) {
            if ($errno === E_USER_DEPRECATED && strpos($errstr, 'increaseBy') !== false) {
                $deprecationTriggered = true;
            }
            return true;
        });

        $number = new RationalNumber(100);
        $this->calculator->increaseBy($number, "10%");

        if ($originalHandler !== null) {
            set_error_handler($originalHandler);
        } else {
            restore_error_handler();
        }

        $this->assertTrue($deprecationTriggered, 'Deprecation warning should be triggered for increaseBy()');
    }

    /**
     * Test that decreaseBy triggers deprecation warning
     */
    public function testDecreaseByTriggersDeprecationWarning(): void
    {
        $deprecationTriggered = false;
        $originalHandler = set_error_handler(function($errno, $errstr) use (&$deprecationTriggered) {
            if ($errno === E_USER_DEPRECATED && strpos($errstr, 'decreaseBy') !== false) {
                $deprecationTriggered = true;
            }
            return true;
        });

        $number = new RationalNumber(100);
        $this->calculator->decreaseBy($number, "25%");

        if ($originalHandler !== null) {
            set_error_handler($originalHandler);
        } else {
            restore_error_handler();
        }

        $this->assertTrue($deprecationTriggered, 'Deprecation warning should be triggered for decreaseBy()');
    }

    /**
     * Test that percentageOf triggers deprecation warning
     */
    public function testPercentageOfTriggersDeprecationWarning(): void
    {
        $deprecationTriggered = false;
        $originalHandler = set_error_handler(function($errno, $errstr) use (&$deprecationTriggered) {
            if ($errno === E_USER_DEPRECATED && strpos($errstr, 'percentageOf') !== false) {
                $deprecationTriggered = true;
            }
            return true;
        });

        $part = new RationalNumber(1, 2);
        $whole = new RationalNumber(1, 1);
        $this->calculator->percentageOf($part, $whole);

        if ($originalHandler !== null) {
            set_error_handler($originalHandler);
        } else {
            restore_error_handler();
        }

        $this->assertTrue($deprecationTriggered, 'Deprecation warning should be triggered for percentageOf()');
    }
}
