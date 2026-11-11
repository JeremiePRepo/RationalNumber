<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use RationalNumber\RationalNumber;

class RationalNumberTest extends TestCase
{
    public function testAddition(): void
    {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->add($number2);
        $this->assertEquals("5/4", $result->toString());
        $this->assertEqualsWithDelta(1.25, $result->getFloat(), 1e-12);
    }

    public function testMultiplication(): void
    {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->multiply($number2);
        $this->assertEquals("3/8", $result->toString());
        $this->assertEqualsWithDelta(0.375, $result->getFloat(), 1e-12);
    }

    public function testSubtraction(): void
    {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->subtract($number2);
        $this->assertEquals("1/4", $result->toString());
        $this->assertEqualsWithDelta(0.25, $result->getFloat(), 1e-12);
    }

    public function testReciprocal(): void
    {
        $number1 = new RationalNumber(3, 4);
        $reciprocal = $number1->reciprocal();
        $this->assertEquals("4/3", $reciprocal->toString());
        $this->assertEqualsWithDelta(1.3333333333333333, $reciprocal->getFloat(), 1e-12);
    }

    public function testDivision(): void
    {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->divideBy($number2);
        $this->assertEquals("3/2", $result->toString());
        $this->assertEqualsWithDelta(1.5, $result->getFloat(), 1e-12);
    }

    #[IgnoreDeprecations]
    public function testDivisionFrom(): void
    {
        $this->expectUserDeprecationMessageMatches('/divideFrom\(\) is deprecated since 2\.11.*divideBy/');
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(1, 2);
        $result = $number1->divideFrom($number2);
        $this->assertEquals("2/3", $result->toString());
        $this->assertEqualsWithDelta(0.6666666666666666, $result->getFloat(), 1e-12);
    }

    public function testFromFloat(): void
    {
        $value = 2.5;
        $number = RationalNumber::fromFloat($value);
        $this->assertEquals("5/2", $number->toString());
        $this->assertEqualsWithDelta(2.5, $number->getFloat(), 1e-12);
    }

    public function testFromInt(): void
    {
        $value = 5;
        $number = RationalNumber::fromFloat($value);
        $this->assertEquals("5/1", $number->toString());
        $this->assertEqualsWithDelta(5, $number->getFloat(), 1e-12);
    }

    public function testFromNegativeFloat(): void
    {
        $value = -3.75;
        $number = RationalNumber::fromFloat($value);
        $this->assertEquals("-15/4", $number->toString());
        $this->assertEqualsWithDelta(-3.75, $number->getFloat(), 1e-12);
    }

    public function testFromNegativeInt(): void
    {
        $value = -8;
        $number = RationalNumber::fromFloat($value);
        $this->assertEquals("-8/1", $number->toString());
        $this->assertEqualsWithDelta(-8, $number->getFloat(), 1e-12);
    }

    public function testToPercentage(): void
    {
        $number = new RationalNumber(1, 2);
        $percentage = $number->toPercentage(2);
        $this->assertEquals("50.00%", $percentage);
    }

    public function testFromPercentage(): void
    {
        $percentageValue = "75%";
        $number = RationalNumber::fromPercentage($percentageValue);
        $this->assertEquals("3/4", $number->toString());
        $this->assertEqualsWithDelta(0.75, $number->getFloat(), 1e-12);
    }

    public function testFromStringWithSimpleFraction(): void
    {
        $number = RationalNumber::fromString('1/2');
        $this->assertEquals("1/2", $number->toString());
        $this->assertEquals(0.5, $number->getFloat());
    }

    public function testFromStringWithFractionThatReduces(): void
    {
        $number = RationalNumber::fromString('6/8');
        $this->assertEquals("3/4", $number->toString()); // Should be reduced
        $this->assertEquals(0.75, $number->getFloat());
    }

    public function testFromStringWithVariousFractions(): void
    {
        $testCases = [
            ['3/4', '3/4', 0.75],
            ['22/7', '22/7', 22 / 7],
            ['5/10', '1/2', 0.5], // Reduces
            ['100/25', '4/1', 4.0], // Reduces to integer
        ];

        foreach ($testCases as [$input, $expectedString, $expectedFloat]) {
            $number = RationalNumber::fromString($input);
            $this->assertEquals($expectedString, $number->toString(), "Failed for input: {$input}");
            $this->assertEqualsWithDelta($expectedFloat, $number->getFloat(), 1e-12, "Failed for input: {$input}");
        }
    }

    public function testFromStringWithNegativeFractions(): void
    {
        // Negative numerator
        $number1 = RationalNumber::fromString('-1/2');
        $this->assertEquals("-1/2", $number1->toString());
        $this->assertEquals(-0.5, $number1->getFloat());

        // Negative denominator (normalized to negative numerator)
        $number2 = RationalNumber::fromString('1/-2');
        $this->assertEquals("-1/2", $number2->toString());
        $this->assertEquals(-0.5, $number2->getFloat());

        // Both negative (becomes positive)
        $number3 = RationalNumber::fromString('-1/-2');
        $this->assertEquals("1/2", $number3->toString());
        $this->assertEquals(0.5, $number3->getFloat());
    }

    public function testFromStringWithSpaces(): void
    {
        // Leading/trailing spaces
        $number1 = RationalNumber::fromString(' 1/2 ');
        $this->assertEquals("1/2", $number1->toString());

        // Spaces around the slash
        $number2 = RationalNumber::fromString('1 / 2');
        $this->assertEquals("1/2", $number2->toString());

        // Multiple spaces
        $number3 = RationalNumber::fromString('  3  /  4  ');
        $this->assertEquals("3/4", $number3->toString());
    }

    public function testFromStringWithDecimalStrings(): void
    {
        // Simple decimal
        $number1 = RationalNumber::fromString('0.25');
        $this->assertEquals("1/4", $number1->toString());
        $this->assertEquals(0.25, $number1->getFloat());

        // Another decimal
        $number2 = RationalNumber::fromString('0.5');
        $this->assertEquals("1/2", $number2->toString());
        $this->assertEquals(0.5, $number2->getFloat());

        // Decimal that doesn't simplify nicely
        $number3 = RationalNumber::fromString('0.333');
        $this->assertEqualsWithDelta(0.333, $number3->getFloat(), 1e-12);
    }

    public function testFromStringWithIntegerStrings(): void
    {
        // Zero
        $zero = RationalNumber::fromString('0');
        $this->assertEquals("0/1", $zero->toString());
        $this->assertEquals(0.0, $zero->getFloat());

        // One
        $one = RationalNumber::fromString('1');
        $this->assertEquals("1/1", $one->toString());
        $this->assertEquals(1.0, $one->getFloat());

        // Positive integer
        $number = RationalNumber::fromString('42');
        $this->assertEquals("42/1", $number->toString());
        $this->assertEquals(42.0, $number->getFloat());

        // Negative integer
        $negNumber = RationalNumber::fromString('-5');
        $this->assertEquals("-5/1", $negNumber->toString());
        $this->assertEquals(-5.0, $negNumber->getFloat());
    }

    public function testFromStringWithScientificNotation(): void
    {
        $number = RationalNumber::fromString('1.5e-3');
        $this->assertEqualsWithDelta(0.0015, $number->getFloat(), 1e-12);
    }

    public function testFromStringWithEmptyStringThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot create RationalNumber from empty string");
        RationalNumber::fromString('');
    }

    public function testFromStringWithInvalidFormatThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid string format");
        RationalNumber::fromString('abc');
    }

    public function testFromStringWithMultipleFractionsThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid string format");
        RationalNumber::fromString('1/2/3');
    }

    public function testFromStringWithDecimalDenominatorThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid string format");
        RationalNumber::fromString('1.5/2');
    }

    public function testFromStringWithDivisionByZeroThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Denominator cannot be zero");
        RationalNumber::fromString('5/0');
    }

    public function testFromStringWithNegativeDivisionByZeroThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Denominator cannot be zero");
        RationalNumber::fromString('-3/0');
    }

    public function testIncreaseByPercentage(): void
    {
        $number = new RationalNumber(100);
        $increasePercentage = "10%";
        $result = $number->increaseByPercentage($increasePercentage);
        $this->assertEquals("110/1", $result->toString());
        $this->assertEquals(110, $result->getFloat());
    }

    public function testDecreaseByPercentage(): void
    {
        $number = new RationalNumber(200);
        $decreasePercentage = "25%";
        $result = $number->decreaseByPercentage($decreasePercentage);
        $this->assertEquals("150/1", $result->toString());
        $this->assertEquals(150, $result->getFloat());
    }

    public function testPercentageMethodsAcceptARateAsRationalNumber(): void
    {
        $price = new RationalNumber(200);
        $rate = RationalNumber::fromPercentage('25%');

        $this->assertSame('250/1', $price->increaseByPercentage($rate)->toString());
        $this->assertSame('150/1', $price->decreaseByPercentage($rate)->toString());
        $this->assertSame('220/1', $price->increaseByPercentage(new RationalNumber(1, 10))->toString());
        $this->assertTrue(
            $price->increaseByPercentage('12.5%')->equals($price->increaseByPercentage(new RationalNumber(1, 8)))
        );
    }

    public function testZeroDenominatorThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Denominator cannot be zero.");
        new RationalNumber(1, 0);
    }

    public function testReciprocalOfZeroThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot get reciprocal of zero.");
        $zero = new RationalNumber(0, 1);
        $zero->reciprocal();
    }

    public function testDivideByZeroThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot divide by zero.");
        $number = new RationalNumber(5, 1);
        $zero = new RationalNumber(0, 1);
        $number->divideBy($zero);
    }

    #[IgnoreDeprecations]
    public function testDivideFromZeroThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot divide by zero.");
        $zero = new RationalNumber(0, 1);
        $number = new RationalNumber(5, 1);
        $zero->divideFrom($number);
    }

    public function testNegativeDenominatorIsNormalized(): void
    {
        $number = new RationalNumber(3, -4);
        $this->assertEquals("-3/4", $number->toString());
        $this->assertEqualsWithDelta(-0.75, $number->getFloat(), 1e-12);
    }

    public function testNegativeNumbersAreNormalized(): void
    {
        $number = new RationalNumber(-6, -8);
        $this->assertEquals("3/4", $number->toString());
        $this->assertEqualsWithDelta(0.75, $number->getFloat(), 1e-12);
    }

    public function testIsZero(): void
    {
        $zero = new RationalNumber(0, 5);
        $notZero = new RationalNumber(1, 5);
        $this->assertTrue($zero->isZero());
        $this->assertFalse($notZero->isZero());
    }

    public function testIsInteger(): void
    {
        $integer = new RationalNumber(5, 1);
        $notInteger = new RationalNumber(5, 2);
        $this->assertTrue($integer->isInteger());
        $this->assertFalse($notInteger->isInteger());
    }

    #[IgnoreDeprecations]
    public function testReduce(): void
    {
        $number = new RationalNumber(6, 8);
        $this->expectUserDeprecationMessageMatches('/reduce\(\) is deprecated since 2\.11.*remove the call/');
        // Already normalized in the constructor: reduce() returns the same value.
        $this->assertEquals("3/4", $number->toString());
        $reduced = $number->reduce();
        $this->assertEquals("3/4", $reduced->toString());
    }

    public function testMagicToString(): void
    {
        $number = new RationalNumber(3, 4);
        $this->assertEquals("3/4", (string)$number);
    }

    // Tests for comparison methods (Comparable interface)

    public function testEquals(): void
    {
        $number1 = new RationalNumber(3, 4);
        $number2 = new RationalNumber(6, 8); // Equivalent to 3/4 after normalization
        $number3 = new RationalNumber(1, 2);

        $this->assertTrue($number1->equals($number2));
        $this->assertFalse($number1->equals($number3));
    }

    public function testCompareTo(): void
    {
        $smaller = new RationalNumber(1, 4);
        $larger = new RationalNumber(3, 4);
        $equal = new RationalNumber(3, 4);

        $this->assertEquals(-1, $smaller->compareTo($larger));
        $this->assertEquals(1, $larger->compareTo($smaller));
        $this->assertEquals(0, $larger->compareTo($equal));
    }

    public function testIsGreaterThan(): void
    {
        $smaller = new RationalNumber(1, 4);
        $larger = new RationalNumber(3, 4);

        $this->assertTrue($larger->isGreaterThan($smaller));
        $this->assertFalse($smaller->isGreaterThan($larger));
        $this->assertFalse($larger->isGreaterThan($larger));
    }

    public function testIsLessThan(): void
    {
        $smaller = new RationalNumber(1, 4);
        $larger = new RationalNumber(3, 4);

        $this->assertTrue($smaller->isLessThan($larger));
        $this->assertFalse($larger->isLessThan($smaller));
        $this->assertFalse($smaller->isLessThan($smaller));
    }

    public function testIsGreaterThanOrEqual(): void
    {
        $smaller = new RationalNumber(1, 4);
        $larger = new RationalNumber(3, 4);
        $equal = new RationalNumber(3, 4);

        $this->assertTrue($larger->isGreaterThanOrEqual($smaller));
        $this->assertTrue($larger->isGreaterThanOrEqual($equal));
        $this->assertFalse($smaller->isGreaterThanOrEqual($larger));
    }

    public function testIsLessThanOrEqual(): void
    {
        $smaller = new RationalNumber(1, 4);
        $larger = new RationalNumber(3, 4);
        $equal = new RationalNumber(1, 4);

        $this->assertTrue($smaller->isLessThanOrEqual($larger));
        $this->assertTrue($smaller->isLessThanOrEqual($equal));
        $this->assertFalse($larger->isLessThanOrEqual($smaller));
    }

    // Tests for abs() and negate()

    public function testAbs(): void
    {
        $positive = new RationalNumber(3, 4);
        $negative = new RationalNumber(-3, 4);

        $this->assertEquals("3/4", $positive->abs()->toString());
        $this->assertEquals("3/4", $negative->abs()->toString());
    }

    public function testNegate(): void
    {
        $positive = new RationalNumber(3, 4);
        $negative = new RationalNumber(-3, 4);

        $this->assertEquals("-3/4", $positive->negate()->toString());
        $this->assertEquals("3/4", $negative->negate()->toString());
    }

    public function testNegateZero(): void
    {
        $zero = new RationalNumber(0, 1);
        $this->assertEquals("0/1", $zero->negate()->toString());
        $this->assertTrue($zero->negate()->isZero());
    }

    // Tests for factory methods

    public function testZeroFactory(): void
    {
        $zero = RationalNumber::zero();
        $this->assertTrue($zero->isZero());
        $this->assertEquals("0/1", $zero->toString());
        $this->assertEqualsWithDelta(0, $zero->getFloat(), 1e-12);
    }

    public function testOneFactory(): void
    {
        $one = RationalNumber::one();
        $this->assertTrue($one->isInteger());
        $this->assertEquals("1/1", $one->toString());
        $this->assertEqualsWithDelta(1, $one->getFloat(), 1e-12);
    }

    public function testPropertiesAreReadonly(): void
    {
        $number = new RationalNumber(1, 2);
        $property = new \ReflectionProperty($number, 'numerator');

        $this->assertTrue($property->isReadOnly());
        $this->expectException(\Error::class);
        $property->setValue($number, 3);
    }
}
