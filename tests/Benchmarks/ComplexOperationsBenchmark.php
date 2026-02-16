<?php

declare(strict_types=1);

namespace RationalNumber\Benchmarks;

use RationalNumber\RationalNumber;

/**
 * Benchmark complex mathematical operations
 */
class ComplexOperationsBenchmark
{
    /**
     * Benchmark GCD calculation on large numbers
     */
    public function benchGCDLargeNumbers(): array
    {
        $start = microtime(true);
        
        // Test GCD on various large number combinations
        for ($i = 0; $i < 1000; $i++) {
            $a = 987654321 - ($i * 1000);
            $b = 123456789 + ($i * 500);
            new RationalNumber($a, $b); // Constructor calls GCD and normalize
        }
        
        $duration = microtime(true) - $start;
        $opsPerSecond = 1000 / $duration;
        
        return [
            'name' => 'GCD Large Numbers (1k operations)',
            'duration' => round($duration, 4),
            'ops_per_second' => round($opsPerSecond, 2),
        ];
    }

    /**
     * Benchmark power operations
     */
    public function benchPowerOperations(): array
    {
        $start = microtime(true);
        
        $base = RationalNumber::fromFloat(1.05);
        
        for ($i = 0; $i < 1000; $i++) {
            $result = $base->pow(10); // Compound interest calculation
        }
        
        $duration = microtime(true) - $start;
        $opsPerSecond = 1000 / $duration;
        
        return [
            'name' => 'Power Operations (1k × 10^10)',
            'duration' => round($duration, 4),
            'ops_per_second' => round($opsPerSecond, 2),
        ];
    }

    /**
     * Benchmark square root calculations
     */
    public function benchSqrtOperations(): array
    {
        $start = microtime(true);
        
        for ($i = 1; $i <= 100; $i++) {
            $number = RationalNumber::fromFloat($i);
            $sqrt = $number->sqrt(10); // 10 iterations Newton-Raphson
        }
        
        $duration = microtime(true) - $start;
        $opsPerSecond = 100 / $duration;
        
        return [
            'name' => 'Square Root (100 numbers, 10 iterations)',
            'duration' => round($duration, 4),
            'ops_per_second' => round($opsPerSecond, 2),
        ];
    }

    /**
     * Benchmark comparison operations
     */
    public function benchComparisonOperations(): array
    {
        $start = microtime(true);
        
        $a = RationalNumber::fromFloat(10.5);
        $b = RationalNumber::fromFloat(7.25);
        
        for ($i = 0; $i < 10000; $i++) {
            $a->isGreaterThan($b);
            $a->isLessThan($b);
            $a->equals($b);
            $a->compareTo($b);
        }
        
        $duration = microtime(true) - $start;
        $opsPerSecond = 40000 / $duration; // 4 comparisons × 10k
        
        return [
            'name' => 'Comparisons (40k operations)',
            'duration' => round($duration, 4),
            'ops_per_second' => round($opsPerSecond, 2),
        ];
    }

    /**
     * Benchmark rounding operations
     */
    public function benchRoundingOperations(): array
    {
        $start = microtime(true);
        
        for ($i = 0; $i < 10000; $i++) {
            $number = RationalNumber::fromFloat(123.456789 + $i * 0.001);
            $number->round(100);  // Round to cents
            $number->floor();
            $number->ceil();
        }
        
        $duration = microtime(true) - $start;
        $opsPerSecond = 30000 / $duration; // 3 operations × 10k
        
        return [
            'name' => 'Rounding Operations (30k total)',
            'duration' => round($duration, 4),
            'ops_per_second' => round($opsPerSecond, 2),
        ];
    }

    /**
     * Benchmark percentage operations
     */
    public function benchPercentageOperations(): array
    {
        $start = microtime(true);
        
        $base = RationalNumber::fromFloat(100);
        
        for ($i = 0; $i < 10000; $i++) {
            $base->increaseByPercentage('20%');
            $base->decreaseByPercentage('15%');
            $base->percentageOf('50%');
        }
        
        $duration = microtime(true) - $start;
        $opsPerSecond = 30000 / $duration; // 3 operations × 10k
        
        return [
            'name' => 'Percentage Operations (30k total)',
            'duration' => round($duration, 4),
            'ops_per_second' => round($opsPerSecond, 2),
        ];
    }

    /**
     * Run all complex operation benchmarks
     */
    public function runAll(): array
    {
        return [
            $this->benchGCDLargeNumbers(),
            $this->benchPowerOperations(),
            $this->benchSqrtOperations(),
            $this->benchComparisonOperations(),
            $this->benchRoundingOperations(),
            $this->benchPercentageOperations(),
        ];
    }
}
