<?php

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);

echo(json_encode($lines, JSON_PRETTY_PRINT));


$sum1 = 0;
$sum2 = 0;
foreach ($lines as $line) {
    $lastdigit = -1;
    $lastpos = strlen($line);
    $firstdigit = -1;
    $bestpos = 0;
    foreach (str_split($line) as $i => $c) {
        if (is_numeric($c)) {
            $lastpos = $i;
            $lastdigit = $c;
            if ($firstdigit == -1) {
                $bestpos = $i;
                $firstdigit = $c;
            }
        }
    }
    $num1 = intval("" . $firstdigit . $lastdigit);
    echo($num1 . PHP_EOL);
    $sum1 += $num1;

    foreach (["one", "two", "three", "four", "five", "six", "seven", "eight", "nine"] as $i => $val) {
        $pos = strpos($line, $val);
        if ($pos > -1) {
            // Found
            if ($pos < $bestpos) {
                // Add 1 to the index to get the value of the string.
                $firstdigit = $i + 1;
                $bestpos = $pos;
            }
        }
        $pos = strrpos($line, $val);
        if ($pos > -1) {
            // Found
            if ($pos > $lastpos) {
                // Add 1 to the index to get the value of the string.
                $lastdigit = $i + 1;
                $lastpos = $pos;
            }
        }
    }
    $num2 = intval($firstdigit . $lastdigit);
    echo("$line " . $num2 . PHP_EOL);
    $sum2 += $num2;

}

echo("Part 1: $sum1". PHP_EOL);
echo("Part 2: $sum2". PHP_EOL);
