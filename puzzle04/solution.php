<?php

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
$sample = [
    'Card 1: 41 48 83 86 17 | 83 86  6 31 17  9 48 53',
    'Card 2: 13 32 20 16 61 | 61 30 68 82 17 32 24 19',
    'Card 3:  1 21 53 59 44 | 69 82 63 72 16 21 14  1',
    'Card 4: 41 92 73 84 69 | 59 84 76 51 58  5 54 83',
    'Card 5: 87 83 26 28 32 | 88 30 70 12 93 22 82 36',
    'Card 6: 31 18 13 56 72 | 74 77 10 23 35 67 36 11'
];
//$lines = $sample;

$results = [];
foreach ($lines as $i => $line) {
    [$gameId, $numbers] = preg_split('/:\s+/', $line);
    [$winnerStr, $cardStr] = preg_split('/ \|\s+/', $numbers);
    // Split on any amount of whitespace
    $winNums = preg_split('/\s+/', $winnerStr);
    // Split on any amount of whitespace
    $checkNums = preg_split('/\s+/', $cardStr);

    // convert both to int.
    $winNums = array_map('intval', $winNums);
    $checkNums = array_map('intval', $checkNums);

    echo($gameId . ": " . json_encode($winNums) . "|" . json_encode($checkNums) . "\n");
    $result = 0;
    foreach ($checkNums as $num) {
        if (in_array($num, $winNums)) {
            $result++;
        }
    }
    $results[$i] = $result;
    echo($result . "\n");
}

foreach ($results as $i => $result) {
    if ($result > 0) {
        $val = 2 ** ($result - 1);
    } else {
        $val = 0;
    }
    echo("Game $i: $result $val\n");
    $part1 += $val;
}

// We start with 1 of each card.
$countCards = array_fill(0, count($results), 1);
foreach ($results as $i => $result) {
    echo("Card $i has $result matching numbers\n");
    $copies = $countCards[$i];
    // result is the number of matching numbers in card i.
    for ($ii = 1; $ii <= $result; $ii++) {
        $countCards[$i + $ii] += $copies;
    }
}

echo(json_encode($countCards) . "\n");
$part2 = array_sum($countCards);
// 28941 was too high.
echo("Part 1: $part1". PHP_EOL);
echo("Part 2: $part2". PHP_EOL);
