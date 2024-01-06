<?php

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

$times = [41, 96, 88, 94];
$distances = [214, 1789, 1127, 1055];

$time2 = 41968894;
$distance2 = 214178911271055;

// sample
$times = [7, 15, 30];
$distances = [9, 40, 200];
//$time2 = 71530;
//$distance2 = 940200;

$part1 = 1;
for ($i = 0; $i < count($times); $i++) {
    $time = $times[$i];
    $recordDis = $distances[$i];

    // $x can be 0 -> $time;
    $cnt = 0;
    for ($x = 0; $x < $time; $x++) {
        $dis = $x * ($time - $x);
        if ($dis > $recordDis) {
            $cnt++;
        }
    }
    $part1 *= $cnt;
}

// Solve for dis = 0?
//$dis = $x * (71530 - $x) - 940200;
//$dis = $x * (41968894 - $x) - 214178911271055;

// This is a little slow, but not unreasonable.
for ($x = 0; $x < $time2; $x++) {
    $dis = $x * ($time2 - $x);
    if ($dis > $distance2) {
        $part2++;
    }
}

echo("Part 1: $part1". PHP_EOL);
echo("Part 2: $part2". PHP_EOL);
