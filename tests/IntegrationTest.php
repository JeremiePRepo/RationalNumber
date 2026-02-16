<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\TestCase;
use RationalNumber\RationalNumber;
use RationalNumber\Calculator\PercentageCalculator;
use RationalNumber\Factory\RationalNumberFactory;

/**
 * Tests d'intégration combinant Factory, Calculator et RationalNumber
 * pour valider les interactions entre composants
 */
class IntegrationTest extends TestCase
{
    private RationalNumberFactory $factory;
    private PercentageCalculator $calculator;

    protected function setUp(): void
    {
        $this->factory = new RationalNumberFactory();
        $this->calculator = new PercentageCalculator();
    }

    /**
     * Scénario complet: création via factory, calculs, puis conversion pourcentage
     */
    public function testCompleteWorkflow(): void
    {
        // Créer un nombre via factory
        $price = $this->factory->fromFloat(100.0);
        
        // Appliquer une remise de 20% via calculator
        $discount = $this->calculator->decreaseBy($price, "20%");
        
        // Vérifier le résultat
        $this->assertEquals(80.0, $discount->getFloat());
        $this->assertEquals("80/1", $discount->toString());
        
        // Ajouter une taxe de 10%
        $withTax = $this->calculator->increaseBy($discount, "10%");
        
        // 80 + 10% = 88
        $this->assertEquals(88.0, $withTax->getFloat());
    }

    /**
     * Scénario: calcul de pourcentage d'économie
     */
    public function testSavingsCalculation(): void
    {
        $originalPrice = $this->factory->fromFloat(150.0);
        $salePrice = $this->factory->fromFloat(120.0);
        
        // Calculer l'économie
        $savings = $originalPrice->subtract($salePrice);
        $this->assertEquals(30.0, $savings->getFloat());
        
        // Calculer le pourcentage d'économie
        $savingsPercent = $this->calculator->percentageOf($savings, $originalPrice);
        $this->assertEquals("20.00%", $savingsPercent);
    }

    /**
     * Scénario: calcul d'intérêts composés (simplifié)
     */
    public function testCompoundInterest(): void
    {
        // Capital initial
        $principal = $this->factory->fromFloat(1000.0);
        
        // Taux d'intérêt annuel: 5%
        $rate = "5%";
        
        // Après 1 an
        $year1 = $this->calculator->increaseBy($principal, $rate);
        $this->assertEquals(1050.0, $year1->getFloat());
        
        // Après 2 ans
        $year2 = $this->calculator->increaseBy($year1, $rate);
        $this->assertEqualsWithDelta(1102.5, $year2->getFloat(), 0.01);
        
        // Après 3 ans
        $year3 = $this->calculator->increaseBy($year2, $rate);
        $this->assertEqualsWithDelta(1157.625, $year3->getFloat(), 0.01);
    }

    /**
     * Scénario: partage de facture entre plusieurs personnes
     */
    public function testBillSplitting(): void
    {
        // Facture totale
        $totalBill = $this->factory->fromFloat(150.75);
        
        // Pourboire de 15%
        $tip = $this->calculator->increaseBy($totalBill, "15%");
        $this->assertEqualsWithDelta(173.3625, $tip->getFloat(), 0.01);
        
        // Diviser entre 4 personnes
        $fourPeople = $this->factory->create(4, 1);
        $perPerson = $tip->divideBy($fourPeople);
        
        $this->assertEqualsWithDelta(43.340625, $perPerson->getFloat(), 0.01);
    }

    /**
     * Scénario: conversion de recette (proportions)
     */
    public function testRecipeScaling(): void
    {
        // Recette originale pour 4 personnes: 2/3 tasse de farine
        $originalFlour = $this->factory->create(2, 3);
        
        // Adapter pour 6 personnes (facteur 1.5)
        $scaleFactor = $this->factory->create(3, 2);
        $scaledFlour = $originalFlour->multiply($scaleFactor);
        
        // 2/3 * 3/2 = 6/6 = 1
        $this->assertEquals("1/1", $scaledFlour->toString());
        $this->assertEquals(1.0, $scaledFlour->getFloat());
        $this->assertTrue($scaledFlour->isInteger());
    }

    /**
     * Scénario: calcul de moyenne pondérée
     */
    public function testWeightedAverage(): void
    {
        // Notes avec poids
        // Note 1: 85/100 avec poids 30%
        $note1 = $this->factory->create(85, 100);
        $weight1 = $this->factory->fromPercentage("30%");
        
        // Note 2: 90/100 avec poids 70%
        $note2 = $this->factory->create(90, 100);
        $weight2 = $this->factory->fromPercentage("70%");
        
        // Moyenne pondérée
        $weighted1 = $note1->multiply($weight1);
        $weighted2 = $note2->multiply($weight2);
        $average = $weighted1->add($weighted2);
        
        // 0.85 * 0.30 + 0.90 * 0.70 = 0.255 + 0.630 = 0.885
        $this->assertEqualsWithDelta(0.885, $average->getFloat(), 0.001);
        $this->assertEquals("177/200", $average->toString());
    }

    /**
     * Scénario: comparaison de produits avec remises différentes
     */
    public function testProductComparison(): void
    {
        // Produit A: 100€ avec 20% de remise
        $productA = $this->factory->fromFloat(100.0);
        $priceA = $this->calculator->decreaseBy($productA, "20%");
        
        // Produit B: 90€ avec 10% de remise
        $productB = $this->factory->fromFloat(90.0);
        $priceB = $this->calculator->decreaseBy($productB, "10%");
        
        // Comparer les prix finaux
        $this->assertEquals(80.0, $priceA->getFloat());
        $this->assertEquals(81.0, $priceB->getFloat());
        
        $this->assertTrue($priceA->isLessThan($priceB));
        $this->assertFalse($priceA->equals($priceB));
    }

    /**
     * Scénario: conversion entre formats différents
     */
    public function testFormatConversions(): void
    {
        // Partir d'un float
        $value = $this->factory->fromFloat(0.375);
        
        // Convertir en pourcentage
        $percentage = $this->calculator->toPercentage($value, 1);
        $this->assertEquals("37.5%", $percentage);
        
        // Reconvertir du pourcentage
        $backToRational = $this->factory->fromPercentage($percentage);
        
        // Vérifier qu'on retrouve la valeur originale
        $this->assertEqualsWithDelta($value->getFloat(), $backToRational->getFloat(), 0.001);
    }

    /**
     * Scénario: calculs en cascade avec factory methods
     */
    public function testCascadingCalculations(): void
    {
        // Partir de zéro
        $result = $this->factory->zero();
        
        // Ajouter des valeurs
        $result = $result->add($this->factory->fromFloat(10.5));
        $result = $result->add($this->factory->fromFloat(20.25));
        $result = $result->add($this->factory->fromFloat(5.0));
        
        // Total devrait être 35.75
        $this->assertEqualsWithDelta(35.75, $result->getFloat(), 0.01);
        
        // Multiplier par 2
        $result = $result->multiply($this->factory->create(2, 1));
        $this->assertEqualsWithDelta(71.5, $result->getFloat(), 0.01);
    }

    /**
     * Scénario: utilisation des factory constants (zero, one)
     */
    public function testFactoryConstants(): void
    {
        $zero = $this->factory->zero();
        $one = $this->factory->one();
        
        $number = $this->factory->create(42, 7);
        
        // Propriétés des éléments neutres
        $this->assertTrue($number->add($zero)->equals($number));
        $this->assertTrue($number->multiply($one)->equals($number));
        $this->assertTrue($number->subtract($zero)->equals($number));
        $this->assertTrue($number->divideBy($one)->equals($number));
    }

    /**
     * Scénario: calcul de TVA avec différents taux
     */
    public function testVATCalculation(): void
    {
        $priceHT = $this->factory->fromFloat(100.0);
        
        // Montant de TVA 20%
        $vatAmount = $this->calculator->increaseBy($priceHT, "20%")->subtract($priceHT);
        $vatPercent = $this->calculator->percentageOf($vatAmount, $priceHT);
        $this->assertEquals("20.00%", $vatPercent);
        
        $priceTTC = $this->calculator->increaseBy($priceHT, "20%");
        $this->assertEquals(120.0, $priceTTC->getFloat());
        
        // Calculer le montant de TVA
        $vatAmount = $priceTTC->subtract($priceHT);
        $this->assertEquals(20.0, $vatAmount->getFloat());
    }

    /**
     * Scénario: calcul de ratio
     */
    public function testRatioCalculation(): void
    {
        // Ratio 16:9
        $width = $this->factory->create(16, 1);
        $height = $this->factory->create(9, 1);
        $ratio = $width->divideBy($height);
        
        $this->assertEquals("16/9", $ratio->toString());
        $this->assertEqualsWithDelta(1.7777777, $ratio->getFloat(), 0.001);
        
        // Si largeur = 1920, calculer hauteur
        $actualWidth = $this->factory->create(1920, 1);
        $actualHeight = $actualWidth->divideBy($ratio);
        
        $this->assertEquals(1080.0, $actualHeight->getFloat());
    }

    /**
     * Scénario: vérification d'immutabilité dans une chaîne complexe
     */
    public function testImmutabilityInComplexChain(): void
    {
        $original = $this->factory->fromFloat(100.0);
        $originalValue = $original->getFloat();
        $originalString = $original->toString();
        
        // Effectuer de nombreuses opérations
        $result = $original
            ->add($this->factory->fromFloat(50.0))
            ->multiply($this->factory->create(2, 1))
            ->subtract($this->factory->fromFloat(100.0))
            ->divideBy($this->factory->create(5, 1));
        
        // Vérifier que l'original n'a pas changé
        $this->assertEquals($originalValue, $original->getFloat());
        $this->assertEquals($originalString, $original->toString());
        
        // Vérifier le résultat: (100+50)*2-100 = 300-100 = 200, 200/5 = 40
        $this->assertEquals(40.0, $result->getFloat());
    }

    /**
     * Test d'intégration avec PercentageCalculator pour tous ses calculs
     */
    public function testPercentageCalculatorIntegration(): void
    {
        $base = $this->factory->fromFloat(200.0);
        
        // toPercentage
        $halfBase = $this->factory->fromFloat(100.0);
        $percent = $this->calculator->percentageOf($halfBase, $base);
        $this->assertEquals("50.00%", $percent);
        
        // increaseBy
        $increased = $this->calculator->increaseBy($base, "25%");
        $this->assertEquals(250.0, $increased->getFloat());
        
        // decreaseBy
        $decreased = $this->calculator->decreaseBy($base, "25%");
        $this->assertEquals(150.0, $decreased->getFloat());
        
        // fromPercentage
        $fromPercent = $this->factory->fromPercentage("75%");
        $this->assertEquals(0.75, $fromPercent->getFloat());
    }

    /**
     * Test avec des nombres négatifs dans un workflow complet
     */
    public function testNegativeNumbersWorkflow(): void
    {
        // Température
        $temp = $this->factory->fromFloat(-10.0);
        
        // Augmentation de 50%
        $increased = $this->calculator->increaseBy($temp, "50%");
        // -10 + 50% = -10 + (-5) = -15
        $this->assertEquals(-15.0, $increased->getFloat());
        
        // Valeur absolue
        $abs = $increased->abs();
        $this->assertEquals(15.0, $abs->getFloat());
        
        // Négation
        $negated = $abs->negate();
        $this->assertEquals(-15.0, $negated->getFloat());
    }

    /**
     * Test comparaison après calculs complexes
     */
    public function testComparisonsAfterCalculations(): void
    {
        $num1 = $this->factory->fromFloat(100.0);
        $num2 = $this->factory->fromFloat(80.0);
        
        $result1 = $this->calculator->increaseBy($num1, "10%"); // 110
        $result2 = $this->calculator->increaseBy($num2, "25%"); // 100
        
        $this->assertTrue($result1->isGreaterThan($result2));
        $this->assertTrue($result2->isLessThan($result1));
        $this->assertFalse($result1->equals($result2));
        
        $this->assertEquals(1, $result1->compareTo($result2));
        $this->assertEquals(-1, $result2->compareTo($result1));
    }
}
