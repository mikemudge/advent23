<?php

require_once 'helpers/Grid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    "...#......",
//    ".......#..",
//    "#.........",
//    "..........",
//    "......#...",
//    ".#........",
//    ".........#",
//    "..........",
//    ".......#..",
//    "#...#.....",
//];

$grid = new Grid($lines);

// Find all #'s and all rows/columns which are empty?
$rowCosts = [];
$colCosts = [];
/** @var GridLocation[] $galaxies */
$galaxies = [];

$cost = 0;
$cost2 = 0;
$rowCosts = [[$cost, $cost2]];
for ($y = 0; $y < $grid->getHeight(); $y++) {
    $containsGalaxy = false;
    for ($x = 0; $x < $grid->getWidth(); $x++) {
        $loc = $grid->get($x, $y);
        if ($loc->getKey() == "#") {
            $galaxies[] = $loc;
            $containsGalaxy = true;
        }
    }
    if ($containsGalaxy) {
        $cost++;
        $cost2++;
    } else {
        // Double the width of this row.
        $cost += 2;
        $cost2 += 1000000;
    }
    $rowCosts[] = [$cost, $cost2];
}

$cost = 0;
$cost2 = 0;
$colCosts = [[$cost, $cost2]];
for ($x = 0; $x < $grid->getWidth(); $x++) {
    $containsGalaxy = false;
    for ($y = 0; $y < $grid->getHeight(); $y++) {
        $loc = $grid->get($x, $y);
        if ($loc->getKey() == "#") {
            $containsGalaxy = true;
        }
    }
    if ($containsGalaxy) {
        $cost++;
        $cost2++;
    } else {
        // Double the width of this row.
        $cost += 2;
        $cost2 += 1000000;
    }
    $colCosts[] = [$cost, $cost2];
}

$gCount = count($galaxies);
for ($i = 0; $i < $gCount; $i++) {
    for ($ii = $i; $ii < $gCount; $ii++) {
        $g1 = $galaxies[$i];
        $g2 = $galaxies[$ii];
        $dis = abs($colCosts[$g1->getX()][0] - $colCosts[$g2->getX()][0]);
        $dis += abs($rowCosts[$g1->getY()][0] - $rowCosts[$g2->getY()][0]);
        $part1 += $dis;
        $dis = abs($colCosts[$g1->getX()][1] - $colCosts[$g2->getX()][1]);
        $dis += abs($rowCosts[$g1->getY()][1] - $rowCosts[$g2->getY()][1]);
        $part2 += $dis;
    }
}

echo("Part 1: $part1" . PHP_EOL);
echo("Part 2: $part2" . PHP_EOL);
