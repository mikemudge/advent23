<?php

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample 2
//$lines = [
//    'broadcaster -> a',
//    '%a -> inv, con',
//    '&inv -> b',
//    '%b -> con',
//    '&con -> output',
//];

// sample
//$lines = [
//    'broadcaster -> a, b, c',
//    '%a -> b',
//    '%b -> c',
//    '%c -> inv',
//    '&inv -> a',
//];

// Graph stuff.
// With state.

class ModuleNode {
    private string $name;
    private ?string $op;
    private array $destinations;

    public function __construct($name, $op) {
        $this->name = $name;
        $this->op = $op;
        $this->destinations = [];
    }

    public function addDestination(ModuleNode $n): void {
        $this->destinations[] = $n;
    }

    public function getDestinations() {
        return $this->destinations;
    }

    public function getOp() {
        return $this->op;
    }

    public function getName() {
        return $this->name;
    }
}

$flipFlopStates = [];
$conjuctionStates = [];
// Add nodes for all the lines.
foreach ($lines as $line) {
    [$p1,] = explode(' -> ', $line);
    $op = $p1[0];
    if ($op == '%' || $op == '&') {
        $name = substr($p1, 1);
        if ($op == '%') {
            // Flip-flop modules (prefix %) are either on or off; they are initially off.
            $flipFlopStates[$name] = false;
        } else {
            $conjuctionStates[$name] = [];
        }
    } else {
        $name = $p1;
    }
    $nodes[$name] = new ModuleNode($name, $op);
}

// Connect nodes.
foreach ($lines as $line) {
    [$p1, $p2] = explode(' -> ', $line);
    $op = $p1[0];
    if ($op == '%' || $op == '&') {
        $name = substr($p1, 1);
    } else {
        $name = $p1;
    }
    $node = $nodes[$name];

    $next = explode(", ", $p2);
    foreach($next as $n) {
        // Conjunction modules (prefix &) remember the type of the most recent pulse received from each of their connected input modules;
        // they initially default to remembering a low pulse for each input.
        if (isset($conjuctionStates[$n])) {
            $conjuctionStates[$n][$name] = false;
        }
        if (!isset($nodes[$n])) {
            // An untyped module (referenced but not defined).
            $nodes[$n] = new ModuleNode($n, null);
        }
        $node->addDestination($nodes[$n]);
    }
}

$broadcaster = $nodes['broadcaster'];
$lowPulses = 0;
$highPulses = 0;

for ($b = 0; $b < 1000; $b++) {
    // When the button is pushed the $broadcaster forwards a low pulse to all its destinations.
    $lowPulses++;
//    echo("button -low -> broadcaster\n");
    $toProcess = [];
    foreach ($broadcaster->getDestinations() as $init) {
        $toProcess[] = [false, $init, $broadcaster->getName()];
    }
    // Then we need to process all the pulses which this causes.
    while($toProcess) {
        $nextPulses = [];
        foreach ($toProcess as $pulse) {
            /** @var ModuleNode $node */
            [$pulseType, $node, $inputName] = $pulse;
            $name = $node->getName();

//            if ($pulseType) {
//                echo("$inputName -high -> $name\n");
//            } else {
//                echo("$inputName -low -> $name\n");
//            }
            // Count it
            if ($pulseType) {
                $highPulses++;
            } else {
                $lowPulses++;
            }

            $op = $node->getOp();
            if ($op == null) {
                // an untyped module should do nothing (but the pulse is still counted).
                continue;
            }
            if ($op == '%') {
                if ($pulseType) {
                    // If a flip-flop module receives a high pulse, it is ignored and nothing happens.
                    continue;
                } else {
                    // However, if a flip-flop module receives a low pulse, it flips between on and off.
                    if (!$flipFlopStates[$name]) {
                        // If it was off, it turns on and sends a high pulse.
                        $flipFlopStates[$name] = true;
                        foreach ($node->getDestinations() as $n) {
                            $nextPulses[] = [true, $n, $name];
                        }
                    } else {
                        // If it was on, it turns off and sends a low pulse.
                        $flipFlopStates[$name] = false;
                        foreach ($node->getDestinations() as $n) {
                            $nextPulses[] = [false, $n, $name];
                        }
                    }
                }
            } else if ($op == '&') {
                // When a pulse is received, the conjunction module first updates its memory for that input.
                $conjuctionStates[$name][$inputName] = $pulseType;
                $sendPulseType = false;
                // Then, if it remembers high pulses for all inputs, it sends a low pulse;
                // otherwise, it sends a high pulse.
                foreach ($conjuctionStates[$name] as $key => $input) {
                    if (!$input) {
                        $sendPulseType = true;
                        break;
                    }
                }
                foreach ($node->getDestinations() as $n) {
                    $nextPulses[] = [$sendPulseType, $n, $name];
                }
            } else {
                throw new RuntimeException("Unknown node type $op");
            }
        }
        $toProcess = $nextPulses;
    }
    echo("button press $b: high=$highPulses, low=$lowPulses\n");
}

echo("high=$highPulses, low=$lowPulses\n");
$part1 = $highPulses * $lowPulses;

echo("Part 1: $part1" . PHP_EOL);

// Part 2 requires understanding the "code" to figure out how many button presses it takes to get a low pulse

foreach ($conjuctionStates as $node => $input) {
    echo("$node = " . join(" & ", array_keys($input)) . PHP_EOL);
}

// rd = hc
// hc = df & fr & nl & vg & gq & ks & lq & lc

// bt = qt
// qt = xm & fp & cs & kz & zq & rp & gb & fz

// fv = ck
// ck = xz & jn & rb & hh & rq & bg & ts & jl

// pr = kb
// kb = gt & pp & gv & ps & rf & gc & gn

// This is the combination of all of the above.
// rx = vd
// vd = rd & bt & fv & pr


// How do the flip flops update?
// broadcaster -> gn, gb, rb, df
//
// %gn -> kb, tn
// &kb -> pv, pr, tn, nm, pf, gn, gd
// %tn -> pf
// %pv -> ps
// &pr -> vd
// %nm -> gt
// %pf -> gd


// The other way around
// kb = gt & pp & gv & ps & rf & gc & gn

// %gn -> kb, tn
// %tn -> pf
// %pf -> gd
// %gd -> gc
// %gc -> kb, pv
// %pv -> ps
// %ps -> kb, rf
// %rf -> kb, nm
// %nm -> gt
// %gt -> pp, kb
// %pp -> gv, kb
// %gv -> kb
// &kb -> pv, pr, tn, nm, pf, gn, gd
// broadcaster -> gn, gb, rb, df

// It makes a 12 bit counter which resets once it hits 3793 (a prime)
// The other broadcaster targets all have 12 bit counters for different primes as well.
// RX will only be set when all counters are at their target in the same button press.
// aka multiply the 4 primes together.
// Via pen and paper diagrams I found the primes (3793, 3911, 3917 and 3929)

$part2 = (3793 * 3911 * 3917 * 3929);

echo("Part 2: $part2" . PHP_EOL);
