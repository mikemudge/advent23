<?php

require_once 'helpers/Grid.php';

class Gear {

    private array $partNums = [];

    public function addPartNumber($num) {
        $this->partNums[] = $num;
    }
    public function getPartNums() {
        return $this->partNums;
    }
}

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

$gears = [];
for ($y = 0; $y < $grid->getHeight(); $y++) {
    for ($x = 0; $x < $grid->getWidth(); $x++) {
        $gridLoc = $grid->get($x,$y);
        if ($gridLoc->getKey() == "*") {
            $gear = new Gear();
            $gridLoc->setData($gear);
            $gears[] = $gear;
        }
    }
}

echo($grid->getWidth() . "," . $grid->getHeight() . "\n");
echo("Number of gears " . count($gears) . "\n");

$cur = 0;
$adjacent = false;
$adjacentGears = [];
for ($y = 0; $y < $grid->getHeight(); $y++) {
    // End of line/number (don't continue number to next line)
    if ($cur > 0 && $adjacent) {
        $part1 += $cur;
        foreach($adjacentGears as $gear) {
            $gear->getData()->addPartNumber($cur);
        }
    }
    $adjacent = false;
    $cur = 0;
    $adjacentGears = [];
    for ($x = 0; $x < $grid->getWidth(); $x++) {
        $c = $grid->get($x, $y)->getKey();
        if (is_numeric($c)) {
            $cur *= 10;
            $cur += intval($c);
            $neighbours = $grid->getAdjacent($x, $y);
            if (!$adjacent) {
                // Check neighbours for a *,+ or #
                foreach ($neighbours as $loc) {
                    if ($loc == null) {
                        continue;
                    }
                    $s = $loc->getKey();
                    if ($s == '.' || is_numeric($s)) {
                        continue;
                    }
                    if ($s == "*") {
                        $adjacentGears[$loc->getLocationString()] = $loc;
                    }
                    // Other characters are engine symbols.
                    $adjacent = true;
                }
            }
        } else {
            if ($adjacent) {
                $part1 += $cur;
            }
            foreach($adjacentGears as $gear) {
                $gear->getData()->addPartNumber($cur);
            }
            $adjacent = false;
            $cur = 0;
            $adjacentGears = [];
        }
    }
}

/** @var Gear $gear */
foreach($gears as $gear) {
    $nums = $gear->getPartNums();
    if (count($nums) == 2) {
        echo("Found gear with $nums[0] * $nums[1]\n");
        $part2 += $nums[0] * $nums[1];
    }
};

echo("Part 1: $part1". PHP_EOL);

// 76714386 is too low.
// 78236071
echo("Part 2: $part2". PHP_EOL);
