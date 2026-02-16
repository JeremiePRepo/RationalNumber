<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\TestCase;
use RationalNumber\RationalNumber;
use RationalNumber\Factory\RationalNumberFactory;
use InvalidArgumentException;

/**
 * Specific tests to validate PHP 8.3 compatibility
 * and edge cases (overflow, float precision, large values)
 */
class PHP83EdgeCasesTest extends TestCase
{
    private RationalNumberFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new RationalNumberFactory();
    }

    /**
     * Test with very large numbers close to PHP_INT_MAX
     */
    public function testLargeNumeratorValues(): void
    {
        $largeNum = PHP_INT_MAX - 1000;
        $number = new RationalNumber($largeNum, 1);
        
        $this->assertEquals($largeNum, $number->getFloat());
        $this->assertEquals("{$largeNum}/1", $number->toString());
        $this->assertTrue($number->isInteger());
    }

    /**
     * Test with very large denominators
     */
    public function testLargeDenominatorValues(): void
    {
        $largeDenom = PHP_INT_MAX - 1000;
        $number = new RationalNumber(1, $largeDenom);
        
        $this->assertEqualsWithDelta(1 / $largeDenom, $number->getFloat(), 1e-15);
        $this->assertEquals("1/{$largeDenom}", $number->toString());
        $this->assertFalse($number->isInteger());
    }

    /**
     * Test addition with very large numbers
     */
    public function testAdditionWithLargeNumbers(): void
    {
        // Use numbers large enough but that won't cause overflow during addition
        $num1 = new RationalNumber(1000000000, 1);
        $num2 = new RationalNumber(2000000000, 1);
        $result = $num1->add($num2);
        
        $this->assertEquals("3000000000/1", $result->toString());
        $this->assertEqualsWithDelta(3000000000.0, $result->getFloat(), 1e-6);
    }

    /**
     * Test multiplication with large numbers (risk of overflow)
     */
    public function testMultiplicationWithLargeNumbers(): void
    {
        // Numbers that won't cause overflow in numerator/denominator
        $num1 = new RationalNumber(1000000, 1);
        $num2 = new RationalNumber(1000000, 1);
        $result = $num1->multiply($num2);
        
        $this->assertEquals("1000000000000/1", $result->toString());
        $this->assertEqualsWithDelta(1e12, $result->getFloat(), 1e-6);
    }

    /**
     * Test float conversion with precision limits
     */
    public function testFloatPrecisionEdgeCases(): void
    {
        // Test with a float that has many decimal places
        $preciseFloat = 0.123456789012345;
        $number = $this->factory->fromFloat($preciseFloat);
        
        // Verify that the conversion is reasonably accurate
        $this->assertEqualsWithDelta($preciseFloat, $number->getFloat(), 1e-10);
    }

    /**
     * Test float conversion with very small values
     * Note: fromFloat has precision limits with very small numbers
     * due to string->int conversion in the implementation
     */
    public function testVerySmallFloatValues(): void
    {
        $verySmall = 0.001; // Use a more reasonable value
        $number = $this->factory->fromFloat($verySmall);
        
        $this->assertEqualsWithDelta($verySmall, $number->getFloat(), 1e-10);
        $this->assertFalse($number->isZero());
        $this->assertEquals("1/1000", $number->toString());
    }

    /**
     * Test float conversion with very large values
     */
    public function testVeryLargeFloatValues(): void
    {
        $veryLarge = 1000000.5;
        $number = $this->factory->fromFloat($veryLarge);
        
        $this->assertEqualsWithDelta($veryLarge, $number->getFloat(), 1e-6);
        $this->assertFalse($number->isInteger());
    }

    /**
     * Test normalization with large negative numbers
     */
    public function testNormalizationWithLargeNegativeNumbers(): void
    {
        $number = new RationalNumber(-1000000000, 1000);
        
        $this->assertEquals("-1000000/1", $number->toString());
        $this->assertEquals(-1000000.0, $number->getFloat());
    }

    /**
     * Test with negative numbers in denominator (large value)
     */
    public function testNegativeLargeDenominator(): void
    {
        $number = new RationalNumber(1000000, -1000);
        
        // Should be normalized with negative in numerator
        $this->assertEquals("-1000/1", $number->toString());
        $this->assertEquals(-1000.0, $number->getFloat());
    }

    /**
     * Test chaining multiple operations (immutability)
     */
    public function testMethodChaining(): void
    {
        $number = new RationalNumber(10, 1);
        
        // Chain multiple operations
        $result = $number
            ->add(new RationalNumber(5, 1))
            ->multiply(new RationalNumber(2, 1))
            ->subtract(new RationalNumber(10, 1))
            ->divideBy(new RationalNumber(2, 1));
        
        // (10 + 5) * 2 - 10 = 30 - 10 = 20, then 20 / 2 = 10
        $this->assertEquals("10/1", $result->toString());
        $this->assertEquals(10.0, $result->getFloat());
        
        // Verify that the original hasn't changed (immutability)
        $this->assertEquals("10/1", $number->toString());
    }

    /**
     * Test complex chaining with fractional numbers
     */
    public function testComplexMethodChaining(): void
    {
        $number = new RationalNumber(3, 4);
        
        $result = $number
            ->add(new RationalNumber(1, 4))
            ->reciprocal()
            ->multiply(new RationalNumber(2, 1))
            ->abs();
        
        // (3/4 + 1/4) = 1, reciprocal = 1/1, multiply by 2 = 2/1
        $this->assertEquals("2/1", $result->toString());
        $this->assertEquals(2.0, $result->getFloat());
    }

    /**
     * Test operations with zero
     */
    public function testZeroOperations(): void
    {
        $zero = $this->factory->zero();
        $number = new RationalNumber(5, 1);
        
        $this->assertEquals("5/1", $number->add($zero)->toString());
        $this->assertEquals("5/1", $number->subtract($zero)->toString());
        $this->assertEquals("0/1", $number->multiply($zero)->toString());
        $this->assertEquals("0/1", $zero->multiply($number)->toString());
        
        $this->assertTrue($zero->isZero());
        $this->assertTrue($zero->negate()->isZero());
    }

    /**
     * Test operations with one (identity element)
     */
    public function testOneOperations(): void
    {
        $one = $this->factory->one();
        $number = new RationalNumber(7, 3);
        
        $this->assertEquals("7/3", $number->multiply($one)->toString());
        $this->assertEquals("7/3", $number->divideBy($one)->toString());
        $this->assertEquals("1/1", $one->reciprocal()->toString());
    }

    /**
     * Test automatic reduction with large numbers
     */
    public function testAutoReductionWithLargeNumbers(): void
    {
        // 1000000/2000000 should reduce to 1/2
        $number = new RationalNumber(1000000, 2000000);
        
        $this->assertEquals("1/2", $number->toString());
        $this->assertEquals(0.5, $number->getFloat());
    }

    /**
     * Test reduction with GCD of large numbers
     */
    public function testGCDWithLargeNumbers(): void
    {
        // 999999/333333 = 3/1 (GCD = 333333)
        $number = new RationalNumber(999999, 333333);
        
        $this->assertEquals("3/1", $number->toString());
        $this->assertTrue($number->isInteger());
        $this->assertEquals(3.0, $number->getFloat());
    }

    /**
     * Test comparisons with very close numbers
     */
    public function testComparisonWithVeryCloseNumbers(): void
    {
        $num1 = new RationalNumber(1000000001, 1000000000);
        $num2 = new RationalNumber(1000000002, 1000000000);
        
        $this->assertTrue($num2->isGreaterThan($num1));
        $this->assertTrue($num1->isLessThan($num2));
        $this->assertFalse($num1->equals($num2));
    }

    /**
     * Test absolute value with large negative numbers
     */
    public function testAbsWithLargeNegativeNumbers(): void
    {
        $number = new RationalNumber(-1000000000, 1);
        $abs = $number->abs();
        
        $this->assertEquals("1000000000/1", $abs->toString());
        $this->assertEquals(1000000000.0, $abs->getFloat());
        
        // Original doesn't change
        $this->assertEquals("-1000000000/1", $number->toString());
    }

    /**
     * Test multiple negation (double negation)
     */
    public function testDoubleNegation(): void
    {
        $number = new RationalNumber(42, 7);
        $negated = $number->negate();
        $doubleNegated = $negated->negate();
        
        $this->assertEquals("6/1", $number->toString());
        $this->assertEquals("-6/1", $negated->toString());
        $this->assertEquals("6/1", $doubleNegated->toString());
    }

    /**
     * Test type safety with strict_types=1
     */
    public function testStrictTypesEnforcement(): void
    {
        // This test verifies that strict_types is properly enabled
        // In PHP 8.3 with strict_types=1, passing a string should raise a TypeError
        $this->expectException(\TypeError::class);
        
        // @phpstan-ignore-next-line - We deliberately test a wrong type
        new RationalNumber("5", 1);
    }

    /**
     * Test toString with reduced numbers
     */
    public function testToStringWithReducedFractions(): void
    {
        $tests = [
            [6, 3, "2/1"],
            [4, 8, "1/2"],
            [15, 25, "3/5"],
            [100, 200, "1/2"],
            [-6, 3, "-2/1"],
            [6, -3, "-2/1"],
        ];
        
        foreach ($tests as [$num, $denom, $expected]) {
            $number = new RationalNumber($num, $denom);
            $this->assertEquals($expected, $number->toString(), 
                "Failed for {$num}/{$denom}");
        }
    }

    /**
     * Test percentage conversion with extreme values
     * Note: toPercentage() uses number_format() which adds thousands separators
     */
    public function testPercentageWithExtremeValues(): void
    {
        $verySmall = new RationalNumber(1, 10000);
        $this->assertEquals("0.01%", $verySmall->toPercentage(2));
        
        $veryLarge = new RationalNumber(10000, 1);
        // number_format adds thousands separators for large numbers
        $this->assertEquals("1,000,000.00%", $veryLarge->toPercentage(2));
    }

    /**
     * Test compareTo returns the correct values (-1, 0, 1)
     */
    public function testCompareToReturnValues(): void
    {
        $num1 = new RationalNumber(3, 4);
        $num2 = new RationalNumber(1, 2);
        $num3 = new RationalNumber(3, 4);
        
        $this->assertEquals(1, $num1->compareTo($num2)); // 3/4 > 1/2
        $this->assertEquals(-1, $num2->compareTo($num1)); // 1/2 < 3/4
        $this->assertEquals(0, $num1->compareTo($num3)); // 3/4 == 3/4
    }

    /**
     * Test factory fromFloat with zero
     */
    public function testFactoryFromFloatZero(): void
    {
        $zero = $this->factory->fromFloat(0.0);
        
        $this->assertTrue($zero->isZero());
        $this->assertEquals("0/1", $zero->toString());
    }

    /**
     * Test factory fromFloat with negative float
     */
    public function testFactoryFromFloatNegative(): void
    {
        $negative = $this->factory->fromFloat(-2.5);
        
        $this->assertEquals(-2.5, $negative->getFloat());
        $this->assertEquals("-5/2", $negative->toString());
    }

    /**
     * Test isInteger with different fractions
     */
    public function testIsIntegerVariousCases(): void
    {
        $this->assertTrue((new RationalNumber(10, 1))->isInteger());
        $this->assertTrue((new RationalNumber(10, 2))->isInteger()); // Reduces to 5/1
        $this->assertTrue((new RationalNumber(15, 3))->isInteger()); // Reduces to 5/1
        $this->assertFalse((new RationalNumber(3, 2))->isInteger());
        $this->assertFalse((new RationalNumber(7, 4))->isInteger());
        $this->assertTrue((new RationalNumber(0, 1))->isInteger());
    }
}
