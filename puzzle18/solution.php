<?php

require_once 'helpers/Grid.php';
require_once 'helpers/SparseGrid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    'R 6 (#70c710)',
//    'D 5 (#0dc571)',
//    'L 2 (#5713f0)',
//    'D 2 (#d2c081)',
//    'R 2 (#59c680)',
//    'D 2 (#411b91)',
//    'L 5 (#8ceee2)',
//    'U 2 (#caa173)',
//    'L 1 (#1b58a2)',
//    'U 2 (#caa171)',
//    'R 2 (#7807d2)',
//    'U 3 (#a77fa3)',
//    'L 2 (#015232)',
//    'U 2 (#7a21e3)',
//];

$x = 0;
$y = 0;
$minX = 0;
$minY = 0;
$maxX = 0;
$maxY = 0;
foreach ($lines as $line) {
    [$dir, $dis, $color] = explode(" ", $line);
    $dis = intval($dis);

    $colorNum = base_convert(substr($color, 2, -2), 16, 10);
    $colorDir = $color[7];
//    0 means R, 1 means D, 2 means L, and 3 means U
    switch($color[7]) {
        case 0:
            $colorDir = 'R';
            break;
        case 1:
            $colorDir = 'D';
            break;
        case 2:
            $colorDir = 'L';
            break;
        case 3:
            $colorDir = 'U';
            break;
        default:
            throw new RuntimeException("Unknown direction $color[7]");
    }
    echo("$color, $colorNum, $colorDir\n");
    $parsedInstructions[] = [$dir, $dis, $colorNum, $colorDir];
}
foreach ($parsedInstructions as $v) {
    [$dir, $dis, $colorNum, $colorDir] = $v;
    echo("$x, $y\n");

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
    $maxX = max($maxX, $x);
    $maxY = max($maxY, $y);
    $minX = min($minX, $x);
    $minY = min($minY, $y);
}
echo("x,y: $minX,$minY - $maxX,$maxY" . PHP_EOL);


// Build a grid for the entire covered range for part1.
$data = [];
for ($y = 0; $y < $maxY - $minY + 1; $y++) {
    $data[] = str_repeat('.', $maxX - $minX + 1);
}
$grid = new Grid($data);

$x = -$minX;
$y = -$minY;
// Now fill in the grid.
foreach ($parsedInstructions as $v) {
    [$dir, $dis, , ] = $v;
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

function calculateInterior(Grid $grid) {
    // Determine which grid locations are connected to a border.
    // These are not part of the interior, but everything else is.
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

    // Set every location which is interior as a #
    $grid->forEach(function (GridLocation $gridLoc) {
        if (!$gridLoc->getData()) {
            $gridLoc->setKey("#");
        }
    });
}

calculateInterior($grid);

// Then return the number of these (including the existing outline)
$part1 =  $grid->count("#");

//$grid->show();

// Part 2 can't use a grid as the numbers are massive.
// However the grid is also very sparse, so we need a new data structure.

$x = 0;
$y = 0;
// We need to keep track of all the x's and y's where the grid changes.
$xs = [];
$ys = [];
foreach ($parsedInstructions as $v) {
    // Using the dis and dir from the color hex
    [, , $dis, $dir] = $v;

    $xs[] = $x;
    $ys[] = $y;

    if ($dir == "R") {
        $ys[] = $y + 1;
        $x += $dis;
    } elseif ($dir == "D") {
        $xs[] = $x + 1;
        $y += $dis;
    } elseif ($dir == "L") {
        $ys[] = $y + 1;
        $x -= $dis;
    } elseif ($dir == "U") {
        $xs[] = $x + 1;
        $y -= $dis;
    } else {
        throw new RuntimeException("Unknown direction $dir");
    }
}
$xs[] = $x;
$yx[] = $y;

// now create a sparse grid using the x's and y's of interest.
$grid2 = new SparseGrid($xs, $ys);
$x = 0;
$y = 0;
foreach ($parsedInstructions as $v) {
    // Using the dis and dir from the color hex
    [, , $dis, $dir] = $v;

    if ($dir == "R") {
        $grid2->addBlock($x, $y, $x + $dis + 1, $y + 1, "#");
        $x += $dis;
    } elseif ($dir == "D") {
        $grid2->addBlock($x, $y, $x + 1, $y + $dis + 1, "#");
        $y += $dis;
    } elseif ($dir == "L") {
        $grid2->addBlock($x - $dis, $y, $x, $y + 1, "#");
        $x -= $dis;
    } elseif ($dir == "U") {
        $grid2->addBlock($x, $y - $dis, $x + 1, $y, "#");
        $y -= $dis;
    } else {
        throw new RuntimeException("Unknown direction $dir");
    }
}

// fill in the grid?
calculateInterior($grid2);

//$grid2->show();

// TODO count needs a SparseGrid implementation
$part2 = $grid2->count("#");

// 657 is too low.
echo("Part 1: $part1" . PHP_EOL);
echo("Part 2: $part2" . PHP_EOL);
