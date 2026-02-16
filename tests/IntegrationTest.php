<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\TestCase;
use RationalNumber\RationalNumber;
use RationalNumber\Calculator\PercentageCalculator;
use RationalNumber\Factory\RationalNumberFactory;

/**
 * Integration tests combining Factory, Calculator and RationalNumber
 * to validate interactions between components
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
     * Complete scenario: creation via factory, calculations, then percentage conversion
     */
    public function testCompleteWorkflow(): void
    {
        // Create a number via factory
        $price = $this->factory->fromFloat(100.0);
        
        // Apply a 20% discount via calculator
        $discount = $this->calculator->decreaseBy($price, "20%");
        
        // Verify the result
        $this->assertEquals(80.0, $discount->getFloat());
        $this->assertEquals("80/1", $discount->toString());
        
        // Add a 10% tax
        $withTax = $this->calculator->increaseBy($discount, "10%");
        
        // 80 + 10% = 88
        $this->assertEquals(88.0, $withTax->getFloat());
    }

    /**
     * Scenario: savings percentage calculation
     */
    public function testSavingsCalculation(): void
    {
        $originalPrice = $this->factory->fromFloat(150.0);
        $salePrice = $this->factory->fromFloat(120.0);
        
        // Calculate savings
        $savings = $originalPrice->subtract($salePrice);
        $this->assertEquals(30.0, $savings->getFloat());
        
        // Calculate the savings percentage
        $savingsPercent = $this->calculator->percentageOf($savings, $originalPrice);
        $this->assertEquals("20.00%", $savingsPercent);
    }

    /**
     * Scenario: compound interest calculation (simplified)
     */
    public function testCompoundInterest(): void
    {
        // Initial principal
        $principal = $this->factory->fromFloat(1000.0);
        
        // Annual interest rate: 5%
        $rate = "5%";
        
        // After 1 year
        $year1 = $this->calculator->increaseBy($principal, $rate);
        $this->assertEquals(1050.0, $year1->getFloat());
        
        // After 2 years
        $year2 = $this->calculator->increaseBy($year1, $rate);
        $this->assertEqualsWithDelta(1102.5, $year2->getFloat(), 0.01);
        
        // After 3 years
        $year3 = $this->calculator->increaseBy($year2, $rate);
        $this->assertEqualsWithDelta(1157.625, $year3->getFloat(), 0.01);
    }

    /**
     * Scenario: splitting a bill among multiple people
     */
    public function testBillSplitting(): void
    {
        // Total bill
        $totalBill = $this->factory->fromFloat(150.75);
        
        // 15% tip
        $tip = $this->calculator->increaseBy($totalBill, "15%");
        $this->assertEqualsWithDelta(173.3625, $tip->getFloat(), 0.01);
        
        // Divide among 4 people
        $fourPeople = $this->factory->create(4, 1);
        $perPerson = $tip->divideBy($fourPeople);
        
        $this->assertEqualsWithDelta(43.340625, $perPerson->getFloat(), 0.01);
    }

    /**
     * Scenario: recipe conversion (proportions)
     */
    public function testRecipeScaling(): void
    {
        // Original recipe for 4 people: 2/3 cup of flour
        $originalFlour = $this->factory->create(2, 3);
        
        // Scale for 6 people (factor 1.5)
        $scaleFactor = $this->factory->create(3, 2);
        $scaledFlour = $originalFlour->multiply($scaleFactor);
        
        // 2/3 * 3/2 = 6/6 = 1
        $this->assertEquals("1/1", $scaledFlour->toString());
        $this->assertEquals(1.0, $scaledFlour->getFloat());
        $this->assertTrue($scaledFlour->isInteger());
    }

    /**
     * Scenario: weighted average calculation
     */
    public function testWeightedAverage(): void
    {
        // Grades with weights
        // Grade 1: 85/100 with 30% weight
        $note1 = $this->factory->create(85, 100);
        $weight1 = $this->factory->fromPercentage("30%");
        
        // Grade 2: 90/100 with 70% weight
        $note2 = $this->factory->create(90, 100);
        $weight2 = $this->factory->fromPercentage("70%");
        
        // Weighted average
        $weighted1 = $note1->multiply($weight1);
        $weighted2 = $note2->multiply($weight2);
        $average = $weighted1->add($weighted2);
        
        // 0.85 * 0.30 + 0.90 * 0.70 = 0.255 + 0.630 = 0.885
        $this->assertEqualsWithDelta(0.885, $average->getFloat(), 0.001);
        $this->assertEquals("177/200", $average->toString());
    }

    /**
     * Scenario: comparing products with different discounts
     */
    public function testProductComparison(): void
    {
        // Product A: 100€ with 20% discount
        $productA = $this->factory->fromFloat(100.0);
        $priceA = $this->calculator->decreaseBy($productA, "20%");
        
        // Product B: 90€ with 10% discount
        $productB = $this->factory->fromFloat(90.0);
        $priceB = $this->calculator->decreaseBy($productB, "10%");
        
        // Compare final prices
        $this->assertEquals(80.0, $priceA->getFloat());
        $this->assertEquals(81.0, $priceB->getFloat());
        
        $this->assertTrue($priceA->isLessThan($priceB));
        $this->assertFalse($priceA->equals($priceB));
    }

    /**
     * Scenario: conversion between different formats
     */
    public function testFormatConversions(): void
    {
        // Start from a float
        $value = $this->factory->fromFloat(0.375);
        
        // Convert to percentage
        $percentage = $this->calculator->toPercentage($value, 1);
        $this->assertEquals("37.5%", $percentage);
        
        // Convert back from percentage
        $backToRational = $this->factory->fromPercentage($percentage);
        
        // Verify we get back the original value
        $this->assertEqualsWithDelta($value->getFloat(), $backToRational->getFloat(), 0.001);
    }

    /**
     * Scenario: cascading calculations with factory methods
     */
    public function testCascadingCalculations(): void
    {
        // Start from zero
        $result = $this->factory->zero();
        
        // Add values
        $result = $result->add($this->factory->fromFloat(10.5));
        $result = $result->add($this->factory->fromFloat(20.25));
        $result = $result->add($this->factory->fromFloat(5.0));
        
        // Total should be 35.75
        $this->assertEqualsWithDelta(35.75, $result->getFloat(), 0.01);
        
        // Multiply by 2
        $result = $result->multiply($this->factory->create(2, 1));
        $this->assertEqualsWithDelta(71.5, $result->getFloat(), 0.01);
    }

    /**
     * Scenario: using factory constants (zero, one)
     */
    public function testFactoryConstants(): void
    {
        $zero = $this->factory->zero();
        $one = $this->factory->one();
        
        $number = $this->factory->create(42, 7);
        
        // Properties of identity elements
        $this->assertTrue($number->add($zero)->equals($number));
        $this->assertTrue($number->multiply($one)->equals($number));
        $this->assertTrue($number->subtract($zero)->equals($number));
        $this->assertTrue($number->divideBy($one)->equals($number));
    }

    /**
     * Scenario: VAT calculation with different rates
     */
    public function testVATCalculation(): void
    {
        $priceHT = $this->factory->fromFloat(100.0);
        
        // VAT amount 20%
        $vatAmount = $this->calculator->increaseBy($priceHT, "20%")->subtract($priceHT);
        $vatPercent = $this->calculator->percentageOf($vatAmount, $priceHT);
        $this->assertEquals("20.00%", $vatPercent);
        
        $priceTTC = $this->calculator->increaseBy($priceHT, "20%");
        $this->assertEquals(120.0, $priceTTC->getFloat());
        
        // Calculate the VAT amount
        $vatAmount = $priceTTC->subtract($priceHT);
        $this->assertEquals(20.0, $vatAmount->getFloat());
    }

    /**
     * Scenario: ratio calculation
     */
    public function testRatioCalculation(): void
    {
        // Ratio 16:9
        $width = $this->factory->create(16, 1);
        $height = $this->factory->create(9, 1);
        $ratio = $width->divideBy($height);
        
        $this->assertEquals("16/9", $ratio->toString());
        $this->assertEqualsWithDelta(1.7777777, $ratio->getFloat(), 0.001);
        
        // If width = 1920, calculate height
        $actualWidth = $this->factory->create(1920, 1);
        $actualHeight = $actualWidth->divideBy($ratio);
        
        $this->assertEquals(1080.0, $actualHeight->getFloat());
    }

    /**
     * Scenario: immutability verification in a complex chain
     */
    public function testImmutabilityInComplexChain(): void
    {
        $original = $this->factory->fromFloat(100.0);
        $originalValue = $original->getFloat();
        $originalString = $original->toString();
        
        // Perform many operations
        $result = $original
            ->add($this->factory->fromFloat(50.0))
            ->multiply($this->factory->create(2, 1))
            ->subtract($this->factory->fromFloat(100.0))
            ->divideBy($this->factory->create(5, 1));
        
        // Verify that the original hasn't changed
        $this->assertEquals($originalValue, $original->getFloat());
        $this->assertEquals($originalString, $original->toString());
        
        // Verify the result: (100+50)*2-100 = 300-100 = 200, 200/5 = 40
        $this->assertEquals(40.0, $result->getFloat());
    }

    /**
     * Integration test with PercentageCalculator for all its calculations
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
     * Test with negative numbers in a complete workflow
     */
    public function testNegativeNumbersWorkflow(): void
    {
        // Temperature
        $temp = $this->factory->fromFloat(-10.0);
        
        // 50% increase
        $increased = $this->calculator->increaseBy($temp, "50%");
        // -10 + 50% = -10 + (-5) = -15
        $this->assertEquals(-15.0, $increased->getFloat());
        
        // Absolute value
        $abs = $increased->abs();
        $this->assertEquals(15.0, $abs->getFloat());
        
        // Negation
        $negated = $abs->negate();
        $this->assertEquals(-15.0, $negated->getFloat());
    }

    /**
     * Test comparison after complex calculations
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
