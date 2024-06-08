<?php

require_once 'helpers/Grid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    '...........',
//    '.....###.#.',
//    '.###.##..#.',
//    '..#.#...#..',
//    '....#.#....',
//    '.##..S####.',
//    '.##..#...#.',
//    '.......##..',
//    '.##.#.####.',
//    '.##..##.##.',
//    '...........',
//];

function solveGridDis(GridLocation $start) {
    $start->setData(['dis'=>0]);
    $toExplore = [$start];
    while ($toExplore) {
        $next = [];
        /** @var GridLocation $n */
        foreach ($toExplore as $n) {
            $dis = $n->getData()['dis'];

            foreach ($n->getAdjacent() as $possible) {
                if ($possible->getKey() == '.' || $possible->getKey() == 'S') {
                    $data = $possible->getData();
                    if (!$data || $data['dis'] > $dis + 1) {
                        // We found a shorter path to this node.
                        $possible->setData(['dis'=>$dis+1]);
                        $next[] = $possible;
                    }
                }
            };
        }
        $toExplore = $next;
    }
}

$grid = new Grid($lines);
$start = $grid->find("S");
solveGridDis($start);

//$grid->show();

$sampleCount = getReachable($grid, 6);
$part1 = getReachable($grid, 64);

echo("Sample: $sampleCount" . PHP_EOL);
echo("Part 1: $part1" . PHP_EOL);

// Part 2 needs to analyse the grid differently.
// 26M steps would take us too far for a memory based array representation.
$x = $start->getX();
$y = $start->getY();
$w = $grid->getWidth();
$h = $grid->getHeight();
echo("Start at $x, $y\n");
echo("Size is $w, $h\n");
// Start at 65, 65
// Size is 131, 131

// Looking at the grid, I see that the vertical and horizontal path from the start are clear.
// So we can reach the edge in minimal moves.
$downDis = $grid->get($x, $grid->getHeight() - 1)->getData()['dis'];
$upDis = $grid->get($x, 0)->getData()['dis'];
$leftDis = $grid->get($grid->getWidth() - 1, $y)->getData()['dis'];
$rightDis = $grid->get(0, $y)->getData()['dis'];

echo("Distance to edges $upDis, $rightDis, $downDis, $leftDis\n");
// Distance to edges 65, 65, 65, 65
// This means we can get to the S of the adjacent tile in 131.
// The alternating tiles will be able to access odd tiles instead of even ones though.
// We need to count how many tiles can be completely reached, and figure out how many tiles can only be partially reached.
// The number of steps is 26501365 which is (202300 * 131 + 65)
// It takes 65 steps to reach the edge of the first tile, then 131 more to get to the edge of another tile.
// So we have a diamond shape with 202300 tiles in each direction.
// There will be 4 points which have limited access (130 steps from the middle of the edge).
// Then we have the diamond edges which will also be 202300 tiles with 64 steps from the corner
// The edges will also have 202300 - 1 tiles with (65 + 131 - 1) 195 steps from the corner

// This method assumes that the initial grid is completely covered.
// Aka it won't work for step numbers less than $grid->getWidth().
// Also assumes that the start is in the center and that direct access to each edge is unobstructed.
function calculateStepCoverage(int $steps, array $lines): int {
    $grid = new Grid($lines);
    if ($grid->getWidth() != $grid->getHeight()) {
        throw new RuntimeException("Not supported");
    };
    if ($steps < $grid->getHeight()) {
        throw new RuntimeException("Not supported");
    }
    $size = $grid->getWidth();
    // Assuming size is odd as S must be in the center.
    $halfSize = ($size - 1) / 2;
    $count = 0;

    // Calculate the number of tiles in a straight line to reach the edge.
    $n = floor(($steps - $size) / $size);
    echo("steps: $steps tileSize: $size, halfSize: $halfSize fullTilesToEdge (aka n): $n\n");


    // Solve from the center to find out how many tiles are accessible as odd or even.
    $center = $grid->get($halfSize, $halfSize);
    solveGridDis($center);

    $altReachable = getReachable($grid, $steps - $size);
//    $grid->show();
    $centerReachable = getReachable($grid, $steps);

    // We need to know how many full tiles are odd vs even?
    // N            0 1 2 3  4  5
    // alternative  0 4 4 16 16 36
    // center       1 1 9 9  25 25
    $alternativeCount = pow($n + $n%2, 2);
    $centerCount = pow($n + 1 - $n%2, 2);

    echo("n: $n centerCount: $centerCount altCount: $alternativeCount reachable center: $centerReachable, alt: $altReachable\n");
    $count = $centerCount * $centerReachable + $alternativeCount * $altReachable;

//    echo("count from " . ($centerCount + $alternativeCount) . " full tiles $count\n");

    // Now calculate the remaining steps allowed in edge tiles.
    $r1 = $steps - ($n * $size) - 1;
    $r2 = $steps - ($n * $size) - $size - 1;

    // r1 = 50 - 3 * 11 - 1 = 50 - 33 - 1 = 16
    // r2 = 50 - 3 * 11 - 11 - 1 = 50 - 44 - 1 = 5
    echo("r1: $r1 r2: $r2\n");

    // Solve reachability from each corner.
    $gs = [new Grid($lines), new Grid($lines), new Grid($lines), new Grid($lines)];
    solveGridDis($gs[0]->get(0, 0));
    solveGridDis($gs[1]->get($grid->getWidth() - 1, 0));
    solveGridDis($gs[2]->get(0, $grid->getHeight() - 1));
    solveGridDis($gs[3]->get($grid->getWidth() - 1, $grid->getHeight() - 1));

    // Then calculate how many squares can be reached in r1 or r2 steps.
    $r1Counts = [0, 0, 0, 0];
    $r2Counts = [0, 0, 0, 0];
    foreach($gs as $i=>$g) {
        $r2Counts[$i] = getReachable($g, $r2);
//        $g->show();
        $r1Counts[$i] = getReachable($g, $r1);
//        $g->show();
    }

    echo("r1 counts " . ($n * 4) . " " . json_encode($r1Counts) . "\n");
    echo("r2 counts " . (($n + 1) * 4) . " " . json_encode($r2Counts) . "\n");

    // There will be N of each r1 edge tiles.
    // There will be (N - 1) of each r2 edge tiles.
    $count += $n * array_sum($r1Counts);
    $count += ($n + 1) * array_sum($r2Counts);

//    echo("Count after including edge tiles for r1/2 $count\n");

    // Now calculate the final tile in each cardinal direction.
    // Assuming a direct path (true in the real case, but not sample)
    $rem = $steps - $n * $size - $halfSize - 1;

    echo("Final Tiles rem: $rem\n");

    $gs = [new Grid($lines), new Grid($lines), new Grid($lines), new Grid($lines)];
    solveGridDis($gs[0]->get($halfSize, 0));
    solveGridDis($gs[1]->get($halfSize, $size - 1));
    solveGridDis($gs[2]->get(0, $halfSize));
    solveGridDis($gs[3]->get($size - 1, $halfSize));

    foreach($gs as $g) {
        $x = getReachable($g, $rem);
//        echo("Final tile count: $x\n");
        $count += $x;
//        $g->show();
    }

    return $count;
}

function getReachable(Grid $g, int $dis): int {
    $count = 0;
    $g->forEach(function($loc) use (&$count, $dis) {
        $data = $loc->getData();
        if ($data && isset($data['dis'])) {
            if ($dis % 2 == $data['dis'] % 2 && $data['dis'] <= $dis) {
                $count++;
                $loc->setKey('O');
            }
        }
    });
    return $count;
}

//$step50 = calculateStepCoverage(50, $lines);
//echo("Sample 50: $step50\n");
//
//$step100 = calculateStepCoverage(100, $lines);
//echo("Sample 100: $step100\n");

//
//$step500 = calculateStepCoverage(500, $lines);
//$step1000 = calculateStepCoverage(1000, $lines);
//$step5000 = calculateStepCoverage(5000, $lines);
//echo("Sample 500: $step500 1000: $step1000 and 5000 $step5000" . PHP_EOL);
//
//$part2 = calculateStepCoverage(327, $lines);
$part2 = calculateStepCoverage(26501365, $lines);
// Start at 65, 65
// Size is 131, 131
// Distance to edges 65, 65, 65, 65
// steps: 26501365 tileSize: 131, halfSize: 65 fullTilesToEdge (aka n): 202299
// n: 202299 centerCount: 40924885401 altCount: 40925290000 reachable center: 7265, alt: 7325
// r1: 195 r2: 64
// Final Tiles rem: 130
// Part 2: 597102953692415
// Got result: answer is too low
echo("Part 2: $part2" . PHP_EOL);
