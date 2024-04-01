<?php

require_once 'helpers/Grid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
$lines = [
    'R 6 (#70c710)',
    'D 5 (#0dc571)',
    'L 2 (#5713f0)',
    'D 2 (#d2c081)',
    'R 2 (#59c680)',
    'D 2 (#411b91)',
    'L 5 (#8ceee2)',
    'U 2 (#caa173)',
    'L 1 (#1b58a2)',
    'U 2 (#caa171)',
    'R 2 (#7807d2)',
    'U 3 (#a77fa3)',
    'L 2 (#015232)',
    'U 2 (#7a21e3)',
];

$x = 0;
$y = 0;
$minX = 0;
$minY = 0;
$maxX = 0;
$maxY = 0;
foreach ($lines as $line) {
    [$dir, $dis, $color] = explode(" ", $line);
    $dis = intval($dis);
    if ($dir == "R") {
        $x += $dis;
    } elseif ($dir == "D") {
        $y += $dis;
    } elseif ($dir == "L") {
        $x -= $dis;
    } elseif ($dir == "U") {
        $y -= $dis;
    } else {
        throw new RuntimeException("Unknown direction $dir");
    }
//    echo("$dis, $dir\n");
//    echo("$x, $y\n");
    $maxX = max($maxX, $x);
    $maxY = max($maxY, $y);
    $minX = min($minX, $x);
    $minY = min($minY, $y);
}
echo("x,y: $minX,$minY - $maxX,$maxY" . PHP_EOL);

$data = [];
for ($y = 0; $y < $maxY - $minY + 1; $y++) {
    $data[] = str_repeat('.', $maxX - $minX + 1);
}
$grid = new Grid($data);

$x = -$minX;
$y = -$minY;
// Now fill in the grid.
foreach ($lines as $line) {
    [$dir, $dis, $color] = explode(" ", $line);
    $dis = intval($dis);
    for ($i = 0; $i < $dis; $i++) {
        if ($dir == "R") {
            $x++;
        } elseif ($dir == "D") {
            $y++;
        } elseif ($dir == "L") {
            $x--;
        } elseif ($dir == "U") {
            $y--;
        } else {
            throw new RuntimeException("Unknown direction $dir");
        }
        $gridLoc = $grid->get($x, $y);
        $gridLoc->setKey("#");
    }
}

// TODO figure out which locations can be reached from the border.
$exploreNext = [];
for ($x = 0; $x < $grid->getWidth(); $x++) {
    $exploreNext[] = $grid->get($x, 0);
    $exploreNext[] = $grid->get($x, $grid->getHeight() - 1);
}

for ($y = 0; $y < $grid->getWidth(); $y++) {
    $exploreNext[] = $grid->get(0, $y);
    $exploreNext[] = $grid->get($grid->getWidth() - 1, $y);
}

while ($exploreNext) {
    $next = $exploreNext;
    $exploreNext = [];
    /** @var GridLocation $gridLoc */
    foreach ($next as $gridLoc) {
        if (!$gridLoc->getData() && $gridLoc->getKey() == ".") {
            $gridLoc->setData(true);
            foreach ($gridLoc->getAdjacent() as $opt) {
                $exploreNext[] = $opt;
            }
        }
    }
}

$grid->forEach(function(GridLocation $gridLoc) {
    if (!$gridLoc->getData()) {
        $gridLoc->setKey("#");
    }
});

$part1 = $grid->count("#");

$grid->show();

// 657 is too low.
echo("Part 1: $part1" . PHP_EOL);
echo("Part 2: $part2" . PHP_EOL);
