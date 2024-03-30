<?php

ini_set('memory_limit', '2G');

require_once 'helpers/Grid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    '2413432311323',
//    '3215453535623',
//    '3255245654254',
//    '3446585845452',
//    '4546657867536',
//    '1438598798454',
//    '4457876987766',
//    '3637877979653',
//    '4654967986887',
//    '4564679986453',
//    '1224686865563',
//    '2546548887735',
//    '4322674655533',
//];

$grid = new Grid($lines);

$grid->show();

function getShortestPath($starts, $end, $evaluateFn) {
    $pq = new SplPriorityQueue();
    foreach ($starts as $start) {
        $pq->insert($start, 0);
    }
    $queueReads = 0;
    $visited = [];
    while($pq->valid()) {
        $queueReads++;
        $state = $pq->extract();
        [$loc, $dir, $times, $cost] = $state;
        /** @var GridLocation $loc */

        if ($queueReads % 100000 == 0) {
            echo("Iter $queueReads: cost $cost loc " . $loc->getLocationString() . "\n");
        }
        if ($loc === $end) {
            return -$cost;
        }
        $visitKey = $loc->getLocationString() . ",$dir,$times";
        if (isset($visited[$visitKey])) {
            continue;
        }

        $visited[$visitKey] = $times;

        $up = $loc->north();
        $right = $loc->east();
        $down = $loc->south();
        $left = $loc->west();

        // Consider moving in each direction.
        call_user_func($evaluateFn, $pq, $up, $dir, $times, $cost, 0);
        call_user_func($evaluateFn, $pq, $right, $dir, $times, $cost, 1);
        call_user_func($evaluateFn, $pq, $down, $dir, $times, $cost, 2);
        call_user_func($evaluateFn, $pq, $left, $dir, $times, $cost, 3);
    }

    echo("Completed looping through $queueReads items of the queue, with no win\n");
}

function part1Eval(SplPriorityQueue $pq, ?GridLocation $nextLoc, int $dir, int $times, int $cost, int $move) {
    if ($nextLoc->getKey() == null) {
        // Outside the map
        return;
    }
    if ((4 + $dir - $move) % 4 == 2) {
        // Can't turn around 180.
        return;
    }
    $p = $cost - (int)$nextLoc->getKey();
    $t = 1;
    if ($dir == $move) {
        $t += $times;
    }
    // We can only this direction 3 times.
    if ($t <= 3) {
        $pq->insert([$nextLoc, $move, $t, $p], $p);
    }
}

function part2Eval(SplPriorityQueue $pq, ?GridLocation $nextLoc, int $dir, int $times, int $cost, int $move) {
    if ($nextLoc->getKey() == null) {
        // Outside the map
        return;
    }
    $off = (4 + $dir - $move) % 4;
    if ($off == 2) {
        // Can't turn around 180.
        return;
    }
    $p = $cost - (int)$nextLoc->getKey();
    if ($dir == $move) {
        // Can't go more than 10 in a straight line.
        if ($times < 10) {
            $pq->insert([$nextLoc, $move, $times + 1, $p], $p);
        }
        return;
    }
    if ($off == 1 || $off == 3) {
        // Can't turn until 4 moves in the direction.
        if ($times >= 4) {
            $pq->insert([$nextLoc, $move, 1, $p], $p);
        }
    }
}

$starts = [[$grid->get(0, 0), 1, 0, 0], [$grid->get(0, 0), 2, 0, 0]];
$end = $grid->bottomRight();

echo("End at $end\n");
$part1 = getShortestPath($starts, $end, 'part1Eval');
$part2 = getShortestPath($starts, $end, 'part2Eval');

echo("Part 1: $part1" . PHP_EOL);
echo("Part 2: $part2" . PHP_EOL);
