<?php

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    'Game 1: 3 blue, 4 red; 1 red, 2 green, 6 blue; 2 green',
//    'Game 2: 1 blue, 2 green; 3 green, 4 blue, 1 red; 1 green, 1 blue',
//    'Game 3: 8 green, 6 blue, 20 red; 5 blue, 4 red, 13 green; 5 green, 1 red',
//    'Game 4: 1 green, 3 red, 6 blue; 3 green, 6 red; 3 green, 15 blue, 14 red',
//    'Game 5: 6 red, 1 blue, 3 green; 2 blue, 1 red, 2 green',
//];

function possibleGame($data, $maxes): bool {
    [$gameId, $gamesData] = explode(": ", $data);
    $games = explode("; ", $gamesData);
    foreach($games as $game) {
        $piecesData = explode(", ", $game);
        foreach($piecesData as $piece) {
            [$num, $col] = explode(" ", $piece);
            if ($num > $maxes[$col]) {
                return false;
            }
        }
    }
    return true;
}

function powerOfMinSetForGame($data): int {
    $minSet = [
        'red' => 0,
        'green' => 0,
        'blue' => 0,
    ];
    [$gameId, $gamesData] = explode(": ", $data);
    $games = explode("; ", $gamesData);
    foreach($games as $game) {
        $piecesData = explode(", ", $game);
        foreach($piecesData as $piece) {
            [$num, $col] = explode(" ", $piece);
            $minSet[$col] = max($minSet[$col], $num);
        }
    }
    $power = $minSet['red'] * $minSet['green'] * $minSet['blue'];
    echo("$gameId: $power " . json_encode($minSet, JSON_PRETTY_PRINT) . PHP_EOL);
    return $power;
}

// 12 red cubes, 13 green cubes, and 14 blue cubes
$maxes = [
    'red' => 12,
    'green' => 13,
    'blue' => 14,
];
foreach ($lines as $id => $line) {
    if (possibleGame($line, $maxes)) {
        $part1 += ($id + 1);
    };
    $part2 += powerOfMinSetForGame($line);
}

echo("Part 1: $part1". PHP_EOL);
echo("Part 2: $part2". PHP_EOL);
