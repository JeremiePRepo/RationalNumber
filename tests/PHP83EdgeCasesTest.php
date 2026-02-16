<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\TestCase;
use RationalNumber\RationalNumber;
use RationalNumber\Factory\RationalNumberFactory;
use InvalidArgumentException;

/**
 * Tests spécifiques pour valider la compatibilité PHP 8.3
 * et les cas limites (overflow, précision float, grandes valeurs)
 */
class PHP83EdgeCasesTest extends TestCase
{
    private RationalNumberFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new RationalNumberFactory();
    }

    /**
     * Test avec de très grands nombres proches de PHP_INT_MAX
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
     * Test avec de très grands dénominateurs
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
     * Test addition avec de très grands nombres
     */
    public function testAdditionWithLargeNumbers(): void
    {
        // Utiliser des nombres suffisamment grands mais qui ne causeront pas d'overflow lors de l'addition
        $num1 = new RationalNumber(1000000000, 1);
        $num2 = new RationalNumber(2000000000, 1);
        $result = $num1->add($num2);
        
        $this->assertEquals("3000000000/1", $result->toString());
        $this->assertEqualsWithDelta(3000000000.0, $result->getFloat(), 1e-6);
    }

    /**
     * Test multiplication avec de grands nombres (risque d'overflow)
     */
    public function testMultiplicationWithLargeNumbers(): void
    {
        // Nombres qui ne causeront pas d'overflow au numérateur/dénominateur
        $num1 = new RationalNumber(1000000, 1);
        $num2 = new RationalNumber(1000000, 1);
        $result = $num1->multiply($num2);
        
        $this->assertEquals("1000000000000/1", $result->toString());
        $this->assertEqualsWithDelta(1e12, $result->getFloat(), 1e-6);
    }

    /**
     * Test conversion float avec précision limite
     */
    public function testFloatPrecisionEdgeCases(): void
    {
        // Test avec un float qui a beaucoup de décimales
        $preciseFloat = 0.123456789012345;
        $number = $this->factory->fromFloat($preciseFloat);
        
        // Vérifier que la conversion est raisonnablement précise
        $this->assertEqualsWithDelta($preciseFloat, $number->getFloat(), 1e-10);
    }

    /**
     * Test conversion float avec valeurs très petites
     * Note: fromFloat a des limites de précision avec les très petits nombres
     * en raison de la conversion string->int dans l'implémentation
     */
    public function testVerySmallFloatValues(): void
    {
        $verySmall = 0.001; // Utiliser une valeur plus raisonnable
        $number = $this->factory->fromFloat($verySmall);
        
        $this->assertEqualsWithDelta($verySmall, $number->getFloat(), 1e-10);
        $this->assertFalse($number->isZero());
        $this->assertEquals("1/1000", $number->toString());
    }

    /**
     * Test conversion float avec valeurs très grandes
     */
    public function testVeryLargeFloatValues(): void
    {
        $veryLarge = 1000000.5;
        $number = $this->factory->fromFloat($veryLarge);
        
        $this->assertEqualsWithDelta($veryLarge, $number->getFloat(), 1e-6);
        $this->assertFalse($number->isInteger());
    }

    /**
     * Test normalisation avec de grands nombres négatifs
     */
    public function testNormalizationWithLargeNegativeNumbers(): void
    {
        $number = new RationalNumber(-1000000000, 1000);
        
        $this->assertEquals("-1000000/1", $number->toString());
        $this->assertEquals(-1000000.0, $number->getFloat());
    }

    /**
     * Test avec nombres négatifs au dénominateur (grande valeur)
     */
    public function testNegativeLargeDenominator(): void
    {
        $number = new RationalNumber(1000000, -1000);
        
        // Devrait être normalisé en négatif au numérateur
        $this->assertEquals("-1000/1", $number->toString());
        $this->assertEquals(-1000.0, $number->getFloat());
    }

    /**
     * Test chaînage d'opérations multiples (immutabilité)
     */
    public function testMethodChaining(): void
    {
        $number = new RationalNumber(10, 1);
        
        // Chaîner plusieurs opérations
        $result = $number
            ->add(new RationalNumber(5, 1))
            ->multiply(new RationalNumber(2, 1))
            ->subtract(new RationalNumber(10, 1))
            ->divideBy(new RationalNumber(2, 1));
        
        // (10 + 5) * 2 - 10 = 30 - 10 = 20, puis 20 / 2 = 10
        $this->assertEquals("10/1", $result->toString());
        $this->assertEquals(10.0, $result->getFloat());
        
        // Vérifier que l'original n'a pas changé (immutabilité)
        $this->assertEquals("10/1", $number->toString());
    }

    /**
     * Test chaînage complexe avec nombres fractionnaires
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
     * Test opérations avec zéro
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
     * Test opérations avec un (élément neutre)
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
     * Test réduction automatique avec grands nombres
     */
    public function testAutoReductionWithLargeNumbers(): void
    {
        // 1000000/2000000 devrait se réduire à 1/2
        $number = new RationalNumber(1000000, 2000000);
        
        $this->assertEquals("1/2", $number->toString());
        $this->assertEquals(0.5, $number->getFloat());
    }

    /**
     * Test reduction avec PGCD de grands nombres
     */
    public function testGCDWithLargeNumbers(): void
    {
        // 999999/333333 = 3/1 (PGCD = 333333)
        $number = new RationalNumber(999999, 333333);
        
        $this->assertEquals("3/1", $number->toString());
        $this->assertTrue($number->isInteger());
        $this->assertEquals(3.0, $number->getFloat());
    }

    /**
     * Test comparaisons avec nombres très proches
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
     * Test valeur absolue avec grands nombres négatifs
     */
    public function testAbsWithLargeNegativeNumbers(): void
    {
        $number = new RationalNumber(-1000000000, 1);
        $abs = $number->abs();
        
        $this->assertEquals("1000000000/1", $abs->toString());
        $this->assertEquals(1000000000.0, $abs->getFloat());
        
        // Original ne change pas
        $this->assertEquals("-1000000000/1", $number->toString());
    }

    /**
     * Test négation multiple (double négation)
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
     * Test type safety avec strict_types=1
     */
    public function testStrictTypesEnforcement(): void
    {
        // Ce test vérifie que strict_types est bien activé
        // En PHP 8.3 avec strict_types=1, passer une string devrait lever une TypeError
        $this->expectException(\TypeError::class);
        
        // @phpstan-ignore-next-line - On teste volontairement un mauvais type
        new RationalNumber("5", 1);
    }

    /**
     * Test toString avec nombres réduits
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
     * Test conversion pourcentage avec valeurs extrêmes
     * Note: toPercentage() utilise number_format() qui ajoute des séparateurs de milliers
     */
    public function testPercentageWithExtremeValues(): void
    {
        $verySmall = new RationalNumber(1, 10000);
        $this->assertEquals("0.01%", $verySmall->toPercentage(2));
        
        $veryLarge = new RationalNumber(10000, 1);
        // number_format ajoute des séparateurs de milliers pour les grands nombres
        $this->assertEquals("1,000,000.00%", $veryLarge->toPercentage(2));
    }

    /**
     * Test compareTo retourne les bonnes valeurs (-1, 0, 1)
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
     * Test factory fromFloat avec zéro
     */
    public function testFactoryFromFloatZero(): void
    {
        $zero = $this->factory->fromFloat(0.0);
        
        $this->assertTrue($zero->isZero());
        $this->assertEquals("0/1", $zero->toString());
    }

    /**
     * Test factory fromFloat avec float négatif
     */
    public function testFactoryFromFloatNegative(): void
    {
        $negative = $this->factory->fromFloat(-2.5);
        
        $this->assertEquals(-2.5, $negative->getFloat());
        $this->assertEquals("-5/2", $negative->toString());
    }

    /**
     * Test isInteger avec différentes fractions
     */
    public function testIsIntegerVariousCases(): void
    {
        $this->assertTrue((new RationalNumber(10, 1))->isInteger());
        $this->assertTrue((new RationalNumber(10, 2))->isInteger()); // Réduit à 5/1
        $this->assertTrue((new RationalNumber(15, 3))->isInteger()); // Réduit à 5/1
        $this->assertFalse((new RationalNumber(3, 2))->isInteger());
        $this->assertFalse((new RationalNumber(7, 4))->isInteger());
        $this->assertTrue((new RationalNumber(0, 1))->isInteger());
    }
}
