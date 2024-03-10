<?php

require_once 'helpers/Grid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//'.|...\....',
//'|.-.\.....',
//'.....|-...',
//'........|.',
//'..........',
//'.........\\',
//'..../.\\\\..',
//'.-.-/..|..',
//'.|....-|.\\',
//'..//.|....',
//];

$grid = new Grid($lines);


function calculateEnergy($grid, $start) {
    // keep track of all beams location and direction.
    $directions = [
        'u' => [0, -1],
        'r' => [1, 0],
        'd' => [0, 1],
        'l' => [-1, 0],
    ];
    $beams = [$start];
    while (!empty($beams)) {
        $next = [];
        // Move all beams and calculate the next set.
        foreach ($beams as $b) {
            [$x, $y, $d] = $b;
            $gl = $grid->get($x, $y);
            if (!$gl) {
                // Outside of grid.
                continue;
            }
            $loc = $gl->getKey();
            $data = $gl->getData();
            if (isset($data[$d])) {
                // Already been here with the same direction.
                continue;
            }
            $data[$d] = true;
            $data['energized'] = true;
            $gl->setData($data);
            $inc = $directions[$d];
    //        echo("$x, $y, $d hits a $loc\n");
            switch ($loc) {
                case ".":
                    $next[] = [$x + $inc[0], $y + $inc[1], $d];
                    break;
                case "|":
                    if ($d == 'u' || $d == 'd') {
                        $next[] = [$x + $inc[0], $y + $inc[1], $d];
                    } else {
                        $next[] = [$x, $y - 1, 'u'];
                        $next[] = [$x, $y + 1, 'd'];
                    }
                    break;
                case "-":
                    if ($d == 'l' || $d == 'r') {
                        $next[] = [$x + $inc[0], $y + $inc[1], $d];
                    } else {
                        $next[] = [$x + 1, $y, 'r'];
                        $next[] = [$x - 1, $y, 'l'];
                    }
                    break;
                case "\\":
                    // d <> r
                    // l <> u
                    if ($d == 'l') {
                        $nd = 'u';
                    } elseif ($d == 'u') {
                        $nd = 'l';
                    } elseif($d == 'r') {
                        $nd = 'd';
                    } else {
                        $nd = 'r';
                    }
                    $inc = $directions[$nd];
                    $next[] = [$x + $inc[0], $y + $inc[1], $nd];
                    break;
                case "/":
                    // d <> l
                    // r <> u
                    if ($d == 'l') {
                        $nd = 'd';
                    } elseif ($d == 'd') {
                        $nd = 'l';
                    } elseif($d == 'r') {
                        $nd = 'u';
                    } else {
                        $nd = 'r';
                    }
                    $inc = $directions[$nd];
                    $next[] = [$x + $inc[0], $y + $inc[1], $nd];
                    break;
            }
        }
        $beams = $next;
    }
    $energy = 0;
    for ($y = 0; $y < $grid->getHeight(); $y++) {
        for ($x = 0; $x < $grid->getWidth(); $x++) {
            $data = $grid->get($x, $y)->getData();
            if ($data['energized'] ?? false) {
                $energy++;
            };
        }
    }
    return $energy;
}

$start = [0, 0, 'r'];
$part1 = calculateEnergy($grid, $start);

for ($y = 0; $y < $grid->getHeight(); $y++) {
    $start = [0, $y, 'r'];
    $grid = new Grid($lines);
    $part2 = max($part2, calculateEnergy($grid, $start));

    $start = [$grid->getWidth() - 1, $y, 'l'];
    $grid = new Grid($lines);
    $part2 = max($part2, calculateEnergy($grid, $start));
}
for ($x = 0; $x < $grid->getWidth(); $x++) {
    $start = [$x, 0, 'd'];
    $grid = new Grid($lines);
    $part2 = max($part2, calculateEnergy($grid, $start));

    $start = [$x, $grid->getHeight() - 1, 'u'];
    $grid = new Grid($lines);
    $part2 = max($part2, calculateEnergy($grid, $start));
}

echo("Part 1: $part1" . PHP_EOL);
echo("Part 2: $part2" . PHP_EOL);
