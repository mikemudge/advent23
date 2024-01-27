<?php

require_once 'helpers/Grid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    "#.##..##.",
//    "..#.##.#.",
//    "##......#",
//    "##......#",
//    "..#.##.#.",
//    "..##..##.",
//    "#.#.##.#.",
//    "",
//    "#...##..#",
//    "#....#..#",
//    "..##..###",
//    "#####.##.",
//    "#####.##.",
//    "..##..###",
//    "#....#..#",
//];


$grids = [];
$sublines = [];
$sum = 0;
foreach ($lines as $line) {
    if (empty($line)) {
        $grids[] = $grid = new Grid($sublines);
        $sum += $grid->getHeight();
        $sublines = [];
    } else {
        $sublines[] = $line;
    }
}
// Add the last set.
$grids[] = new Grid($sublines);
$sum += $grid->getHeight();

echo("Grids: " . count($grids) . " height: $sum\n");


function checkVerticalReflection(Grid $grid, int $x) {
    $mismatches = 0;
    $xrange = min($x + 1, $grid->getWidth() - 1 - $x);
//    echo("Checking vert " . ($x - $xrange + 1) . " - " . ($x + $xrange) . " for $x\n");
    for ($i = 1; $i <= $xrange; $i++) {
//        echo("Compare " . ($x - $i + 1) . " vs " . ($x + $i) . " for $i\n");
        // compare $x - $i + 1 and $x + $i?
        for ($y = 0; $y < $grid->getHeight(); $y++) {
            $r1 = $grid->get($x - $i + 1, $y);
            $r2 = $grid->get($x + $i, $y);
            if ($r1->getKey() != $r2->getKey()) {
//                echo("Mismatch at $x, $y, $i\n");
                $mismatches++;
            }
        }
    }
    return $mismatches;
}

function checkHorizontalReflection(Grid $grid, int $y) {
    $mismatches = 0;
    $yrange = min($y + 1, $grid->getHeight() - 1 - $y);
//    echo("Checking hori " . ($y - $yrange + 1) . " - " . ($y + $yrange) . " for $y\n");
    for ($i = 1; $i <= $yrange; $i++) {
//        echo("Compare " . ($y - $i + 1) . " vs " . ($y + $i) . " for $i\n");
        // compare $x - $i + 1 and $x + $i?
        for ($x = 0; $x < $grid->getWidth(); $x++) {
            $r1 = $grid->get($x, $y - $i + 1);
            $r2 = $grid->get($x, $y + $i);
            if ($r1->getKey() != $r2->getKey()) {
//                echo("Mismatch horizontally at $x, $y, $i\n");
                $mismatches++;
            }
        }
    }
    return $mismatches;
}

foreach ($grids as $i => $grid) {
    echo ("-------------- Grid $i --------------\n");

    for ($x = 0; $x < $grid->getWidth() - 1; $x++) {
        $mismatches = checkVerticalReflection($grid, $x);
        if ($mismatches == 0) {
            $part1 += ($x + 1);
        }
        if ($mismatches == 1) {
            $part2 += ($x + 1);
        }
    }
    for ($y = 0; $y < $grid->getHeight() - 1; $y++) {

        $mismatches = checkHorizontalReflection($grid, $y);
        if ($mismatches == 0) {
            $part1 += ($y + 1) * 100;
        }
        if ($mismatches == 1) {
            $part2 += ($y + 1) * 100;
        }
    }
}

echo("Part 1: $part1" . PHP_EOL);
echo("Part 2: $part2" . PHP_EOL);
