<?php

declare(strict_types=1);

namespace RationalNumber\Tests;

use PHPUnit\Framework\TestCase;
use RationalNumber\RationalNumber;
use InvalidArgumentException;

/**
 * Test suite for serialization operations.
 * 
 * Tests jsonSerialize(), toArray(), fromArray(), and fromJson() methods.
 */
class SerializationTest extends TestCase
{
    // ========== jsonSerialize() Tests ==========

    public function testJsonSerializeFormat(): void
    {
        $number = new RationalNumber(3, 4);
        $serialized = $number->jsonSerialize();
        
        $this->assertIsArray($serialized);
        $this->assertArrayHasKey('numerator', $serialized);
        $this->assertArrayHasKey('denominator', $serialized);
        $this->assertArrayHasKey('float', $serialized);
        $this->assertArrayHasKey('string', $serialized);
        
        $this->assertEquals(3, $serialized['numerator']);
        $this->assertEquals(4, $serialized['denominator']);
        $this->assertEqualsWithDelta(0.75, $serialized['float'], 1e-12);
        $this->assertEquals('3/4', $serialized['string']);
    }

    public function testJsonSerializeWithInteger(): void
    {
        $number = RationalNumber::fromFloat(5);
        $serialized = $number->jsonSerialize();
        
        $this->assertEquals(5, $serialized['numerator']);
        $this->assertEquals(1, $serialized['denominator']);
        $this->assertEqualsWithDelta(5.0, $serialized['float'], 1e-12);
    }

    public function testJsonSerializeWithNegative(): void
    {
        $number = new RationalNumber(-7, 3);
        $serialized = $number->jsonSerialize();
        
        $this->assertEquals(-7, $serialized['numerator']);
        $this->assertEquals(3, $serialized['denominator']);
        $this->assertEqualsWithDelta(-2.333333, $serialized['float'], 0.0001);
    }

    public function testJsonEncodeIntegration(): void
    {
        $number = new RationalNumber(1, 2);
        $json = json_encode($number);
        
        $this->assertJson($json);
        
        $decoded = json_decode($json, true);
        $this->assertEquals(1, $decoded['numerator']);
        $this->assertEquals(2, $decoded['denominator']);
    }

    // ========== toArray() Tests ==========

    public function testToArrayFormat(): void
    {
        $number = new RationalNumber(5, 8);
        $array = $number->toArray();
        
        $this->assertIsArray($array);
        $this->assertCount(2, $array);
        $this->assertArrayHasKey('numerator', $array);
        $this->assertArrayHasKey('denominator', $array);
        
        $this->assertEquals(5, $array['numerator']);
        $this->assertEquals(8, $array['denominator']);
    }

    public function testToArrayWithInteger(): void
    {
        $number = RationalNumber::fromFloat(10);
        $array = $number->toArray();
        
        $this->assertEquals(10, $array['numerator']);
        $this->assertEquals(1, $array['denominator']);
    }

    public function testToArrayWithZero(): void
    {
        $number = RationalNumber::zero();
        $array = $number->toArray();
        
        $this->assertEquals(0, $array['numerator']);
        $this->assertEquals(1, $array['denominator']);
    }

    // ========== fromArray() Tests ==========

    public function testFromArrayBasic(): void
    {
        $data = ['numerator' => 3, 'denominator' => 5];
        $number = RationalNumber::fromArray($data);
        
        $this->assertEquals("3/5", $number->toString());
        $this->assertEqualsWithDelta(0.6, $number->getFloat(), 1e-12);
    }

    public function testFromArrayWithInteger(): void
    {
        $data = ['numerator' => 7, 'denominator' => 1];
        $number = RationalNumber::fromArray($data);
        
        $this->assertEquals("7/1", $number->toString());
        $this->assertTrue($number->isInteger());
    }

    public function testFromArrayNormalization(): void
    {
        $data = ['numerator' => 6, 'denominator' => 9];
        $number = RationalNumber::fromArray($data);
        
        // Should be normalized to 2/3
        $this->assertEquals("2/3", $number->toString());
    }

    public function testFromArrayMissingNumerator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Array must contain 'numerator' and 'denominator' keys");
        
        $data = ['denominator' => 5];
        RationalNumber::fromArray($data);
    }

    public function testFromArrayMissingDenominator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Array must contain 'numerator' and 'denominator' keys");
        
        $data = ['numerator' => 3];
        RationalNumber::fromArray($data);
    }

    public function testFromArrayInvalidNumerator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Numerator must be an integer or numeric value");
        
        $data = ['numerator' => 'invalid', 'denominator' => 5];
        RationalNumber::fromArray($data);
    }

    public function testFromArrayInvalidDenominator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Denominator must be an integer or numeric value");
        
        $data = ['numerator' => 3, 'denominator' => 'invalid'];
        RationalNumber::fromArray($data);
    }

    // ========== fromJson() Tests ==========

    public function testFromJsonBasic(): void
    {
        $json = '{"numerator": 7, "denominator": 11}';
        $number = RationalNumber::fromJson($json);
        
        $this->assertEquals("7/11", $number->toString());
        $this->assertEqualsWithDelta(0.636363, $number->getFloat(), 0.0001);
    }

    public function testFromJsonWithExtraFields(): void
    {
        $json = '{"numerator": 2, "denominator": 3, "float": 0.666, "string": "2/3"}';
        $number = RationalNumber::fromJson($json);
        
        // Extra fields should be ignored
        $this->assertEquals("2/3", $number->toString());
    }

    public function testFromJsonInvalidJson(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid JSON");
        
        $json = '{invalid json}';
        RationalNumber::fromJson($json);
    }

    public function testFromJsonNotAnArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("JSON must decode to an array");
        
        $json = '"just a string"';
        RationalNumber::fromJson($json);
    }

    public function testFromJsonMissingKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Array must contain 'numerator' and 'denominator' keys");
        
        $json = '{"numerator": 5}';
        RationalNumber::fromJson($json);
    }

    // ========== Round-trip Tests ==========

    public function testToArrayFromArrayRoundTrip(): void
    {
        $original = new RationalNumber(9, 13);
        $array = $original->toArray();
        $reconstructed = RationalNumber::fromArray($array);
        
        $this->assertTrue($original->equals($reconstructed));
        $this->assertEquals($original->toString(), $reconstructed->toString());
    }

    public function testJsonEncodeDecodeRoundTrip(): void
    {
        $original = new RationalNumber(5, 7);
        $json = json_encode($original);
        $reconstructed = RationalNumber::fromJson($json);
        
        $this->assertTrue($original->equals($reconstructed));
    }

    public function testMultipleNumbersRoundTrip(): void
    {
        $numbers = [
            new RationalNumber(1, 2),
            new RationalNumber(3, 4),
            new RationalNumber(5, 6)
        ];
        
        $arrays = array_map(fn($n) => $n->toArray(), $numbers);
        $reconstructed = array_map(fn($a) => RationalNumber::fromArray($a), $arrays);
        
        for ($i = 0; $i < count($numbers); $i++) {
            $this->assertTrue($numbers[$i]->equals($reconstructed[$i]));
        }
    }

    // ========== Integration Tests ==========

    public function testCacheStorageScenario(): void
    {
        // Simulate storing in cache
        $price = RationalNumber::fromFloat(99.99);
        $cached = $price->toArray();
        
        // Simulate retrieving from cache
        $retrieved = RationalNumber::fromArray($cached);
        
        $this->assertTrue($price->equals($retrieved));
        $this->assertEqualsWithDelta(99.99, $retrieved->getFloat(), 0.01);
    }

    public function testApiResponseScenario(): void
    {
        // API response with multiple prices
        $response = [
            'subtotal' => RationalNumber::fromFloat(150.00),
            'tax' => RationalNumber::fromFloat(30.00),
            'total' => RationalNumber::fromFloat(180.00)
        ];
        
        $encoded = json_encode($response);
        $this->assertJson($encoded);
        
        $decoded = json_decode($encoded, true);
        $total = RationalNumber::fromJson(json_encode($decoded['total']));
        
        $this->assertEqualsWithDelta(180.00, $total->getFloat(), 1e-12);
    }

    public function testSerializationPreservesReduction(): void
    {
        // Create unreduced fraction
        $number = new RationalNumber(10, 15);
        
        // Should be automatically reduced to 2/3
        $this->assertEquals("2/3", $number->toString());
        
        // Serialize and deserialize
        $array = $number->toArray();
        $reconstructed = RationalNumber::fromArray($array);
        
        // Should still be reduced
        $this->assertEquals("2/3", $reconstructed->toString());
    }

    public function testNumericStringInArray(): void
    {
        // Some systems might store as numeric strings
        $data = ['numerator' => '15', 'denominator' => '20'];
        $number = RationalNumber::fromArray($data);
        
        // Should parse correctly and reduce to 3/4
        $this->assertEquals("3/4", $number->toString());
    }
}
