<?php

require_once 'helpers/Grid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    "-L|F7",
//    "7S-7|",
//    "L|7||",
//    "-L-J|",
//    "L|-JF",
//];
//// sample
//$lines = [
//    "7-F7-",
//    ".FJ|7",
//    "SJLL7",
//    "|F--J",
//    "LJ.LJ",
//];

$grid = new Grid($lines);

$loc = $grid->find("S");

$u = $loc->north()->getKey();
$d = $loc->south()->getKey();
$l = $loc->west()->getKey();
$r = $loc->east()->getKey();
$north = false;
$south = false;
$east = false;
$west = false;
if ($u == "|" || $u == "F" || $u == "7") {
    $north = true;
}
if ($d == "|" || $d == "L" || $d == "J") {
    $south = true;
}
if ($l == "-" || $l == "L" || $l == "F") {
    $west = true;
}
if ($r == "-" || $r == "7" || $r == "J") {
    $east = true;
}

echo("$u,$d,$l,$r => $north,$south,$west,$east\n");
// NESW = 0123
$dir = 0;
if ($north) {
    if ($east) {
        $cur = "L";
    } else if ($west) {
        $cur = "J";
    } else if ($south) {
        $cur = "|";
    }
} else if ($south) {
    $dir = 2;
    if ($east) {
        $cur = "F";
    } else if ($west) {
        $cur = "7";
    }
} else {
    $dir = 1;
    $cur = "-";
}

$loc->setKey($cur);
echo("Dir: $dir, Cur: $cur\n");
function updateDir(int $dir, string $pipe) {
    switch ($dir) {
        case 0:
            return match ($pipe) {
                "|" => 0,
                "7" => 3,
                "F" => 1,
                default => throw new RuntimeException("Unknown pipe for north $pipe"),
            };
        case 1:
            return match ($pipe) {
                "-" => 1,
                "7" => 2,
                "J" => 0,
                default => throw new RuntimeException("Unknown pipe for east $pipe"),
            };
        case 2:
            return match ($pipe) {
                "|" => 2,
                "L" => 1,
                "J" => 3,
                default => throw new RuntimeException("Unknown pipe for south $pipe"),
            };
        case 3:
            return match ($pipe) {
                "-" => 3,
                "L" => 0,
                "F" => 2,
                default => throw new RuntimeException("Unknown pipe for west $pipe"),
            };
    }
}

// Follow $dir and build a path.
$iter = $loc->getDir($dir);
$iter->setData(true);
$dir = updateDir($dir, $iter->getKey());
$path = [$iter];
while($iter !== $loc) {
    $iter = $iter->getDir($dir);
    $iter->setData(true);
    $path[] = $iter;
    if ($iter->getKey() == "S") {
        break;
    }
    $dir = updateDir($dir, $iter->getKey());
}

$pathLength = count($path);
echo("Length: $pathLength\n");
$part1 = $pathLength / 2;

echo("Part 1: $part1" . PHP_EOL);

for ($y = 0; $y < $grid->getHeight(); $y++) {
    $insideLoop = false;
    $crossedLoop = false;
    $entry = null;
    for ($x = 0; $x < $grid->getWidth(); $x++) {
        $loc = $grid->get($x, $y);
        if ($loc->getData() === true) {
            echo($loc->getKey());
            if (!$crossedLoop) {
                $entry = $loc->getKey();
                if ($entry == "|") {
                    // Simple crossing.
                    $insideLoop = !$insideLoop;
                } else {
                    // Start a crossing.
                    $crossedLoop = true;
                }
            } else {
                // A continued crossing.
                if ($loc->getKey() != "-") {
                    // The crossing ends with a non -
                    $exit = $loc->getKey();
                    if ($entry == "F" && $exit == "J") {
                        $insideLoop = !$insideLoop;
                    } else if ($entry == "L" && $exit == "7") {
                        $insideLoop = !$insideLoop;
                    }
                    // No longer crossing.
                    $crossedLoop = false;
                }
            }
        } else {
            // This tile is not part of the loop
            if ($insideLoop) {
                $part2++;
                echo("I");
            } else {
                echo("O");
            }
        }
    }
    echo("\n");
}

echo("Part 2: $part2" . PHP_EOL);
