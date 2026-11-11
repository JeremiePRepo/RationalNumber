<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RationalNumber\Exception\IntegerOverflowException;
use RationalNumber\RationalNumber;
use InvalidArgumentException;

/**
 * Specific tests to validate PHP 8.3 compatibility
 * and edge cases (overflow, float precision, large values)
 */
class PHP83EdgeCasesTest extends TestCase
{
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
        $number = RationalNumber::fromFloat($preciseFloat);

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
        $number = RationalNumber::fromFloat($verySmall);

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
        $number = RationalNumber::fromFloat($veryLarge);

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
        $zero = RationalNumber::zero();
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
        $one = RationalNumber::one();
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
            $this->assertEquals(
                $expected,
                $number->toString(),
                "Failed for {$num}/{$denom}"
            );
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
     * Test fromFloat with zero
     */
    public function testFromFloatZeroValue(): void
    {
        $zero = RationalNumber::fromFloat(0.0);

        $this->assertTrue($zero->isZero());
        $this->assertEquals("0/1", $zero->toString());
    }

    /**
     * Test fromFloat with negative float
     */
    public function testFromFloatNegativeValue(): void
    {
        $negative = RationalNumber::fromFloat(-2.5);

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

    /**
     * Test overflow detection in multiplication
     */
    public function testMultiplicationOverflowDetection(): void
    {
        $this->expectException(\ArithmeticError::class);
        $this->expectExceptionMessageMatches('/overflow/i');

        // Create a number that will overflow when multiplied by itself
        $largeNum = new RationalNumber(PHP_INT_MAX, 2);
        $largeNum->multiply($largeNum);
    }

    /**
     * Test overflow detection in addition with cross-multiplication
     */
    public function testAdditionOverflowDetection(): void
    {
        $this->expectException(\ArithmeticError::class);
        $this->expectExceptionMessageMatches('/overflow/i');

        // Create numbers that will overflow during cross-multiplication in add()
        $num1 = new RationalNumber(PHP_INT_MAX, 2);
        $num2 = new RationalNumber(PHP_INT_MAX, 3);
        $num1->add($num2);
    }

    /**
     * Test overflow detection in subtraction with cross-multiplication
     */
    public function testSubtractionOverflowDetection(): void
    {
        $this->expectException(\ArithmeticError::class);
        $this->expectExceptionMessageMatches('/overflow/i');

        // Create numbers that will overflow during cross-multiplication in subtract()
        $num1 = new RationalNumber(PHP_INT_MAX, 2);
        $num2 = new RationalNumber(PHP_INT_MAX, 3);
        $num1->subtract($num2);
    }

    /**
     * The products fit, but their sum does not (int + int would silently become a float).
     */
    public function testAdditionSumOverflowDetection(): void
    {
        $this->expectException(\ArithmeticError::class);
        $this->expectExceptionMessage('Operation would cause integer overflow during addition.');

        (new RationalNumber(PHP_INT_MAX))->add(new RationalNumber(1));
    }

    public function testSubtractionSumOverflowDetection(): void
    {
        $this->expectException(\ArithmeticError::class);
        $this->expectExceptionMessage('Operation would cause integer overflow during subtraction.');

        (new RationalNumber(-PHP_INT_MAX))->subtract(new RationalNumber(2));
    }

    /**
     * Products just above PHP_INT_MAX, where a float bound (PHP_INT_MAX / 2 = 2^62) is not exact.
     *
     * @return iterable<string, array{callable(): mixed}>
     */
    public static function productJustAboveIntMaxProvider(): iterable
    {
        yield 'multiply 2^62 by 2' => [fn () => (new RationalNumber(2 ** 62))->multiply(new RationalNumber(2))];
        yield 'multiply -2^62 by 2' => [fn () => (new RationalNumber(-(2 ** 62)))->multiply(new RationalNumber(2))];
        yield 'add 1/2 to 2^62' => [fn () => (new RationalNumber(2 ** 62))->add(new RationalNumber(1, 2))];
        yield 'subtract 1/2 from 2^62' => [fn () => (new RationalNumber(2 ** 62))->subtract(new RationalNumber(1, 2))];
        yield 'pow 2^32 squared' => [fn () => (new RationalNumber(2 ** 32))->pow(2)];
    }

    #[DataProvider('productJustAboveIntMaxProvider')]
    public function testProductJustAboveIntMaxThrowsOverflow(callable $operation): void
    {
        $this->expectException(IntegerOverflowException::class);

        $operation();
    }

    public function testLargestProductThatFitsDoesNotOverflow(): void
    {
        // 3 * 3074457345618258602 = 9223372036854775806 = PHP_INT_MAX - 1
        $product = (new RationalNumber(3074457345618258602))->multiply(new RationalNumber(3));

        $this->assertSame(PHP_INT_MAX - 1, $product->getNumerator());
    }

    public function testAdditionAtIntegerLimitsDoesNotOverflow(): void
    {
        $sum = (new RationalNumber(PHP_INT_MAX - 1))->add(new RationalNumber(1));
        $difference = (new RationalNumber(-PHP_INT_MAX + 1))->subtract(new RationalNumber(1));

        $this->assertSame(PHP_INT_MAX, $sum->getNumerator());
        $this->assertSame(-PHP_INT_MAX, $difference->getNumerator());
    }

    /**
     * Test fromFloat with scientific notation - small numbers
     */
    public function testFromFloatScientificNotationSmall(): void
    {
        $smallNumber = RationalNumber::fromFloat(1e-10);

        $this->assertEqualsWithDelta(1e-10, $smallNumber->getFloat(), 1e-15);
        $this->assertFalse($smallNumber->isZero());
    }

    /**
     * Test fromFloat with scientific notation - medium small numbers
     */
    public function testFromFloatScientificNotationMedium(): void
    {
        $number = RationalNumber::fromFloat(2.3e-5);

        $this->assertEqualsWithDelta(2.3e-5, $number->getFloat(), 1e-10);
        $this->assertFalse($number->isZero());
    }

    /**
     * Test fromFloat with scientific notation - large numbers
     */
    public function testFromFloatScientificNotationLarge(): void
    {
        $largeNumber = RationalNumber::fromFloat(1.5e10);

        $this->assertEqualsWithDelta(1.5e10, $largeNumber->getFloat(), 1e5);
        $this->assertFalse($largeNumber->isZero());
    }

    /**
     * Test fromFloat with extreme scientific notation that should overflow
     */
    public function testFromFloatExtremeScientificOverflow(): void
    {
        $this->expectException(\ArithmeticError::class);
        $this->expectExceptionMessageMatches('/overflow/i');

        // This should cause overflow
        RationalNumber::fromFloat(1e100);
    }

    /**
     * Test fromFloat with zero in scientific notation
     */
    public function testFromFloatScientificNotationZero(): void
    {
        $zero = RationalNumber::fromFloat(0.0e10);

        $this->assertTrue($zero->isZero());
        $this->assertEquals("0/1", $zero->toString());
    }

    /**
     * Test fromFloat with negative scientific notation
     */
    public function testFromFloatScientificNotationNegative(): void
    {
        $negative = RationalNumber::fromFloat(-3.14e-8);

        $this->assertEqualsWithDelta(-3.14e-8, $negative->getFloat(), 1e-13);
        $this->assertLessThan(0, $negative->getFloat());
    }

    /**
     * Test that overflow error messages mention GMP when available
     */
    public function testOverflowErrorMessageIncludesGMPHint(): void
    {
        try {
            $largeNum = new RationalNumber(PHP_INT_MAX, 2);
            $largeNum->multiply($largeNum);
            $this->fail('Expected ArithmeticError was not thrown');
        } catch (\ArithmeticError $e) {
            $message = $e->getMessage();
            $this->assertStringContainsStringIgnoringCase('overflow', $message);
            $this->assertStringContainsStringIgnoringCase('gmp', $message);
        }
    }
}
