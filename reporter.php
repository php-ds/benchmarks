<?

/**
 * Allow benchmarking to use as much memory as it needs, if available.
 */
ini_set('memory_limit', -1);

?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title></title>
    <script src="https://rawgit.com/nnnick/Chart.js/2.0.0-beta2/Chart.min.js"></script>

    <style>

    * {
        padding: 0;
        margin: 0;
        font-family: sans-serif;
    }

    body {
        padding-bottom: 200px;
    }

    canvas {

    }

    .chart {
        box-sizing: border-box;
        padding: 0 80px;
        margin: 20px 0;
        width: 100%;
        display: block;
    }

    hr {
        margin: 20px;
        border: none;
        border-top: 1px dashed #ccc;
    }

    h1 {
        padding: 20px 0px 0px 40px;
    }


    .version {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 0px 0 0px 40px;
    }

    </style>
</head>
<body>

<h1>Benchmark report</h1>
<p class="version"><?php echo phpversion() . ", " . php_uname('m') . ", " . php_uname('s') . "\n"; ?></p>

<?php

$colors = [
    "rgb(63, 81, 181)",
    "rgb(76, 175, 80)",
    "rgb(255, 152, 0)",
    "rgb(244, 67, 54)",
    "rgb(224, 64, 251)",
    "rgb(207, 216, 220)",
    "rgb(29, 233, 182)",
    "rgb(40, 40, 40)",
    "rgb(0, 188, 212)",
];
?>


<?php
function sup(int $value) {

    $superscript = [
        '⁰', '¹', '²', '³', '⁴', '⁵', '⁶', '⁷', '⁸', '⁹',
    ];

    for ($res = ''; $value > 0; $value = floor($value / 10)) {
        $res = $value % 10 . $res;
    }

    return '₂' . $res;
}
?>


<?php

$results = json_decode(file_get_contents(__DIR__ . "/results.json"), true);

?>

<?php foreach ($results as $benchmark => $batches) { ?>

    <?php
    if (count($batches) === 0 || count($batches[0]) === 0) {
        continue;
    }
    ?>



    <?php foreach ($batches as $batchId => $batch) { ?>

        <?php
            $labels = json_encode(array_keys($batch[array_keys($batch)[0]]['m']));
        ?>

        <?php foreach (['t', 'm'] as $type) { ?>

            <hr>

                <?php $id = preg_replace('~\W~', '_', strtolower("{$benchmark}__{$batchId}__{$type}")); ?>

                <div class="chart">
                    <canvas id="<?php echo $id; ?>" width="400" height="200"></canvas>
                </div>

                <script>
                    var context = document.getElementById("<?php echo $id; ?>");
                    var chart = new Chart(context, {

                        type: 'line',
                        data: {
                            labels: <?php echo $labels; ?>,
                            datasets: [

                            <?php
                            $i = 0;
                            foreach ($batch as $name => $results) {
                                $data = array_values($results[$type]);
                                $color = $colors[($i++) % count($colors)];
                            ?>
                            {
                                label: "<?php echo $name ?>",
                                fill: false,
                                backgroundColor: "<?php echo $color; ?>",
                                borderColor: "<?php echo $color; ?>",

                                pointRadius: 0,
                                pointBorderWidth: 0,
                                pointHoverRadius: 0,
                                pointHoverBorderWidth: 0,

                                tension: 0.4,

                                data: <?php echo json_encode($data); ?>,
                            },
                            <?php } ?>
                            ],
                        },
                        options: {
                            responsive: true,

                            title: {
                                display: true,
                                fontSize: 24,
                                padding: 20,
                                fontColor: "#111",
                                fontStyle: "normal",
                                fontFamily: "Input Mono",
                                text: "<?php echo $benchmark . (($type === 'm') ? ' (Memory usage)' : ' (Time taken)'); ?>",
                            },

                            elements: {
                                point: {
                                    radius: 0,
                                },
                                line: {
                                    borderWidth: 4,
                                }
                            },

                            tooltips: {
                                enabled: false,
                            },

                            legend: {
                                display: true,
                                fontFamily: "Input Mono",
                                labels: {
                                    boxWidth: 20,
                                    fontSize: 20,
                                    padding: 20,
                                    fontFamily: "Input Mono",
                                }
                            },

                            scales: {
                                xAxes: [
                                    {
                                        display: true,
                                        scaleLabel: {
                                            fontSize: 20,
                                            fontFamily: "Input Mono",
                                            display: true,
                                            labelString: "Number of values (2ⁿ)",
                                        },
                                        ticks: {
                                            fontSize: 20,
                                            fontFamily: "Input Mono",
                                            maxRotation: 0,
                                            autoSkip: false,
                                            callback: function(i) {
                                                return (i && !(i & (i - 1)))
                                                    ? Math.log2(i)
                                                    : null;
                                            }
                                        }
                                    }
                                ],
                                yAxes: [
                                    {
                                        display: true,
                                        scaleLabel: {
                                            fontSize: 20,
                                            fontFamily: "Input Mono",
                                            display: true,
                                            labelString: "<?php echo ($type === 'm') ? 'Memory usage (Mb)' : 'Time (ms)'; ?>",
                                        },
                                        ticks: {
                                            fontSize: 20,
                                            fontFamily: "Input Mono",
                                            maxRotation: 0,
                                            autoSkip: false,

                                            callback: function(i) {
                                                <?php if ($type === 'm') { ?>
                                                    return (i / 1000000).toFixed(1);
                                                <?php } else { ?>
                                                    return (i * 1000).toFixed(1);
                                                <?php } ?>
                                            }
                                        }
                                    }
                                ],
                            },
                        }
                    });
                </script>

            <?php } ?>
        <?php } ?>
    <?php } ?>
</body>
</html>
