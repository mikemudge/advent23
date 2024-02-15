<?php

require_once 'helpers/Grid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    "O....#....",
//    "O.OO#....#",
//    ".....##...",
//    "OO.#O....O",
//    ".O.....O#.",
//    "O.#..O.#.#",
//    "..O..#O..O",
//    ".......O..",
//    "#....###..",
//    "#OO..#....",
//];

function calculateLoad(Grid $grid): int {
    $sum = 0;
    for ($x = 0; $x < $grid->getWidth(); $x++) {
        for ($y = 0; $y < $grid->getHeight(); $y++) {
            $c = $grid->get($x, $y)->getKey();
            if ($c == "O") {
                $sum += ($grid->getHeight() - $y);
            }
        }
    }
    return $sum;
}

function moveNorth($grid): void {
    // Move North (to the top)
    $lastY = array_fill(0, $grid->getWidth(), -1);

    for ($y = 0; $y < $grid->getHeight(); $y++) {
        for ($x = 0; $x < $grid->getWidth(); $x++) {
            $c = $grid->get($x, $y)->getKey();
            if ($c == "#") {
                $lastY[$x] = $y;
//            echo("Set y ($x) = $y\n");
            } else if ($c == "O") {

                $lastY[$x]++;

//            echo("Slide y ($x) = $y - $lastY[$x]\n");
                // Unset current location.
                $grid->get($x, $y)->setKey(".");
                // Set the rock at the north most location it would slide to.
                $grid->get($x, $lastY[$x])->setKey("O");
            }
        }
    }
}

function cycle(Grid $grid): void {
    // Move North (to the top)
    moveNorth($grid);

    // Move west (to the left)
    $lastX = array_fill(0, $grid->getHeight(), -1);

    for ($x = 0; $x < $grid->getWidth(); $x++) {
        for ($y = 0; $y < $grid->getHeight(); $y++) {
            $c = $grid->get($x, $y)->getKey();
            if ($c == "#") {
                $lastX[$y] = $x;
            } else if ($c == "O") {
                $lastX[$y]++;
                // Unset current location.
                $grid->get($x, $y)->setKey(".");
                // Set the rock at the west most location it would slide to.
                $grid->get($lastX[$y], $y)->setKey("O");
            }
        }
    }

    // Move South (to the bottom)
    $lastY = array_fill(0, $grid->getWidth(), $grid->getHeight());

    for ($y = $grid->getHeight() - 1; $y >= 0; $y--) {
        for ($x = 0; $x < $grid->getWidth(); $x++) {
            $c = $grid->get($x, $y)->getKey();
            if ($c == "#") {
                $lastY[$x] = $y;
            } else if ($c == "O") {
                $lastY[$x]--;
                // Unset current location.
                $grid->get($x, $y)->setKey(".");
                // Set the rock at the north most location it would slide to.
                $grid->get($x, $lastY[$x])->setKey("O");
            }
        }
    }

    // Move east (to the right)
    $lastX = array_fill(0, $grid->getHeight(), $grid->getWidth());

    for ($x = $grid->getWidth() - 1; $x >=0; $x--) {
        for ($y = 0; $y < $grid->getHeight(); $y++) {
            $c = $grid->get($x, $y)->getKey();
            if ($c == "#") {
                $lastX[$y] = $x;
            } else if ($c == "O") {
                $lastX[$y]--;
                // Unset current location.
                $grid->get($x, $y)->setKey(".");
                // Set the rock at the west most location it would slide to.
                $grid->get($lastX[$y], $y)->setKey("O");
            }
        }
    }
}

$grid = new Grid($lines);
moveNorth($grid);

$part1 = calculateLoad($grid);

//$grid->show();

// Start again for part 2.
$grid = new Grid($lines);

$visited = [];

function getKey(Grid $grid) {
    return $grid->toIdString();
}

$oldOffset = -1;
$cycleLength = -1;
for ($offset = 1; $offset <= 1000000000; $offset++) {
    cycle($grid);
    if ($offset < 4) {
        echo("Cycle $offset\n");
//        $grid->show();
    }

    $key = getKey($grid);

    if (array_key_exists($key, $visited)) {
        // Found a loop
        $oldOffset = $visited[$key];
        echo("Loop from $oldOffset to $offset\n");
        $cycleLength = $offset - $oldOffset;
        break;
    }
    $visited[$key] = $offset;
}

echo("Cycle at $oldOffset of length $cycleLength\n");
// how many cycles do we need to get to 1,000,000,000?

$numberOfCycles = floor((1000000000 - $oldOffset) / $cycleLength);
$newOffset = $oldOffset + $numberOfCycles * $cycleLength;

echo("Being at $oldOffset is equal to being at $newOffset (after $numberOfCycles cycles)\n");

// Iterate until 1000000000
for ($offset = $newOffset + 1; $offset <= 1000000000; $offset++) {
    cycle($grid);
    $load = calculateLoad($grid);
    echo("Cycle $offset has load $load\n");
}

$part2 = calculateLoad($grid);
echo("Part 1: $part1" . PHP_EOL);
echo("Part 2: $part2" . PHP_EOL);
