<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\TestCase;
use RationalNumber\RationalNumber;

/**
 * Integration tests of realistic RationalNumber workflows
 * (creation, arithmetic, percentages and conversions)
 */
class IntegrationTest extends TestCase
{
    /**
     * Complete scenario: creation, calculations, then percentage conversion
     */
    public function testCompleteWorkflow(): void
    {
        // Create a number
        $price = RationalNumber::fromFloat(100.0);

        // Apply a 20% discount
        $discount = $price->decreaseByPercentage("20%");

        // Verify the result
        $this->assertEquals(80.0, $discount->getFloat());
        $this->assertEquals("80/1", $discount->toString());

        // Add a 10% tax
        $withTax = $discount->increaseByPercentage("10%");

        // 80 + 10% = 88
        $this->assertEquals(88.0, $withTax->getFloat());
    }

    /**
     * Scenario: savings percentage calculation
     */
    public function testSavingsCalculation(): void
    {
        $originalPrice = RationalNumber::fromFloat(150.0);
        $salePrice = RationalNumber::fromFloat(120.0);

        // Calculate savings
        $savings = $originalPrice->subtract($salePrice);
        $this->assertEquals(30.0, $savings->getFloat());

        // Calculate the savings percentage
        $savingsPercent = $savings->divideBy($originalPrice)->toPercentage();
        $this->assertEquals("20.00%", $savingsPercent);
    }

    /**
     * Scenario: compound interest calculation (simplified)
     */
    public function testCompoundInterest(): void
    {
        // Initial principal
        $principal = RationalNumber::fromFloat(1000.0);

        // Annual interest rate: 5%
        $rate = "5%";

        // After 1 year
        $year1 = $principal->increaseByPercentage($rate);
        $this->assertEquals(1050.0, $year1->getFloat());

        // After 2 years
        $year2 = $year1->increaseByPercentage($rate);
        $this->assertEqualsWithDelta(1102.5, $year2->getFloat(), 0.01);

        // After 3 years
        $year3 = $year2->increaseByPercentage($rate);
        $this->assertEqualsWithDelta(1157.625, $year3->getFloat(), 0.01);
    }

    /**
     * Scenario: splitting a bill among multiple people
     */
    public function testBillSplitting(): void
    {
        // Total bill
        $totalBill = RationalNumber::fromFloat(150.75);

        // 15% tip
        $tip = $totalBill->increaseByPercentage("15%");
        $this->assertEqualsWithDelta(173.3625, $tip->getFloat(), 0.01);

        // Divide among 4 people
        $fourPeople = new RationalNumber(4, 1);
        $perPerson = $tip->divideBy($fourPeople);

        $this->assertEqualsWithDelta(43.340625, $perPerson->getFloat(), 0.01);
    }

    /**
     * Scenario: recipe conversion (proportions)
     */
    public function testRecipeScaling(): void
    {
        // Original recipe for 4 people: 2/3 cup of flour
        $originalFlour = new RationalNumber(2, 3);

        // Scale for 6 people (factor 1.5)
        $scaleFactor = new RationalNumber(3, 2);
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
        $note1 = new RationalNumber(85, 100);
        $weight1 = RationalNumber::fromPercentage("30%");

        // Grade 2: 90/100 with 70% weight
        $note2 = new RationalNumber(90, 100);
        $weight2 = RationalNumber::fromPercentage("70%");

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
        $productA = RationalNumber::fromFloat(100.0);
        $priceA = $productA->decreaseByPercentage("20%");

        // Product B: 90€ with 10% discount
        $productB = RationalNumber::fromFloat(90.0);
        $priceB = $productB->decreaseByPercentage("10%");

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
        $value = RationalNumber::fromFloat(0.375);

        // Convert to percentage
        $percentage = $value->toPercentage(1);
        $this->assertEquals("37.5%", $percentage);

        // Convert back from percentage
        $backToRational = RationalNumber::fromPercentage($percentage);

        // Verify we get back the original value
        $this->assertEqualsWithDelta($value->getFloat(), $backToRational->getFloat(), 0.001);
    }

    /**
     * Scenario: cascading calculations with static constructors
     */
    public function testCascadingCalculations(): void
    {
        // Start from zero
        $result = RationalNumber::zero();

        // Add values
        $result = $result->add(RationalNumber::fromFloat(10.5));
        $result = $result->add(RationalNumber::fromFloat(20.25));
        $result = $result->add(RationalNumber::fromFloat(5.0));

        // Total should be 35.75
        $this->assertEqualsWithDelta(35.75, $result->getFloat(), 0.01);

        // Multiply by 2
        $result = $result->multiply(new RationalNumber(2, 1));
        $this->assertEqualsWithDelta(71.5, $result->getFloat(), 0.01);
    }

    /**
     * Scenario: using the zero() and one() constants
     */
    public function testZeroAndOneConstants(): void
    {
        $zero = RationalNumber::zero();
        $one = RationalNumber::one();

        $number = new RationalNumber(42, 7);

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
        $priceHT = RationalNumber::fromFloat(100.0);

        // VAT amount 20%
        $vatAmount = $priceHT->increaseByPercentage("20%")->subtract($priceHT);
        $vatPercent = $vatAmount->divideBy($priceHT)->toPercentage();
        $this->assertEquals("20.00%", $vatPercent);

        $priceTTC = $priceHT->increaseByPercentage("20%");
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
        $width = new RationalNumber(16, 1);
        $height = new RationalNumber(9, 1);
        $ratio = $width->divideBy($height);

        $this->assertEquals("16/9", $ratio->toString());
        $this->assertEqualsWithDelta(1.7777777, $ratio->getFloat(), 0.001);

        // If width = 1920, calculate height
        $actualWidth = new RationalNumber(1920, 1);
        $actualHeight = $actualWidth->divideBy($ratio);

        $this->assertEquals(1080.0, $actualHeight->getFloat());
    }

    /**
     * Scenario: immutability verification in a complex chain
     */
    public function testImmutabilityInComplexChain(): void
    {
        $original = RationalNumber::fromFloat(100.0);
        $originalValue = $original->getFloat();
        $originalString = $original->toString();

        // Perform many operations
        $result = $original
            ->add(RationalNumber::fromFloat(50.0))
            ->multiply(new RationalNumber(2, 1))
            ->subtract(RationalNumber::fromFloat(100.0))
            ->divideBy(new RationalNumber(5, 1));

        // Verify that the original hasn't changed
        $this->assertEquals($originalValue, $original->getFloat());
        $this->assertEquals($originalString, $original->toString());

        // Verify the result: (100+50)*2-100 = 300-100 = 200, 200/5 = 40
        $this->assertEquals(40.0, $result->getFloat());
    }

    /**
     * Integration test of every percentage calculation
     */
    public function testPercentageIntegration(): void
    {
        $base = RationalNumber::fromFloat(200.0);

        // toPercentage
        $halfBase = RationalNumber::fromFloat(100.0);
        $percent = $halfBase->divideBy($base)->toPercentage();
        $this->assertEquals("50.00%", $percent);

        // increaseBy
        $increased = $base->increaseByPercentage("25%");
        $this->assertEquals(250.0, $increased->getFloat());

        // decreaseBy
        $decreased = $base->decreaseByPercentage("25%");
        $this->assertEquals(150.0, $decreased->getFloat());

        // fromPercentage
        $fromPercent = RationalNumber::fromPercentage("75%");
        $this->assertEquals(0.75, $fromPercent->getFloat());
    }

    /**
     * Test with negative numbers in a complete workflow
     */
    public function testNegativeNumbersWorkflow(): void
    {
        // Temperature
        $temp = RationalNumber::fromFloat(-10.0);

        // 50% increase
        $increased = $temp->increaseByPercentage("50%");
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
        $num1 = RationalNumber::fromFloat(100.0);
        $num2 = RationalNumber::fromFloat(80.0);

        $result1 = $num1->increaseByPercentage("10%"); // 110
        $result2 = $num2->increaseByPercentage("25%"); // 100

        $this->assertTrue($result1->isGreaterThan($result2));
        $this->assertTrue($result2->isLessThan($result1));
        $this->assertFalse($result1->equals($result2));

        $this->assertEquals(1, $result1->compareTo($result2));
        $this->assertEquals(-1, $result2->compareTo($result1));
    }
}
