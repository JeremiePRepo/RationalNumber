<?php

declare(strict_types=1);

namespace RationalNumber\Benchmarks;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Main benchmark runner
 */
class BenchmarkRunner
{
    private array $results = [];

    public function run(): void
    {
        echo "==========================================================\n";
        echo "   RationalNumber Performance Benchmarks\n";
        echo "==========================================================\n\n";
        echo "PHP Version: " . PHP_VERSION . "\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

        // Run arithmetic benchmarks
        echo "─────────────────────────────────────────────────────────\n";
        echo " Arithmetic Operations\n";
        echo "─────────────────────────────────────────────────────────\n";
        $arithmeticBench = new ArithmeticBenchmark();
        $this->runBenchmarkGroup($arithmeticBench->runAll());

        // Run complex operations benchmarks
        echo "\n─────────────────────────────────────────────────────────\n";
        echo " Complex Operations\n";
        echo "─────────────────────────────────────────────────────────\n";
        $complexBench = new ComplexOperationsBenchmark();
        $this->runBenchmarkGroup($complexBench->runAll());

        // Run collection benchmarks
        echo "\n─────────────────────────────────────────────────────────\n";
        echo " Collection Operations\n";
        echo "─────────────────────────────────────────────────────────\n";
        $collectionBench = new CollectionBenchmark();
        $this->runBenchmarkGroup($collectionBench->runAll());

        // Summary
        echo "\n==========================================================\n";
        echo " Summary\n";
        echo "==========================================================\n";
        $this->printSummary();

        // Save results
        $this->saveResults();
    }

    private function runBenchmarkGroup(array $benchmarks): void
    {
        foreach ($benchmarks as $result) {
            $this->results[] = $result;
            $this->printResult($result);
        }
    }

    private function printResult(array $result): void
    {
        echo sprintf(
            "%-45s %8s s  %12s ops/s\n",
            $result['name'],
            $result['duration'],
            isset($result['ops_per_second']) ? number_format($result['ops_per_second'], 0) : 'N/A'
        );
    }

    private function printSummary(): void
    {
        $totalDuration = array_sum(array_column($this->results, 'duration'));
        $totalBenchmarks = count($this->results);

        echo "\nTotal Benchmarks: {$totalBenchmarks}\n";
        echo "Total Duration: " . round($totalDuration, 4) . "s\n";
        echo "Average Duration: " . round($totalDuration / $totalBenchmarks, 4) . "s\n";

        // Find fastest and slowest
        usort($this->results, fn($a, $b) => $a['duration'] <=> $b['duration']);
        $fastest = $this->results[0];
        $slowest = $this->results[count($this->results) - 1];

        echo "\nFastest: {$fastest['name']} (" . $fastest['duration'] . "s)\n";
        echo "Slowest: {$slowest['name']} (" . $slowest['duration'] . "s)\n";
    }

    private function saveResults(): void
    {
        $markdown = $this->generateMarkdownReport();
        $filename = 'benchmark-results-' . date('Y-m-d-His') . '.md';
        file_put_contents(__DIR__ . '/../../' . $filename, $markdown);
        
        echo "\n✓ Results saved to: {$filename}\n";
    }

    private function generateMarkdownReport(): string
    {
        $md = "# RationalNumber Benchmark Results\n\n";
        $md .= "**Date:** " . date('Y-m-d H:i:s') . "\n";
        $md .= "**PHP Version:** " . PHP_VERSION . "\n\n";

        $md .= "## Results\n\n";
        $md .= "| Benchmark | Duration (s) | Ops/Second |\n";
        $md .= "|-----------|--------------|------------|\n";

        foreach ($this->results as $result) {
            $opsPerSecond = isset($result['ops_per_second']) 
                ? number_format($result['ops_per_second'], 0) 
                : 'N/A';
            $md .= sprintf(
                "| %s | %.4f | %s |\n",
                $result['name'],
                $result['duration'],
                $opsPerSecond
            );
        }

        $md .= "\n## Summary\n\n";
        $totalDuration = array_sum(array_column($this->results, 'duration'));
        $md .= "- **Total Benchmarks:** " . count($this->results) . "\n";
        $md .= "- **Total Duration:** " . round($totalDuration, 4) . "s\n";
        $md .= "- **Average Duration:** " . round($totalDuration / count($this->results), 4) . "s\n";

        return $md;
    }
}

// Run benchmarks if executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $runner = new BenchmarkRunner();
    $runner->run();
}
