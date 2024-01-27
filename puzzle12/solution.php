<?php

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    "???.### 1,1,3",
//    ".??..??...?##. 1,1,3",
//    "?#?#?#?#?#?#?#? 1,3,1,6",
//    "????.#...#... 4,1,1",
//    "????.######..#####. 1,6,5",
//    "?###???????? 3,2,1",
//];

function possibilities($row, $i, $values, &$cache): int {
//    echo($row . " " . $i . " " . join(",", $values) . "\n");
    if ($i >= strlen($row)) {
        // Reached the end
        // If we have no values left then this is a match.
        if (empty($values)) {
//            echo("Match found\n");
            return 1;
        }
        return 0;
    }
    if (array_sum($values) + count($values) - 1 > strlen($row) - $i) {
        // We need at least enough remaining row to match all the values and the gaps between values.
        return 0;
    }

    $cacheKey = $row . "$i". join(",", $values);
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    // Figure out total?
    $next = $row[$i];
    if ($next == ".") {
        // find the values in the rest of the row.
        $knownDot =  possibilities($row, $i + 1, $values, $cache);
//        echo("$i . -> $knownDot\n");
        $cache[$cacheKey] = $knownDot;
        return $knownDot;
    } else if ($next == "#") {
        // We must match the entire next $value here.
        if (empty($values)) {
            // No match is possible if there are no values remaining.
            return 0;
        }
        // Remove the front value and consume it.
        $nextValue = array_shift($values);
        for ($j = 0; $j < $nextValue; $j++) {
            // These values must all be # or ? to match.
            if ($row[$i + $j] == ".") {
                return 0;
            }
        }
        if ($i + $nextValue < strlen($row) && $row[$i + $nextValue] == "#") {
            // After the value we must have a gap (. or ?) otherwise the value doesn't fit.
            return 0;
        }
        $knownHash = possibilities($row, $i + $nextValue + 1, $values, $cache);
//        echo("$i # -> $knownHash\n");
        $cache[$cacheKey] = $knownHash;
        return $knownHash;
    } else if ($next == "?") {
        // Could be either or.
        $assumeDot = possibilities($row, $i + 1, $values, $cache);
//        echo("$i ? = . -> $assumeDot\n");

        // We must match the entire next $value here.
        if (empty($values)) {
            // No match is possible if there are no values remaining.
            $cache[$cacheKey] = $assumeDot;
            return $assumeDot;
        }
        // Remove the front value and consume it.
        $nextValue = array_shift($values);
        for ($j = 0; $j < $nextValue; $j++) {
            // These values must all be # or ? to match.
            if ($row[$i + $j] == ".") {
                $cache[$cacheKey] = $assumeDot;
                return $assumeDot;
            }
        }
        if ($i + $nextValue < strlen($row) && $row[$i + $nextValue] == "#") {
            // After the value we must have a gap (. or ? or EOL) otherwise the value doesn't fit.
            $cache[$cacheKey] = $assumeDot;
            return $assumeDot;
        }
        $assumeHash = possibilities($row, $i + $nextValue + 1, $values, $cache);
//        echo("$i ? = # -> $assumeHash\n");
        $cache[$cacheKey] = $assumeDot + $assumeHash;
        return $assumeDot + $assumeHash;
    }
    throw new RuntimeException("Unknown next $next");
}

$cache = [];
$part1 = 0;
foreach ($lines as $x) {
    [$row, $vals] = explode(" ", $x);
    $values = array_map('intval', explode(",", $vals));

    $lineCount = possibilities($row, 0, $values, $cache);
//    echo("$row -> $lineCount\n");
    $part1 += $lineCount;
}
echo("Part 1: $part1" . PHP_EOL);

echo("####   Part 2:    ####\n");
foreach ($lines as $x) {
    [$row, $vals] = explode(" ", $x);
    $values = array_map('intval', explode(",", $vals));

    $row2 = join("?", array_fill(0, 5, $row));
    $values2 = array_merge($values, $values, $values, $values, $values);

    $lineCount = possibilities($row2, 0, $values2, $cache);
//    echo("$row2 -> $lineCount\n");
    $part2 += $lineCount;
}

echo("Part 2: $part2" . PHP_EOL);
