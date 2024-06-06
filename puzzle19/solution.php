<?php

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    'px{a<2006:qkq,m>2090:A,rfg}',
//    'pv{a>1716:R,A}',
//    'lnx{m>1548:A,A}',
//    'rfg{s<537:gd,x>2440:R,A}',
//    'qs{s>3448:A,lnx}',
//    'qkq{x<1416:A,crn}',
//    'crn{x>2662:A,R}',
//    'in{s<1351:px,qqz}',
//    'qqz{s>2770:qs,m<1801:hdj,R}',
//    'gd{a>3333:R,R}',
//    'hdj{m>838:A,pv}',
//    '',
//    '{x=787,m=2655,a=1222,s=2876}',
//    '{x=1679,m=44,a=2067,s=496}',
//    '{x=2036,m=264,a=79,s=2244}',
//    '{x=2461,m=1339,a=466,s=291}',
//    '{x=2127,m=1623,a=2188,s=1013}',
//];

$workflows = [];
$parts = [];
$parsingParts = false;
foreach ($lines as $line) {
    if (empty($line)) {
        echo("Blank line reached, parsing parts now\n");
        $parsingParts = true;
        continue;
    }
    if ($parsingParts) {
        //{x=2127,m=1623,a=2188,s=1013}
        $part = [];
        $data = explode(",", substr($line, 1, -1));
        foreach ($data as $val) {
            [$k, $v] = explode("=", $val);
            $part[$k] = $v;
        }
        $parts[] = $part;
        continue;
    }
    // parse a workflow
    [$name, $rest] = explode("{", substr($line, 0, -1));
    $instructions = [];
    $data = explode(",", $rest);
    // The name of the workflow to go to if no conditions match.
    $defaultName = array_pop($data);

    foreach ($data as $ins) {
        // a<2006:qkq
        [$cond, $next] = explode(":", $ins);
        $condKey = $cond[0];
        $condOp = $cond[1];
        $condVal = intval(substr($cond, 2));
        $instructions[] = [
            'cond' => [$condKey, $condOp, $condVal],
            'next' => $next
        ];
    }
    $workflows[$name] = [
        'instructions' => $instructions,
        'next' => $defaultName
    ];
//    echo("$name " . json_encode($instructions) . " else " . $defaultName . PHP_EOL);
}

echo("Processing " . count($workflows) . " workflows and " . count($parts) . " parts\n");

function shouldAccept(array $part, array $workflows, string $w) {
    if ($w == 'R') {
        // The part is rejected
        return false;
    }
    if ($w == 'A') {
        // The part is accepted
        return true;
    }
    // Otherwise apply the current workflow.
    $workflow = $workflows[$w];
    foreach ($workflow['instructions'] as $instruction) {
        [$key, $op, $val] = $instruction['cond'];
        switch ($op) {
            case "<":
                $match = $part[$key] < $val;
                break;
            case ">":
                $match = $part[$key] > $val;
                break;
            default:
                throw new RuntimeException("Unknown op $op");
        }
        if ($match) {
            return shouldAccept($part, $workflows, $instruction['next']);
        }
    }

    // If there are no matches use the next workflow.
    return shouldAccept($part, $workflows, $workflow['next']);
}

$sum = 0;
foreach ($parts as $part) {
    $accept = shouldAccept($part, $workflows, 'in');
    if ($accept) {
        $sum += $part['x'] + $part['m'] + $part['a'] + $part['s'];
    }
}
$part1 = $sum;

// Determine the number of parts which can be accepted.
// Given all parts with x, m, a, s = 1-4000

// Need a concept of a part group with ranges for x,m,a and s.
// Split the group up at each condition.

$partGroup = [
    'x' => [1, 4001],
    'm' => [1, 4001],
    'a' => [1, 4001],
    's' => [1, 4001],
];

function acceptableParts(array $partGroup, array $workflows, string $w) {
    if ($w == 'R') {
        // All parts in this group are rejected.
        return 0;
    }
    if ($w == 'A') {
        // All parts in the group are accepted.
        $dx = $partGroup['x'][1] - $partGroup['x'][0];
        $dm = $partGroup['m'][1] - $partGroup['m'][0];
        $da = $partGroup['a'][1] - $partGroup['a'][0];
        $ds = $partGroup['s'][1] - $partGroup['s'][0];
        // Calculate the size of the part group.
        return $dx * $dm * $da * $ds;
    }

    // Otherwise go through the current workflow.
    $workflow = $workflows[$w];
    $total = 0;
    foreach ($workflow['instructions'] as $instruction) {
        [$key, $op, $val] = $instruction['cond'];
        $matchGroup = $partGroup;
        $nonMatchGroup = $partGroup;

        // s<1351:px
        switch ($op) {
            case "<":
                // s = [1, 1351]
                $matchGroup[$key][1] = min($matchGroup[$key][1], $val);
                // s = [1351, 4001]
                $nonMatchGroup[$key][0] = max($nonMatchGroup[$key][0], $val);
                break;
            case ">":
                // s = [1352, 4001]
                $matchGroup[$key][0] = max($matchGroup[$key][0], $val + 1);
                // s = [1, 1352]
                $nonMatchGroup[$key][1] = min($nonMatchGroup[$key][1], $val + 1);
                break;
            default:
                throw new RuntimeException("Unknown op $op");
        }

        // Check how many matching parts within the matched group using the next workflow.
        $total += acceptableParts($matchGroup, $workflows, $instruction['next']);
        // update the part group to only include the non matches.
        $partGroup = $nonMatchGroup;
    }

    // For the remaining part group check how many are acceptable for the next workflow.
    return $total + acceptableParts($partGroup, $workflows, $workflow['next']);
}

$part2 = acceptableParts($partGroup, $workflows, 'in');

echo("Part 1: $part1" . PHP_EOL);
echo("Part 2: $part2" . PHP_EOL);
