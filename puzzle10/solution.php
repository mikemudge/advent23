<?php

require_once 'helpers/Grid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
$lines = [
    "-L|F7",
    "7S-7|",
    "L|7||",
    "-L-J|",
    "L|-JF",
];

$grid = new Grid($lines);

$loc = $grid->find("S");

echo ("$loc\n");
echo("Part 1: $part1" . PHP_EOL);
echo("Part 2: $part2" . PHP_EOL);
