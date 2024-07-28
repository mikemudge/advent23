<?php
ini_set('xdebug.max_nesting_level', 10000);

require_once 'helpers/Grid.php';
require_once 'helpers/Graph.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    "jqt: rhn xhk nvd",
//    "rsh: frs pzl lsr",
//    "xhk: hfx",
//    "cmg: qnr nvd lhk bvb",
//    "rhn: xhk bvb hfx",
//    "bvb: xhk hfx",
//    "pzl: lsr hfx nvd",
//    "qnr: nvd",
//    "ntq: jqt hfx bvb xhk",
//    "nvd: lhk",
//    "lsr: lhk",
//    "rzs: qnr cmg lsr rsh",
//    "frs: qnr lhk lsr",
//];

$graph = new Graph();
$edges = [];
foreach ($lines as $line) {
    [$name, $rest] = explode(": ", $line);
    $names = explode(" ", $rest);
    $me = $graph->addNode($name);
    foreach($names as $childName) {
        $node = $graph->addNode($childName);
        $me->addChild($node, 1);
        $node->addChild($me, 1);
        $edges[] = [$me, $node];
    }
}


$edgeCounts = [];
foreach ($graph->getNodes() as $node) {
    $cnt = count($node->getChildren());
//    echo("Node edges: " . $cnt . PHP_EOL);
    if (!array_key_exists($cnt, $edgeCounts)) {
        $edgeCounts[$cnt] = 0;
    }
    $edgeCounts[$cnt]++;
}


function findCluster(Graph $graph, GraphNode $startNode) {
    $cluster = [$startNode->getId() => true];

    while(true) {
        $bestAdds = [];
        $bestConnections = 0;
        /** @var GraphNode $node */
        foreach ($graph->getNodes() as $node) {
            if (array_key_exists($node->getId(), $cluster)) {
                // Already in the cluster, skip.
                continue;
            }
            // See how many connections the node has to the cluster
            $connections = 0;
            foreach($node->getReachable() as $n=>$t) {
                if (array_key_exists($n, $cluster)) {
                    $connections++;
                }
            }
            if ($connections == $bestConnections) {
                // Add the node as an option.
                $bestAdds[] = $node;
            } else if ($connections > $bestConnections) {
                $bestAdds = [$node];
                $bestConnections = $connections;
            }
        }
        $size = count($cluster);
        $canAdd = null;
        // If its connected to 4 nodes of the cluster then its definitely in the cluster.
        // We will add on 2+ connections (assuming the edges we cut are not to the same node).
        if ($bestConnections >= 2) {
            // Just add the first one?
            // TODO randomly add one?
            $canAdd = $bestAdds[0];
        } else if ($bestConnections == 1) {
            // If there are only nodes which can connect to 1 cluster member what do we do?
            // Check how many we have and pick one at random?
            // When the cluster is small we want it to grow.
            // When there are many nodes which can be added we also take the risk of growing it.
            if ($size < 50 || count($bestAdds) > 15) {
                // Tiny cluster should always grow
                $canAdd = $bestAdds[array_rand($bestAdds)];
            }
        }
        // 0 nodes is never added (should only happen when the cluster is everything)

        if ($canAdd) {
            // grow the cluster
            $cluster[$canAdd->getId()] = true;
        } else {
            // The cluster can't grow any more.
            return $cluster;
        }
    }
}

$clusterSizes = [];
$largest = 0;
// Finding pairs of nodes which cannot be separated with 3 missing edges?
foreach ($graph->getNodes() as $startNode) {
    $cluster = findCluster($graph, $startNode);
    $size = count($cluster);
    if ($size > 5) {
        echo($startNode->getId() . " has cluster size $size\n");
    }
    if (!isset($clusterSizes[$size])) {
        $clusterSizes[$size] = 0;
    }
    $clusterSizes[$size]++;
    $largest = max($largest, $size);
}
// Statistically this should find the most common cluster sizes more often.
// It can overshoot occasionally, but the correct answers should be clear after running from every node.
echo("Sizes: " . json_encode($clusterSizes, JSON_PRETTY_PRINT) . PHP_EOL);

echo("Largest: $largest\n");
echo("Nodes: " . count($graph->getNodes()) . PHP_EOL);
echo("Edges: " . count($edges) . PHP_EOL);
echo(json_encode($edgeCounts) . PHP_EOL);


echo("Part 1: $part1" . PHP_EOL);
echo("Part 2: $part2" . PHP_EOL);
