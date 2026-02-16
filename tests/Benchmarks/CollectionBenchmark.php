<?php

declare(strict_types=1);

namespace RationalNumber\Benchmarks;

use RationalNumber\RationalNumber;
use RationalNumber\Collection\RationalCollection;

/**
 * Benchmark collection operations
 */
class CollectionBenchmark
{
    /**
     * Benchmark sum operation on 1000 elements
     */
    public function benchCollectionSum1000(): array
    {
        $numbers = [];
        for ($i = 1; $i <= 1000; $i++) {
            $numbers[] = RationalNumber::fromFloat($i * 0.5);
        }
        $collection = new RationalCollection($numbers);
        
        $start = microtime(true);
        $sum = $collection->sum();
        $duration = microtime(true) - $start;
        
        return [
            'name' => 'Collection Sum (1000 elements)',
            'duration' => round($duration, 4),
            'result' => $sum->getFloat(),
        ];
    }

    /**
     * Benchmark average operation on 1000 elements
     */
    public function benchCollectionAverage1000(): array
    {
        $numbers = [];
        for ($i = 1; $i <= 1000; $i++) {
            $numbers[] = RationalNumber::fromFloat($i);
        }
        $collection = new RationalCollection($numbers);
        
        $start = microtime(true);
        $average = $collection->average();
        $duration = microtime(true) - $start;
        
        return [
            'name' => 'Collection Average (1000 elements)',
            'duration' => round($duration, 4),
            'result' => $average->getFloat(),
        ];
    }

    /**
     * Benchmark filter operation on 1000 elements
     */
    public function benchCollectionFilter1000(): array
    {
        $numbers = [];
        for ($i = 1; $i <= 1000; $i++) {
            $numbers[] = RationalNumber::fromFloat($i);
        }
        $collection = new RationalCollection($numbers);
        
        $start = microtime(true);
        $filtered = $collection->filter(fn($n) => $n->isGreaterThan(RationalNumber::fromFloat(500)));
        $duration = microtime(true) - $start;
        
        return [
            'name' => 'Collection Filter (1000 elements)',
            'duration' => round($duration, 4),
            'result_count' => $filtered->count(),
        ];
    }

    /**
     * Benchmark map operation on 1000 elements
     */
    public function benchCollectionMap1000(): array
    {
        $numbers = [];
        for ($i = 1; $i <= 1000; $i++) {
            $numbers[] = RationalNumber::fromFloat($i);
        }
        $collection = new RationalCollection($numbers);
        
        $start = microtime(true);
        $mapped = $collection->map(fn($n) => $n->multiply(new RationalNumber(2, 1)));
        $duration = microtime(true) - $start;
        
        return [
            'name' => 'Collection Map (1000 elements)',
            'duration' => round($duration, 4),
            'result_count' => $mapped->count(),
        ];
    }

    /**
     * Benchmark min/max operations on 1000 elements
     */
    public function benchCollectionMinMax1000(): array
    {
        $numbers = [];
        for ($i = 1; $i <= 1000; $i++) {
            $numbers[] = RationalNumber::fromFloat(rand(1, 10000) / 100);
        }
        $collection = new RationalCollection($numbers);
        
        $start = microtime(true);
        $min = $collection->min();
        $max = $collection->max();
        $duration = microtime(true) - $start;
        
        return [
            'name' => 'Collection Min/Max (1000 elements)',
            'duration' => round($duration, 4),
            'min' => $min->getFloat(),
            'max' => $max->getFloat(),
        ];
    }

    /**
     * Benchmark chained operations
     */
    public function benchCollectionChainedOperations(): array
    {
        $numbers = [];
        for ($i = 1; $i <= 1000; $i++) {
            $numbers[] = RationalNumber::fromFloat($i);
        }
        $collection = new RationalCollection($numbers);
        
        $start = microtime(true);
        
        // Chain filter -> map -> sum
        $result = $collection
            ->filter(fn($n) => $n->isGreaterThan(RationalNumber::fromFloat(100)))
            ->map(fn($n) => $n->increaseByPercentage('20%'))
            ->sum();
        
        $duration = microtime(true) - $start;
        
        return [
            'name' => 'Collection Chained Operations',
            'duration' => round($duration, 4),
            'result' => $result->getFloat(),
        ];
    }

    /**
     * Run all collection benchmarks
     */
    public function runAll(): array
    {
        return [
            $this->benchCollectionSum1000(),
            $this->benchCollectionAverage1000(),
            $this->benchCollectionFilter1000(),
            $this->benchCollectionMap1000(),
            $this->benchCollectionMinMax1000(),
            $this->benchCollectionChainedOperations(),
        ];
    }
}
