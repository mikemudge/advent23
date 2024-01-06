<?php

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

//sample
//$lines = [
//    "32T3K 765",
//    "T55J5 684",
//    "KK677 28",
//    "KTJJT 220",
//    "QQQJA 483"
//];

$strengths = ["2", "3", "4", "5", "6", "7", "8", "9", "T", "J", "Q", "K", "A"];
$strengths2 = ["J", "2", "3", "4", "5", "6", "7", "8", "9", "T", "Q", "K", "A"];


$types = [
    'High Card', // 0
    'Pair', // 1
    'Two Pair', // 2
    'Three of a kind', // 3
    'Full house', // 4
    'Four of a kind', // 5
    'Five of a kind' // 6
];
function determineHandType($hand) {
    $map = [];
    // High card is a minimum type.
    $type = 0;
    foreach (str_split($hand) as $c) {
        $map[$c]++;
    }
    foreach ($map as $cnt) {
        if ($cnt == 2) {
            if ($type == 1) {
                // If we already have a pair of something, then its a two pair.
                $type = 2;
            } else if ($type == 3) {
                // If we already have a 3 of a kind, then its a full house.
                $type = 4;
            } else {
                // We have a pair.
                $type = max($type, 1);
            }
        } else if ($cnt == 3) {
            if ($type == 1) {
                // If we already have a pair of something, then its a full house.
                $type = 4;
            } else {
                // We have a 3 of a kind.
                $type = 3;
            }
        } else if ($cnt == 4) {
            // We have a 4 of a kind.
            $type = 5;
        } else if ($cnt == 5) {
            // We have a 5 of a kind.
            $type = 6;
        }
    }
    return $type;
}

function determineHandType2($hand) {
    $map = [];
    $j = 0;
    foreach (str_split($hand) as $c) {
        if ($c == "J") {
            $j++;
            continue;
        }
        $map[$c] = ($map[$c] ?? 0) + 1;
    }
    // High card is a minimum type.
    $type = 0;
    foreach ($map as $cnt) {
        if ($cnt == 2) {
            if ($type == 1) {
                // If we already have a pair of something, then its a two pair.
                $type = 2;
            } else if ($type == 3) {
                // If we already have a 3 of a kind, then its a full house.
                $type = 4;
            } else {
                // We have a pair.
                $type = max($type, 1);
            }
        } else if ($cnt == 3) {
            if ($type == 1) {
                // If we already have a pair of something, then its a full house.
                $type = 4;
            } else {
                // We have a 3 of a kind.
                $type = 3;
            }
        } else if ($cnt == 4) {
            // We have a 4 of a kind.
            $type = 5;
        } else if ($cnt == 5) {
            // We have a 5 of a kind.
            $type = 6;
        }
    }

    // Now we know the type without jokers.
    // How can we improve the hand with $j?
    if ($j == 5 || $j == 4) {
        // We have a 5 of a kind
        $type = 6;
    } else if ($j == 3) {
        // With 3 jokers
        if ($type == 1) {
            // With an existing pair, we can make a 5 of a kind.
            $type = 6;
        } else {
            // 2 different cards, we can make a 4 of a kind.
            $type = 5;
        }
    } else if ($j == 2) {
        // With 2 jokers
        if ($type == 3) {
            // With an existing 3 of a kind, we can make a 5 of a kind.
            $type = 6;
        } else if ($type == 1) {
            // With an existing pair, we can make a 4 of a kind.
            $type = 5;
        } else {
            // 3 different cards, we can make a 3 of a kind.
            $type = 3;
        }
    } else if ($j == 1) {
        // With 1 joker
        if ($type == 5) {
            // Four of a kind becomes 5 of a kind.
            $type = 6;
        } else if ($type == 3) {
            // Three of a kind becomes 4 of a kind.
            $type = 5;
        } else if ($type == 2) {
            // Two pair becomes full house.
            $type = 4;
        } else if ($type == 1) {
            // A pair becomes 3 of a kind.
            $type = 3;
        } else if ($type == 0) {
            // High card becomes a pair.
            $type = 1;
        }
    }
    return $type;
}

foreach ($lines as $line) {
    [$hand, $bid] = explode(" ", $line);
    $bid = intval($bid);

    $type = determineHandType($hand);
    // rank hands?
    $handVals = [];
    foreach (str_split($hand) as $c) {
        $handVals[] = array_search($c, $strengths);
    }
    $sortable[] = [$type, $handVals, $bid, $hand];
}

sort($sortable);

$rank = 1;
foreach ($sortable as $item) {
    [$type, $handVals, $bid, $hand] = $item;
    echo("$rank $hand $type $bid\n");
    $part1 += $rank * $bid;
    $rank++;
}

echo("\n### Part 2 ###\n\n");

foreach ($lines as $line) {
    [$hand, $bid] = explode(" ", $line);
    $bid = intval($bid);

    $type2 = determineHandType2($hand);
    $handVals2 = [];
    foreach (str_split($hand) as $c) {
        $handVals2[] = array_search($c, $strengths2);
    }
    $sortable2[] = [$type2, $handVals2, $bid, $hand];
}
sort($sortable2);

$rank = 1;
foreach ($sortable2 as $item) {
    [$type, $handVals, $bid, $hand] = $item;
    echo("$rank $hand $type $bid\n");
    $part2 += $rank * $bid;
    $rank++;
}

echo("Part 1: $part1". PHP_EOL);

// 248883982 is too high.
echo("Part 2: $part2". PHP_EOL);

// This should get a type = 6 (5 of a kind);
$hand = "QJQQJ";
$type = determineHandType2($hand);
echo "$hand $type\n";