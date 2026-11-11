<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\TestCase;
use RationalNumber\RationalNumber;

/**
 * Exact decimal parsing in fromString(), fromFloat() and fromPercentage().
 *
 * Decimal strings are parsed digit by digit (no float round-trip), and floats are converted
 * from their shortest decimal representation, independently of the precision ini settings.
 */
class DecimalParsingTest extends TestCase
{
    /** @var array<string, string|false> */
    private array $savedIni = [];

    protected function setUp(): void
    {
        $this->savedIni = [
            'precision' => ini_get('precision'),
            'serialize_precision' => ini_get('serialize_precision'),
        ];
    }

    protected function tearDown(): void
    {
        foreach ($this->savedIni as $name => $value) {
            if ($value !== false) {
                ini_set($name, $value);
            }
        }
    }

    // ---------------------------------------------------------------
    // fromFloat() and fromString(): same exact result for the same decimal
    // ---------------------------------------------------------------

    /**
     * @dataProvider decimalProvider
     */
    public function testFromFloatIsExact(string $input, string $expected): void
    {
        $this->assertSame($expected, RationalNumber::fromFloat((float) $input)->toString());
    }

    /**
     * @dataProvider decimalProvider
     */
    public function testFromStringIsExact(string $input, string $expected): void
    {
        $this->assertSame($expected, RationalNumber::fromString($input)->toString());
    }

    /**
     * Decimals that are the shortest representation of a float, so both conversions must agree.
     *
     * @return array<string, array{string, string}>
     */
    public static function decimalProvider(): array
    {
        return [
            // fromFloat() used to truncate $value * 10^n (0.29 * 100 = 28.999999999999996 gave 7/25).
            '0.29'                      => ['0.29', '29/100'],
            '0.57'                      => ['0.57', '57/100'],
            '1.15'                      => ['1.15', '23/20'],
            '4.35'                      => ['4.35', '87/20'],
            '9.95'                      => ['9.95', '199/20'],
            '-0.29'                     => ['-0.29', '-29/100'],
            '-1.15'                     => ['-1.15', '-23/20'],
            '0.001'                     => ['0.001', '1/1000'],
            '1.005'                     => ['1.005', '201/200'],
            // "1.15E+15": the mantissa 1.15 * 100 = 114.99999999999999 was truncated too.
            'scientific notation'       => ['1.15e15', '1150000000000000/1'],
            // (string) 99999999999999.5 is "1.0E+14" with precision=14: the .5 was lost.
            'all significant digits'    => ['99999999999999.5', '199999999999999/2'],
            // Both used to throw a false overflow error.
            'large exponent that fits'  => ['1.2345e16', '12345000000000000/1'],
            'many significant digits'   => ['123456789012345.67', '12345678901234567/100'],
        ];
    }

    // ---------------------------------------------------------------
    // fromString(): exact parsing of inputs a float cannot hold
    // ---------------------------------------------------------------

    /**
     * @dataProvider exactDecimalStringProvider
     */
    public function testFromStringParsesDecimalsExactly(string $input, string $expected): void
    {
        $this->assertSame($expected, RationalNumber::fromString($input)->toString());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function exactDecimalStringProvider(): array
    {
        return [
            'leading dot'               => ['.5', '1/2'],
            'trailing dot'              => ['5.', '5/1'],
            'explicit plus sign'        => ['+2.5', '5/2'],
            'negative zero'             => ['-0.0', '0/1'],
            'many trailing zeros'       => ['1.50000000000000000000', '3/2'],
            'many leading zeros'        => ['000000000000000000000.25', '1/4'],
            'negative exponent'         => ['1.5e-3', '3/2000'],
            'uppercase exponent'        => ['2.5E+2', '250/1'],
            'beyond float precision'    => ['0.12345678901234567', '12345678901234567/100000000000000000'],
            'PHP_INT_MAX'               => ['9223372036854775807', '9223372036854775807/1'],
            'smallest exact decimal'    => ['1e-18', '1/1000000000000000000'],
            'fraction with zeros'       => ['00000000000000000000001/2', '1/2'],
        ];
    }

    // ---------------------------------------------------------------
    // fromString(): strict errors
    // ---------------------------------------------------------------

    /**
     * @dataProvider overflowingStringProvider
     */
    public function testFromStringThrowsOnValuesThatCannotBeRepresented(string $input): void
    {
        $this->expectException(\ArithmeticError::class);
        $this->expectExceptionMessageMatches('/overflow/i');

        RationalNumber::fromString($input);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function overflowingStringProvider(): array
    {
        return [
            'huge exponent'             => ['1e400'], // used to become INF, then silently 0/1
            'tiny exponent'             => ['1e-400'],
            'too many integer digits'   => ['99999999999999999999'],
            'PHP_INT_MAX + 1'           => ['9223372036854775808'],
            'too many decimal places'   => ['1e-19'],
            'absurd exponent'           => ['1e99999999999999999999'],
            'fraction PHP_INT_MAX + 1'  => ['9223372036854775808/1'],
            'fraction denominator'      => ['1/9223372036854775808'],
        ];
    }

    /**
     * @dataProvider invalidStringProvider
     */
    public function testFromStringThrowsOnInvalidFormat(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid string format');

        RationalNumber::fromString($input);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidStringProvider(): array
    {
        return [
            'letters'          => ['abc'],
            'two dots'         => ['1.2.3'],
            'exponent only'    => ['e5'],
            'lone dot'         => ['.'],
            'lone sign'        => ['-'],
            'hexadecimal'      => ['0x1A'],
            'inner whitespace' => ['1 000'],
        ];
    }

    // ---------------------------------------------------------------
    // fromFloat()
    // ---------------------------------------------------------------

    /**
     * @dataProvider nonFiniteFloatProvider
     */
    public function testFromFloatThrowsOnNonFiniteValues(float $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('INF or NAN');

        RationalNumber::fromFloat($value);
    }

    /**
     * @return array<string, array{float}>
     */
    public static function nonFiniteFloatProvider(): array
    {
        return [
            'INF'  => [INF],
            '-INF' => [-INF],
            'NAN'  => [NAN],
        ];
    }

    public function testFromFloatThrowsWhenValueIsTooLarge(): void
    {
        $this->expectException(\ArithmeticError::class);
        $this->expectExceptionMessageMatches('/overflow/i');

        RationalNumber::fromFloat(1e19);
    }

    public function testFromFloatThrowsInsteadOfReturningZeroForTinyValues(): void
    {
        // Used to silently return 0/1.
        $this->expectException(\ArithmeticError::class);
        $this->expectExceptionMessageMatches('/too small/i');

        RationalNumber::fromFloat(1.5e-20);
    }

    /**
     * Values whose exact decimal does not fit are rounded (half up) to 18 decimal places.
     *
     * @dataProvider floatsToRoundProvider
     */
    public function testFromFloatRoundsValuesThatDoNotFitExactly(float $value, string $expected): void
    {
        $this->assertSame($expected, RationalNumber::fromFloat($value)->toString());
    }

    /**
     * @return array<string, array{float, string}>
     */
    public static function floatsToRoundProvider(): array
    {
        return [
            // 12345678901234568 / 10^21 => 12345678901235 / 10^18
            'too many decimal places'   => [1.2345678901234568e-5, '2469135780247/200000000000000000'],
            'negative'                  => [-1.2345678901234568e-5, '-2469135780247/200000000000000000'],
            'rounds up instead of zero' => [6e-19, '1/1000000000000000000'],
        ];
    }

    public function testFromFloatHandlesNegativeZero(): void
    {
        $this->assertSame('0/1', RationalNumber::fromFloat(-0.0)->toString());
    }

    public function testFromFloatRestoresSerializePrecision(): void
    {
        ini_set('serialize_precision', '17');

        RationalNumber::fromFloat(0.29);

        $this->assertSame('17', ini_get('serialize_precision'));
    }

    // ---------------------------------------------------------------
    // Independence from the precision ini settings
    // ---------------------------------------------------------------

    /**
     * @dataProvider iniSettingsProvider
     */
    public function testConversionsDoNotDependOnPrecisionSettings(string $precision, string $serializePrecision): void
    {
        ini_set('precision', $precision);
        ini_set('serialize_precision', $serializePrecision);

        $this->assertSame('29/100', RationalNumber::fromFloat(0.29)->toString());
        $this->assertSame('23/20', RationalNumber::fromFloat(1.15)->toString());
        $this->assertSame('1150000000000000/1', RationalNumber::fromFloat(1.15e15)->toString());
        $this->assertSame(
            '3/10',
            RationalNumber::fromFloat(0.1)->add(RationalNumber::fromFloat(0.2))->toString()
        );
        $this->assertSame('57/100', RationalNumber::fromPercentage('57%')->toString());
        $this->assertSame('29/100', RationalNumber::fromString('0.29')->toString());
        $this->assertSame('1311/10', RationalNumber::fromFloat(114)->increaseByPercentage('15%')->toString());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function iniSettingsProvider(): array
    {
        return [
            'PHP defaults'          => ['14', '-1'],
            'high precision'        => ['17', '17'],
            'low precision'         => ['5', '5'],
            'shortest round-trip'   => ['-1', '-1'],
        ];
    }

    // ---------------------------------------------------------------
    // fromPercentage()
    // ---------------------------------------------------------------

    /**
     * @dataProvider percentageProvider
     */
    public function testFromPercentageIsExact(string $input, string $expected): void
    {
        $this->assertSame($expected, RationalNumber::fromPercentage($input)->toString());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function percentageProvider(): array
    {
        return [
            'simple'                  => ['57%', '57/100'],
            'used to truncate'        => ['29%', '29/100'],
            'without sign'            => ['57', '57/100'],
            'decimal with spaces'     => [' 12.5 % ', '1/8'],
            'negative'                => ['-20%', '-1/5'],
            'over one hundred'        => ['250%', '5/2'],
            'many decimals'           => ['33.333333%', '33333333/100000000'],
        ];
    }

    /**
     * @dataProvider invalidPercentageProvider
     */
    public function testFromPercentageThrowsOnInvalidInput(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);

        RationalNumber::fromPercentage($input);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidPercentageProvider(): array
    {
        return [
            'letters'      => ['abc%'],
            'empty'        => [''],
            'sign only'    => ['%'],
        ];
    }

    public function testIncreaseByPercentageThrowsOnInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Used to be treated as 0% and return the value unchanged.
        (new RationalNumber(100))->increaseByPercentage('abc%');
    }

    public function testDecreaseByPercentageIsExact(): void
    {
        $this->assertSame('5643/100', RationalNumber::fromString('57')->decreaseByPercentage('1%')->toString());
    }

    // ---------------------------------------------------------------
    // Callers relying on fromFloat()
    // ---------------------------------------------------------------

    public function testSqrtOfSmallNumberDoesNotThrow(): void
    {
        $number = new RationalNumber(2, 1000000000);
        $root = $number->sqrt();

        $this->assertFalse($root->isZero());
        $this->assertEqualsWithDelta(sqrt(2e-9), $root->getFloat(), 1e-15);
    }
}
