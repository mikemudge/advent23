<?php

require_once 'puzzle05/Range.php';

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
$convertPipeline = [];
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


        $range = new Range($source, $source + $len);
        $mapping = $dest - $source;
        $currentMap[] = [$range, $mapping];
    }

    usort($currentMap, function(array $r1, array $r2) {
        return $r1[0]->getStart() - $r2[0]->getStart();
    });

    // Fill in gaps of currentMap
    $start = 0;
    $currentMap2 = [];
    foreach ($currentMap as $mapItem) {
        /** @var Range $range */
        [$range, $offset] = $mapItem;
        if ($start !== $range->getStart()) {
            // A gap was found, add the gap as a new range.
            $filler = new Range($start, $range->getStart());
            // The mapping for missing ranges is 0.
            $currentMap2[] = [$filler, 0];
        }
        // Put the existing range in the new map.
        $currentMap2[] = [$range, $offset];
        // Shift the new start along to the end of the current range.
        $start = $range->getEnd();
    }

    $convertPipeline[] = [
        'name' => $mapName,
        'map' => $currentMap2
    ];
    echo($mapName . PHP_EOL);
    foreach ($currentMap2 as $map) {
        echo($map[0] . " offsets ". $map[1] . PHP_EOL);
    }
}

function performMapping($map, $sourceId) {
    foreach ($map as $mapItem) {
        /** @var Range $range */
        [$range, $offset] = $mapItem;
        if ($range->contains($sourceId)) {
            return $sourceId + $offset;
        }
    }
    // Beyond the end of known ranges will perform the identity mapping.
    return $sourceId;
}

foreach ($seeds as $seed) {
    $it = $seed;
    foreach ($convertPipeline as $stage) {
        $it = performMapping($stage['map'], $it);
//        echo($stage['name'] . " -> $it\n");
    }
    $loc = $it;
    echo("Seed $seed is at location $loc\n");
    if (!$part1) {
        $part1 = $loc;
    } else {
        $part1 = min($loc, $part1);
    }
}

function getRange($map, $num) {
    foreach ($map['map'] as $mapItem) {
        [$range, $offset] = $mapItem;
        if ($range->contains($num)) {
            return [$range, $offset];
        }
    }
    // Return the remaining integer space with no offset (identity mapping).
    return [new Range($range->getEnd(), PHP_INT_MAX), 0];
}

function rangeMap($map, Range $sourceRange): array {
    $destRanges = [];
    $rangeStart = $sourceRange->getStart();
    while ($rangeStart < $sourceRange->getEnd()) {
        // Find a dest range which covers rangeStart.
        [$range, $offset] = getRange($map, $rangeStart);

//        echo ("Found a range covering $rangeStart " . $range . " with offset $offset\n");

        // Need to determine how much of the source range this covers?

        // Use the max of the 2 range start points.
        $start = max($rangeStart, $range->getStart());
        // And the min of the 2 range end points.
        $end = min($sourceRange->getEnd(), $range->getEnd());

        // Add the offset to convert into the destination range.
        $r = new Range($start + $offset, $end + $offset);
//        echo ("Adding a dest range " . $r . "\n");
        $destRanges[] = $r;

        // $end should always be > start to have some coverage.
        if ($end <= $start) {
            throw new RuntimeException("Coverage is too little for $r");
        }

        $rangeStart = $end;
    }

    return $destRanges;
}

function getMin($maps, $i, Range $sourceRange) {
    if ($i >= count($maps)) {
        // At the end of the convert the min is just the lowest value.
        return $sourceRange->getStart();
    }

//    echo ("Recurse level $i, mapping " . $maps[$i]['name'] . " " . $sourceRange . "\n");

    // Map the current range into its set of destination ranges.
    $destRanges = rangeMap($maps[$i], $sourceRange);

//    echo ("Found " . count($destRanges) . " ranges\n");

    $result = PHP_INT_MAX;
    foreach ($destRanges as $range) {
        // recurse into the next material to find the min of the dest range.
        $tmp = getMin($maps, $i + 1, $range);
        $result = min($result, $tmp);
    }
    return $result;
}

echo("\n### Part 2 ###\n\n");
// Need a different approach to scale for part 2, need to work with ranges more efficiently.
$part2 = PHP_INT_MAX;
for ($i = 0; $i < count($seeds); $i += 2) {
    $rangeStart = $seeds[$i];
    $rangeLen = $seeds[$i + 1];
    $range = new Range($rangeStart, $rangeStart + $rangeLen);
    $tmp = getMin($convertPipeline, 0, $range);
    echo("$range found min $tmp\n");
    $part2 = min($part2, $tmp);
}

echo("Part 1: $part1". PHP_EOL);
echo("Part 2: $part2". PHP_EOL);
