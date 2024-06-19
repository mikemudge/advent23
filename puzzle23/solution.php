<?php
ini_set('xdebug.max_nesting_level', 10000);

require_once 'helpers/Grid.php';
require_once 'helpers/Graph.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    '#.#####################',
//    '#.......#########...###',
//    '#######.#########.#.###',
//    '###.....#.>.>.###.#.###',
//    '###v#####.#v#.###.#.###',
//    '###.>...#.#.#.....#...#',
//    '###v###.#.#.#########.#',
//    '###...#.#.#.......#...#',
//    '#####.#.#.#######.#.###',
//    '#.....#.#.#.......#...#',
//    '#.#####.#.#.#########v#',
//    '#.#...#...#...###...>.#',
//    '#.#.#v#######v###.###v#',
//    '#...#.>.#...>.>.#.###.#',
//    '#####v#.#.###v#.#.###.#',
//    '#.....#...#...#.#.#...#',
//    '#.#########.###.#.#.###',
//    '#...###...#...#...#.###',
//    '###.###.#.###v#####v###',
//    '#...#...#.#.>.>.#.>.###',
//    '#.###.###.#.###.#.#v###',
//    '#.....###...###...#...#',
//    '#####################.#',
//];

// finding the longest route means we need to find all routes?
// Use depth first as we can track visited more easily then.
function findLongestPath(GridLocation $loc, array &$visited, GridLocation $end) {
    $locString = $loc->getLocationString();
    if ($visited[$locString] ?? false) {
        // Already visited
        return PHP_INT_MIN;
    }
    if ($loc->getKey() == '#') {
        // Hit a wall
        return PHP_INT_MIN;
    }
    if ($loc === $end) {
        return 0;
    }
    $res = PHP_INT_MIN;
    $visited[$locString] = true;
    if ($loc->getKey() == '>') {
        $res = findLongestPath($loc->east(), $visited, $end);
    }
    if ($loc->getKey() == '<') {
        $res = findLongestPath($loc->west(), $visited, $end);
    }
    if ($loc->getKey() == 'v') {
        $res = findLongestPath($loc->south(), $visited, $end);
    }
    if ($loc->getKey() == '^') {
        $res = findLongestPath($loc->north(), $visited, $end);
    }
    if ($loc->getKey() == '.') {
        foreach ($loc->getAdjacent() as $possible) {
            $res = max($res, findLongestPath($possible, $visited, $end));
        }
    }
    $visited[$locString] = false;
    return $res + 1;
}

$grid = new Grid($lines);

$start = $grid->find(".");

for ($x = 0; $x < $grid->getWidth(); $x++) {
    $loc = $grid->get($x, $grid->getHeight() - 1);
    if ($loc->getKey() == '.') {
        $finish = $loc;
    }
}


echo("Start at $start\n");

echo("Finish at $finish\n");

$visited = [];
$part1 = findLongestPath($start, $visited, $finish);
echo("Part 1: $part1" . PHP_EOL);


function getPathToIntersection(GridLocation $loc, GridLocation $last): array {
    if ($loc->getKey() == '#' || $loc->getKey() == null) {
        throw new RuntimeException("Shouldn't be able to happen");
    }

    $walkable = [];
    foreach($loc->getAdjacent() as $n) {
        if ($n->getKey() == '#' || $n->getKey() == null) {
            // Non walkable tiles
            continue;
        }
        $walkable[] = $n;
    }
    if (count($walkable) > 2 || count($walkable) == 1) {
        // An intersection or a dead end.
        return [1, $loc];
    } else {
        if ($walkable[0] == $last) {
            [$dis, $n] = getPathToIntersection($walkable[1], $loc);
            return [$dis + 1, $n];
        } else {
            // Assumes walkable[1] is last.
            [$dis, $n] = getPathToIntersection($walkable[0], $loc);
            return [$dis + 1, $n];
        }
    }
}

function findIntersections(GridLocation $current): array {
    $intersectionNodes = [];
    foreach ($current->getAdjacent() as $n) {
        if ($n->getKey() == '#' || $n->getKey() == null) {
            // No path in this direction
            continue;
        }
        $intersectionNodes[] = getPathToIntersection($n, $current);
    }
    return $intersectionNodes;
}

function convertGridToGraph(GridLocation $start) {
    $g = new Graph();
    $exploreSet = [$start];
    while($exploreSet) {
        $next = [];
        /** @var GridLocation $loc */
        foreach ($exploreSet as $loc) {
            // Set key so show() will highlight intersections.
            $loc->setKey("O");

            // Find next intersections from this location.
            $intersectionNodes = findIntersections($loc);
            $lastNode = $g->addNode($loc->getLocationString());
            foreach ($intersectionNodes as $intersectionNode) {
                /** @var GridLocation $node */
                [$dis, $node] = $intersectionNode;

                $newNode = $g->addNode($node->getLocationString());
                $pathExists = false;
                foreach ($lastNode->getChildren() as $child) {
                    [$graphNode, $d] = $child;
                    if ($graphNode === $newNode) {
                        // The connection already exists, no need to readd it, or continue exploring.
                        echo("Found duplicate path of length $d,$dis to " . $graphNode->getId() . "\n");
                        $pathExists = true;
                        break;
                    }
                }
                if ($pathExists) {
                    // The connection already exists, no need to readd it, or continue exploring.
                    continue;
                }
                $g->connection($lastNode, $newNode, $dis);
                $next[] = $node;
            }
        }
        $exploreSet = $next;
    }
    return $g;
}

$g = convertGridToGraph($start);
$endNode = $g->addNode($finish->getLocationString());
$startNode = $g->addNode($start->getLocationString());

$grid->show();

$g->listNodes();

// Find longest path without repeats?

function findLongestNonRepeatingPath(GraphNode $currentNode, GraphNode $endNode, array &$visited) {
    if ($currentNode === $endNode) {
        return 0;
    }
    $longest = 0;
    $visited[$currentNode->getId()] = true;
    foreach ($currentNode->getChildren() as $child) {
        [$node, $dis] = $child;
        if (array_key_exists($node->getId(), $visited) && $visited[$node->getId()]) {
            // Can't reuse node.
            continue;
        }
        $longest = max($longest, $dis + findLongestNonRepeatingPath($node, $endNode, $visited));
    }
    $visited[$currentNode->getId()] = false;
    return $longest;
}

$visited = [];
$part2 = findLongestNonRepeatingPath($startNode, $endNode, $visited);

echo("Part 2: $part2" . PHP_EOL);
