<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use RationalNumber\Calculator\PercentageCalculator;
use RationalNumber\Collection\RationalCollection;
use RationalNumber\Exception\DivisionByZeroException;
use RationalNumber\Exception\EmptyCollectionException;
use RationalNumber\Exception\IntegerOverflowException;
use RationalNumber\Exception\InvalidArgumentException;
use RationalNumber\Exception\RationalNumberException;
use RationalNumber\RationalNumber;

/**
 * Every error thrown by the library implements RationalNumberException and keeps the type
 * thrown before 2.11, so existing catch blocks still work.
 */
class ExceptionTest extends TestCase
{
    /**
     * @return iterable<string, array{callable(): mixed, class-string<\Throwable>, class-string<\Throwable>}>
     */
    public static function errorProvider(): iterable
    {
        yield 'zero denominator' => [
            fn () => new RationalNumber(1, 0),
            InvalidArgumentException::class,
            \InvalidArgumentException::class,
        ];
        yield 'invalid string' => [
            fn () => RationalNumber::fromString('abc'),
            InvalidArgumentException::class,
            \InvalidArgumentException::class,
        ];
        yield 'INF' => [
            fn () => RationalNumber::fromFloat(INF),
            InvalidArgumentException::class,
            \InvalidArgumentException::class,
        ];
        yield 'invalid JSON' => [
            fn () => RationalNumber::fromJson('not json'),
            InvalidArgumentException::class,
            \InvalidArgumentException::class,
        ];
        yield 'divide by zero' => [
            fn () => (new RationalNumber(1))->divideBy(RationalNumber::zero()),
            DivisionByZeroException::class,
            \InvalidArgumentException::class,
        ];
        yield 'reciprocal of zero' => [
            fn () => RationalNumber::zero()->reciprocal(),
            DivisionByZeroException::class,
            \InvalidArgumentException::class,
        ];
        yield 'average of empty collection' => [
            fn () => (new RationalCollection())->average(),
            EmptyCollectionException::class,
            \InvalidArgumentException::class,
        ];
        yield 'min of empty collection' => [
            fn () => (new RationalCollection())->min(),
            EmptyCollectionException::class,
            \InvalidArgumentException::class,
        ];
        yield 'multiplication overflow' => [
            fn () => (new RationalNumber(PHP_INT_MAX))->multiply(new RationalNumber(2)),
            IntegerOverflowException::class,
            \ArithmeticError::class,
        ];
        yield 'decimal too large' => [
            fn () => RationalNumber::fromString('1e30'),
            IntegerOverflowException::class,
            \ArithmeticError::class,
        ];
        yield 'decimal too small' => [
            fn () => RationalNumber::fromFloat(1e-30),
            IntegerOverflowException::class,
            \ArithmeticError::class,
        ];
    }

    /**
     * @param callable(): mixed $operation
     * @param class-string<\Throwable> $expectedClass
     * @param class-string<\Throwable> $legacyClass
     */
    #[DataProvider('errorProvider')]
    public function testErrorTypes(callable $operation, string $expectedClass, string $legacyClass): void
    {
        try {
            $operation();
        } catch (\Throwable $e) {
            $this->assertInstanceOf($expectedClass, $e);
            $this->assertInstanceOf(RationalNumberException::class, $e);
            $this->assertInstanceOf($legacyClass, $e);

            return;
        }

        $this->fail("Expected {$expectedClass} to be thrown.");
    }

    #[IgnoreDeprecations]
    public function testPercentageOfZeroThrowsDivisionByZero(): void
    {
        $this->expectException(DivisionByZeroException::class);

        (new PercentageCalculator())->percentageOf((new RationalNumber(1)), RationalNumber::zero());
    }

    public function testOverflowMessageMentionsGmp(): void
    {
        $exception = IntegerOverflowException::create('Overflow.');

        $this->assertStringStartsWith('Overflow. ', $exception->getMessage());
        $this->assertStringContainsString('GMP extension', $exception->getMessage());
    }

    public function testCatchingTheMarkerInterface(): void
    {
        try {
            (new RationalNumber(PHP_INT_MAX))->multiply(new RationalNumber(2));
            $this->fail('Expected an overflow.');
        } catch (RationalNumberException $e) {
            $this->assertInstanceOf(IntegerOverflowException::class, $e);
        }
    }
}
