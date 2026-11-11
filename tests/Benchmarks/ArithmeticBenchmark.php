<?php

declare(strict_types=1);

namespace RationalNumber\Tests\Benchmarks;

use RationalNumber\RationalNumber;

/**
 * Benchmark basic arithmetic operations
 */
class ArithmeticBenchmark
{
    /**
     * Benchmark 10,000 addition operations
     */
    public function benchAddition10k(): array
    {
        $start = microtime(true);

        $result = RationalNumber::zero();
        $one = RationalNumber::one();

        for ($i = 0; $i < 10000; $i++) {
            $result = $result->add($one);
        }

        $duration = microtime(true) - $start;
        $opsPerSecond = 10000 / $duration;

        return [
            'name' => 'Addition (10k operations)',
            'duration' => round($duration, 4),
            'ops_per_second' => round($opsPerSecond, 2),
            'result' => $result->toString(),
        ];
    }

    /**
     * Benchmark 10,000 multiplication operations
     */
    public function benchMultiplication10k(): array
    {
        $start = microtime(true);

        // Fixed operands: accumulating the product (1.01^n = 101^n / 100^n)
        // would overflow 64-bit integers after a few iterations.
        $value = RationalNumber::fromFloat(123.45);
        $factor = RationalNumber::fromFloat(1.01);
        $result = $value;

        for ($i = 0; $i < 10000; $i++) {
            $result = $value->multiply($factor);
        }

        $duration = microtime(true) - $start;
        $opsPerSecond = 10000 / $duration;

        return [
            'name' => 'Multiplication (10k operations)',
            'duration' => round($duration, 4),
            'ops_per_second' => round($opsPerSecond, 2),
            'result' => $result->getFloat(),
        ];
    }

    /**
     * Benchmark 10,000 division operations
     */
    public function benchDivision10k(): array
    {
        $start = microtime(true);

        // Fixed operands: accumulating the quotient (2^n / 3^n) would overflow.
        $value = RationalNumber::fromFloat(10000);
        $divisor = new RationalNumber(3, 2); // 1.5
        $result = $value;

        for ($i = 0; $i < 10000; $i++) {
            $result = $value->divideBy($divisor);
        }

        $duration = microtime(true) - $start;
        $opsPerSecond = 10000 / $duration;

        return [
            'name' => 'Division (10k operations)',
            'duration' => round($duration, 4),
            'ops_per_second' => round($opsPerSecond, 2),
            'result' => $result->getFloat(),
        ];
    }

    /**
     * Benchmark 10,000 subtraction operations
     */
    public function benchSubtraction10k(): array
    {
        $start = microtime(true);

        $result = RationalNumber::fromFloat(10000);
        $one = RationalNumber::one();

        for ($i = 0; $i < 10000; $i++) {
            $result = $result->subtract($one);
        }

        $duration = microtime(true) - $start;
        $opsPerSecond = 10000 / $duration;

        return [
            'name' => 'Subtraction (10k operations)',
            'duration' => round($duration, 4),
            'ops_per_second' => round($opsPerSecond, 2),
            'result' => $result->toString(),
        ];
    }

    /**
     * Benchmark mixed arithmetic operations
     */
    public function benchMixedOperations(): array
    {
        $start = microtime(true);

        // Each iteration starts from the same operands: feeding the result back
        // (multiplying by 8/9 every round) would overflow 64-bit integers.
        $a = RationalNumber::fromFloat(10.5);
        $b = RationalNumber::fromFloat(2.5);
        $twoThirds = new RationalNumber(2, 3);
        $threeQuarters = new RationalNumber(3, 4);
        $result = RationalNumber::zero();

        for ($i = 0; $i < 2500; $i++) {
            $result = $a->add($b)
                ->subtract($b)
                ->multiply($twoThirds)
                ->divideBy($threeQuarters);
        }

        $duration = microtime(true) - $start;
        $opsPerSecond = 10000 / $duration; // 4 ops × 2500 iterations

        return [
            'name' => 'Mixed Operations (10k total)',
            'duration' => round($duration, 4),
            'ops_per_second' => round($opsPerSecond, 2),
            'result' => $result->getFloat(),
        ];
    }

    /**
     * Run all arithmetic benchmarks
     */
    public function runAll(): array
    {
        return [
            $this->benchAddition10k(),
            $this->benchSubtraction10k(),
            $this->benchMultiplication10k(),
            $this->benchDivision10k(),
            $this->benchMixedOperations(),
        ];
    }
}
