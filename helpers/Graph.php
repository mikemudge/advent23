<?php

class GraphNode {
    private Graph $graph;
    private string $id;
    private array $data;
    private array $children;

    public function __construct(Graph $graph, string $id) {
        $this->graph = $graph;
        $this->id = $id;
        $this->data = [];
        $this->children = [];
    }

    public function addChild(GraphNode $b, int $dis): void {
        $this->children[] = [$b, $dis];
    }

    public function getId(): string {
        return $this->id;
    }

    public function getChildren() {
        return $this->children;
    }

}

class Graph {
    private array $nodes;

    public function __construct() {
        $this->nodes = [];
    }

    public function addNode(string $id): GraphNode {
        if (!array_key_exists($id, $this->nodes)) {
            $this->nodes[$id] = new GraphNode($this, $id);
        }
        return $this->nodes[$id];
    }

    public function connection(GraphNode $a, GraphNode $b, int $dis): void {
        $a->addChild($b, $dis);
        $b->addChild($a, $dis);
    }

    public function listNodes() {
        /** @var GraphNode $node */
        foreach ($this->nodes as $node) {
            $connections = [];
            foreach ($node->getChildren() as $child) {
                $connections[] .= $child[1] . " to " . $child[0]->getId();
            }
            echo($node->getId() . " connections: " . join(" and ", $connections) . "\n");
        }
    }
}
