<?php

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    "0 3 6 9 12 15",
//    "1 3 6 10 15 21",
//    "10 13 16 21 30 45",
//];

function recurseDiff($nums) {
    $nonZero = false;
    $diffs = [];
    for ($i = 0; $i < count($nums) - 1; $i++) {
        $diff = $nums[$i + 1] - $nums[$i];
        $diffs[] = $diff;
        if ($diff != 0) {
            $nonZero = true;
        }
    }
    if ($nonZero) {
        // Calculate the result of diffs and add it to the final value in nums.
        $result = recurseDiff($diffs);
        return $nums[count($nums) - 1] + $result;
    } else {
        // Base case is all zeros, and we should return the last value + 0.
        return $nums[count($nums) - 1];
    }
}

function recurseBackwards($nums) {
    $nonZero = false;
    $diffs = [];
    for ($i = 0; $i < count($nums) - 1; $i++) {
        $diff = $nums[$i + 1] - $nums[$i];
        $diffs[] = $diff;
        if ($diff != 0) {
            $nonZero = true;
        }
    }
    if ($nonZero) {
        // Calculate the result of diffs and add it to the final value in nums.
        $result = recurseBackwards($diffs);
        return $nums[0] - $result;
    } else {
        // Base case is all zeros, and we should return the last value + 0.
        return $nums[0];
    }
}

foreach ($lines as $line) {
    $nums = array_map('intval', explode(" ", $line));
    $val = recurseDiff($nums);
    echo(json_encode($nums) . "=$val\n");
    $part1 += $val;

    $val2 = recurseBackwards($nums);
    echo(json_encode($nums) . "=$val2\n");
    $part2 += $val2;
}

echo("Part 1: $part1". PHP_EOL);
echo("Part 2: $part2". PHP_EOL);
