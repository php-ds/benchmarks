<?php

/**
 * Benchmark schedule.
 */
return [

    // 'Stack::pop' => [
    //     [MEDIUM, [
    //         SPL_STACK,
    //         STACK,
    //     ]],
    // ],

    // 'PriorityQueue::push' => [
    //     [MEDIUM, [
    //         SPL_PQ,
    //         PRIORITY_QUEUE,
    //         // QUEUE,
    //     ]],
    // ],

    // 'Set::add' => [
    //     [MEDIUM, [
    //         SPL_OS,
    //         SET,
    //     ]]
    // ],

    // 'Set vs. array_unique' => [
    //     [SMALL, [
    //         PHP_ARRAY,
    //         SET,
    //     ]]
    // ],

    // 'Map::put' => [
    //     [MEDIUM, [
    //         PHP_ARRAY,
    //         MAP,
    //     ]],
    // ],

    // 'Map::remove' => [
    //     [MEDIUM, [
    //         PHP_ARRAY,
    //         MAP,
    //     ]],
    // ],

    'Sequence::push (allocated)' => [
        [MEDIUM, [
            SPL_FA,
            VECTOR,
            DEQUE,
        ]],
    ],

    'Sequence::push' => [
        [MEDIUM, [
            PHP_ARRAY,
            SPL_FA,
            SPL_DLL,
            VECTOR,
            DEQUE,
        ]],
    ],

    // 'Sequence::unshift' => [
    //     [MEDIUM, [
    //         PHP_ARRAY,
    //         SPL_DLL,
    //         VECTOR,
    //         DEQUE,
    //     ]],
    // ],

    // 'Sequence::pop' => [
    //     [MEDIUM, [
    //         PHP_ARRAY,
    //         SPL_DLL,
    //         VECTOR,
    //         DEQUE,
    //     ]],
    // ],
];
