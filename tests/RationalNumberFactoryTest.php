<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\TestCase;
use RationalNumber\Factory\RationalNumberFactory;
use RationalNumber\RationalNumber;

class RationalNumberFactoryTest extends TestCase
{
    private $factory;

    protected function setUp(): void
    {
        $this->factory = new RationalNumberFactory();
    }

    public function testCreate(): void
    {
        $number = $this->factory->create(3, 4);
        $this->assertInstanceOf(RationalNumber::class, $number);
        $this->assertEquals("3/4", $number->toString());
    }

    public function testCreateWithDefaultDenominator(): void
    {
        $number = $this->factory->create(5);
        $this->assertEquals("5/1", $number->toString());
    }

    public function testFromFloat(): void
    {
        $number = $this->factory->fromFloat(2.5);
        $this->assertEquals("5/2", $number->toString());
    }

    public function testFromPercentage(): void
    {
        $number = $this->factory->fromPercentage("50%");
        $this->assertEquals("1/2", $number->toString());
    }

    public function testFromString(): void
    {
        $number = $this->factory->fromString("3/4");
        $this->assertInstanceOf(RationalNumber::class, $number);
        $this->assertEquals("3/4", $number->toString());
    }

    public function testFromStringWithDecimal(): void
    {
        $number = $this->factory->fromString("0.25");
        $this->assertEquals("1/4", $number->toString());
    }

    public function testFromStringWithInteger(): void
    {
        $number = $this->factory->fromString("5");
        $this->assertEquals("5/1", $number->toString());
    }

    public function testFromStringWithSpaces(): void
    {
        $number = $this->factory->fromString(" 1 / 2 ");
        $this->assertEquals("1/2", $number->toString());
    }

    public function testFromStringThrowsExceptionOnInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid string format");
        $this->factory->fromString("invalid");
    }

    public function testZero(): void
    {
        $zero = $this->factory->zero();
        $this->assertTrue($zero->isZero());
        $this->assertEquals("0/1", $zero->toString());
    }

    public function testOne(): void
    {
        $one = $this->factory->one();
        $this->assertTrue($one->isInteger());
        $this->assertEquals("1/1", $one->toString());
    }

    public function testCreateThrowsExceptionOnZeroDenominator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Denominator cannot be zero.");
        $this->factory->create(1, 0);
    }
}
