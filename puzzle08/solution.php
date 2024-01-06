<?php

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    "LLR",
//    "",
//    "AAA = (BBB, BBB)",
//    "BBB = (AAA, ZZZ)",
//    "ZZZ = (ZZZ, ZZZ)",
//];

// sample part 2
//$lines = [
//    "LR",
//    "",
//    "11A = (11B, XXX)",
//    "11B = (XXX, 11Z)",
//    "11Z = (11B, XXX)",
//    "22A = (22B, XXX)",
//    "22B = (22C, 22C)",
//    "22C = (22Z, 22Z)",
//    "22Z = (22B, 22B)",
//    "XXX = (XXX, XXX)",
//];

class BinaryNode {

    private string $name;
    private mixed $left;
    private mixed $right;

    public function __construct($name, $left, $right) {
        $this->name = $name;
        $this->left = $left;
        $this->right = $right;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLeft() {
        return $this->left;
    }

    public function getRight() {
        return $this->right;
    }

}

$directions = str_split($lines[0]);

$nodes = [];
$starts = [];
for ($i = 2; $i < count($lines); $i++) {
    $line = $lines[$i];

    [$name, $values] = explode(" = ", $line);
    [$lhs, $rhs] = explode(", ", trim($values, "()"));

    $nodes[$name] = new BinaryNode($name, $lhs, $rhs);

    if (str_ends_with($name, "A")) {
        $starts[] = $nodes[$name];
    }
}

function step($nodes, $node, $dir) {
    if ($dir == "L") {
        return $nodes[$node->getLeft()];
    } else {
        return $nodes[$node->getRight()];
    }
}

function follow($directions, BinaryNode $root, $nodes) {
    $it = $root;
    $steps = 0;
    while (true) {
        foreach ($directions as $d) {
            $it = step($nodes, $it, $d);
            $steps++;
            if ($it->getName() === 'ZZZ') {
                // Reached the end.
                return $steps;
            }
        }
    }
}
function findCycle($nodes, $directions, $it): array {
    $steps = 0;
    $visited = [];
    $offsetsZ = [];
    while (true) {
        foreach ($directions as $i => $d) {
            $key = $it->getName() . "$i";
            if (array_key_exists($key, $visited)) {
                // Already been here.
                $offset = $visited[$key];
                $cycle = $steps - $visited[$key];
                return [$offset, $cycle, $offsetsZ];
            }
            $visited[$key] = $steps;
            if (str_ends_with($it->getName(), 'Z')) {
                $offsetsZ[] = $steps;
            }
            $it = step($nodes, $it, $d);
            $steps++;
        }
    }
}

function followMany($directions, array $starts, $nodes) {
    // Find a cycle for each path?
    $cycles = [];
    echo("directions " . count($directions) . "\n");
    foreach ($starts as $start) {
        [$offset, $cycle, $offsetsZ] = findCycle($nodes, $directions, $start);
        echo($start->getName() . " has a cycle of $cycle at offset $offset . with Z's at " . json_encode($offsetsZ) . "\n");
        $cycles[] = $cycle;
    }

    function gcd ($a, $b) {
        return $b ? gcd($b, $a % $b) : $a;
    }
    // Then reduce any list of integer
    echo(json_encode($cycles) . "\n");

    // Calculate the lcm as the product divided by the gcd for each pair.
    $gcd = array_reduce($cycles, 'gcd');
    echo("gcd $gcd\n");
    $gcd = pow($gcd, count($cycles) - 1);
    return sprintf("%.0f ", array_product($cycles) / $gcd);
}

if (array_key_exists('AAA', $nodes)) {
    /** @var BinaryNode $root */
    $root = $nodes['AAA'];
    $part1 = follow($directions, $root, $nodes);
}

$part2 = followMany($directions, $starts, $nodes);

echo("Part 1: $part1". PHP_EOL);

// 66525727994699778621440 is too high
echo("Part 2: $part2". PHP_EOL);
