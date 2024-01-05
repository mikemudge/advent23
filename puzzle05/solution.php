<?php

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$contents = file_get_contents(dirname(__FILE__) . "/sample");
//$lines = explode("\n", $contents);

$seeds = array_map('intval', explode(" ", explode("seeds: ", $lines[0])[1]));
echo json_encode($seeds) . PHP_EOL;

$i = 1;
$maps = [];
while($i < count($lines) - 1) {
    $mapName = explode(" map:", $lines[++$i])[0];
    $currentMap = [];
    while($i < count($lines) - 1) {
        $line = $lines[++$i];
        if (!$line) {
            break;
        }
        // Add to current mapping.
        [$dest, $source, $len] = array_map('intval', explode(" ", $line));
        $currentMap[] = [$dest, $source, $len];
        // Use $dest/$source?
    }
    $maps[$mapName] = $currentMap;
    echo($mapName . " " . json_encode($currentMap) . PHP_EOL);
}


function performMapping($map, $sourceId) {
    foreach ($map as $ranges) {
        [$dest, $source, $len] = $ranges;
        if ($sourceId >= $source && $sourceId < $source + $len) {
//            echo("Using $source <= $sourceId < " . ($source + $len));
            return $dest + $sourceId - $source;
        }
    }
    return $sourceId;
}

function fullMapping($maps, $seed, $verbose = true) {
    if ($verbose)
        echo("Seed $seed, ");
    $soil = performMapping($maps['seed-to-soil'], $seed);
    if ($verbose)
        echo("Soil $soil, ");
    $fertilizer = performMapping($maps['soil-to-fertilizer'], $soil);
    if ($verbose)
        echo("Fert $fertilizer, ");
    $water = performMapping($maps['fertilizer-to-water'], $fertilizer);
    if ($verbose)
        echo("Water $water, ");
    $light = performMapping($maps['water-to-light'], $water);
    if ($verbose)
        echo("Light $light, ");
    $temperature = performMapping($maps['light-to-temperature'], $light);
    if ($verbose)
        echo("Temp $temperature, ");
    $humidity = performMapping($maps['temperature-to-humidity'], $temperature);
    if ($verbose)
        echo("Humidity $humidity\n");
    $location = performMapping($maps['humidity-to-location'], $humidity);
    return $location;
}

foreach ($seeds as $seed) {
    $loc = fullMapping($maps, $seed);
    echo("Seed $seed is at location $loc\n");
    if (!$part1) {
        $part1 = $loc;
    } else {
        $part1 = min($loc, $part1);
    }
}
for ($i = 0; $i < count($seeds); $i += 2) {
    $rangeStart = $seeds[$i];
    $rangeLen = $seeds[$i + 1];
    echo("Range: " . $rangeStart . " len: " . $rangeLen . "\n");
}

// This approach doesn't scale, need to work with ranges more efficiently.
//for ($i = 0; $i < count($seeds); $i += 2) {
//    $rangeStart = $seeds[$i];
//    $rangeLen = $seeds[$i + 1];
//    for ($seed = $rangeStart; $seed < $rangeStart + $rangeLen; $seed++) {
//        $loc = fullMapping($maps, $seed, false);
//        if (!$part2) {
//            $part2 = $loc;
//        } else {
//            $part2 = min($loc, $part2);
//        }
//    }
//}

echo("Part 1: $part1". PHP_EOL);
echo("Part 2: $part2". PHP_EOL);
