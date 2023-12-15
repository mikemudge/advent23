<?php

require_once 'helpers/Grid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
$sample = explode("\n",'467..114..
...*......
..35..633.
......#...
617*......
.....+.58.
..592.....
......755.
...$.*....
.664.598..');
//$lines = $sample;

$grid = new Grid($lines);

$cur = 0;
$adjacent = false;
for ($y = 0; $y < $grid->getHeight(); $y++) {
    // End of line/number (don't continue number to next line)
    if ($cur > 0 && $adjacent) {
        $part1 += $cur;
    }
    $adjacent = false;
    $cur = 0;
    for ($x = 0; $x < $grid->getWidth(); $x++) {
        $c = $grid->get($x, $y);
        if (is_numeric($c)) {
            $cur *= 10;
            $cur += intval($c);
            $symbols = $grid->getAdjacent($x, $y);
            if (!$adjacent) {
                // Check neighbours for a *,+ or #
                foreach ($symbols as $s) {
                    if ($s == null || $s == '.' || is_numeric($s)) {
                        continue;
                    }
                    // Other characters are engine symbols.
                    $adjacent = true;
                }
            }
        } else {
            if ($adjacent) {
                $part1 += $cur;
            }
            $adjacent = false;
            $cur = 0;
        }
    }
}

echo("Part 1: $part1". PHP_EOL);
echo("Part 2: $part2". PHP_EOL);
