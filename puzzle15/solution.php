<?php

require_once 'helpers/Grid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    "rn=1,cm-,qp=3,cm=2,qp-,pc=4,ot=9,ab=5,pc-,pc=6,ot=7",
//];

function calculateHash(string $line): int {
    $curValue = 0;
    $chars = str_split($line);
    foreach ($chars as $c) {
        $curValue += ord($c);
        $curValue *= 17;
        $curValue %= 256;
    }
    return $curValue;
}

echo("HASH of HASH " . calculateHash("HASH") . "\n");

$instructions = explode(",", $lines[0]);

$buckets = array_fill(0, 256, []);

foreach ($instructions as $instruction) {
    $part1 += calculateHash($instruction);
    if (str_contains($instruction, "=")) {
        $op = "=";
        [$label, $value] = explode("=", $instruction);
    } else {
        $op = "-";
        [$label, $value] = explode("-", $instruction);
    }

    $b = calculateHash($label);

    if ($op == "-") {
        // remove the lens from the label
        $idx = -1;
        foreach ($buckets[$b] as $i => $lens) {
            if ($lens[0] == $label) {
                echo($lens[0] . " == $label\n");
                $idx = $i;
                break;
            }
            echo($lens[0] . " != $label\n");
        }
        if ($idx !== -1) {
            array_splice($buckets[$b], $idx, 1);
        } else {
            echo($lens[0] . " not found in bucket $b\n");
        }
    } else {
        $idx = -1;
        foreach ($buckets[$b] as $i => $lens) {
            if ($lens[0] == $label) {
                echo($lens[0] . " == $label\n");
                $idx = $i;
                break;
            }
            echo($lens[0] . " != $label\n");
        }

        if ($idx !== -1) {
            // Replace the existing label.
            $buckets[$b][$idx] = [$label, $value];
        } else {
            // add to the end.
            $buckets[$b][] = [$label, $value];
        }
    }
    echo("Instruction $instruction\n");
    for ($b2 = 0; $b2 < 4; $b2++) {
        echo("Bucket $b2: ");
        foreach ($buckets[$b2] as $ii => $lens) {
            echo("[" . join(",", $lens) . "] ");
        }
        echo(PHP_EOL);
    }
}

foreach ($buckets as $i => $bucket) {
    $boxNumber = $i + 1;
    foreach ($bucket as $ii => $lens) {
        $slotNumber = $ii + 1;
        $focalLength = $lens[1];
        $focusPower = $boxNumber * $slotNumber * $focalLength;
        echo("$boxNumber * $slotNumber * $focalLength = $focusPower\n");
        $part2 += $focusPower;
    }
}

echo("Part 1: $part1" . PHP_EOL);
echo("Part 2: $part2" . PHP_EOL);
