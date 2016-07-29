<?php

date_default_timezone_set('UTC');

/**
 * Allow benchmarking to use as much memory as it needs, if available.
 */
ini_set('memory_limit', -1);

if ( ! extension_loaded('ds')) {
    die('ds extension either not installed or not enabled');
}

/**
 *
 */
$config = require "config.php";

/**
 * Benchmark schedule: comment out those that should not be run.
 */
$schedule = require "schedule.php";

/**
 * Benchmarks the total time taken, where snapshots are taken at exponential
 * intervals and averaged over multiple trials.
 */
function benchmark($type, $range, $setup, $tick, $reset)
{
    $results = [
        'm' => [], // Memory usage
        't' => [], // Time taken
    ];

    list($initial, $total) = $range;

    $initial = max(1, $initial);

    switch ($type) {

        // Expontential benchmark performs a single task at an expontentially
        // increasing checkpoint.
        case EXPONENTIAL:
            $checkpoints = [];
            for ($i = $initial; $i <= $total; $i *= 2) {
                $checkpoints[] = $i;
            }
            break;

        // Incremental benchmark performs a task at every tick in the range.
        case INCREMENTAL:
            $checkpoints = range(
                $initial, $total, max(1, 2 ** intval(round(log($total / SAMPLES, 2))))
            );
            break;
    }

    // Initialize results
    foreach ($checkpoints as $checkpoint) {
        $results['m'][$checkpoint] = 0;
        $results['t'][$checkpoint] = 0;
    }

    switch ($type) {

        case EXPONENTIAL:
            foreach ($checkpoints as $checkpoint) {

                echo '.';

                for ($trial = 0; $trial < TRIALS; $trial++) {

                    // Just to make sure that resources are cleared beforehand.
                    $reset();
                    gc_collect_cycles();

                    srand(1);

                    $memory = memory_get_usage();
                    $setup($checkpoint);

                    $time = microtime(true);
                    $tick($checkpoint);

                    //
                    $results['t'][$checkpoint] += microtime(true) - $time;
                    $results['m'][$checkpoint] += memory_get_usage() - $memory;
                }

                $reset();
                gc_collect_cycles();
            }
            break;

        case INCREMENTAL:
            for ($trial = 0; $trial < TRIALS; $trial++) {

                echo '.';

                $reset();
                gc_collect_cycles();

                srand(1);

                $memory = memory_get_usage();
                $setup($total);

                $time = microtime(true);

                for ($i = 1, $c = 0; $i <= $total; $i++) {

                    $tick($i - 1);

                    if ($i === $checkpoints[$c]) {

                        $results['t'][$i] += microtime(true) - $time;
                        $results['m'][$i] += memory_get_usage() - $memory;

                        if (++$c === count($checkpoints)) {
                            break;
                        }
                    }
                }
            }
            break;
    }

    // Determine the average memory usage across all trials.
    foreach ($results['m'] as &$result) {
        $result /= TRIALS;
    }

    // Determine the average runtime across all trials.
    foreach ($results['t'] as &$result) {
        $result /= TRIALS;
    }

    return $results;
}

$results = [];

echo "Generating benchmarks...\n";

// Run each benchmark in the schedule.
foreach ($schedule as $name => $batches) {

    $results[$name] = $results[$name] ?? [];

    foreach ($batches as $batchId => $batch) {

        $results[$name][$batchId] = [];

        list($range, $candidates) = $batch;

        if (count($candidates) === 0) {
            continue;
        }

        echo "\n\t$name:";

        foreach ($candidates as $candidate) {

            echo "\n\t\t$candidate ";

            list($type, $functions) = $config[$name];
            $functions = $functions[$candidate];

            // Benchmark this task according to its setup, tick and reset functions.
            $results[$name][$batchId][$candidate] = benchmark($type, $range, ...$functions);
        }

        echo "\n";
    }
}

// This will be the path that the reporter will use.
$results_path = __DIR__ . '/results.json';

file_put_contents($results_path, json_encode($results, JSON_PRETTY_PRINT));
