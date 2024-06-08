<?php

require_once 'helpers/Grid.php';

$contents = file_get_contents(dirname(__FILE__) . "/input");
$lines = explode("\n", $contents);
$part1 = 0;
$part2 = 0;

// sample
//$lines = [
//    '1,0,1~1,2,1',
//    '0,0,2~2,0,2',
//    '0,2,3~2,2,3',
//    '0,0,4~0,2,4',
//    '2,0,5~2,2,5',
//    '0,1,6~2,1,6',
//    '1,1,8~1,1,9',
//];
class Block {
    private int $x1;
    private int $x2;
    private int $y1;
    private int $y2;
    private int $z1;
    private int $z2;
    private bool $fallen;
    private array $supportBlocks;
    private array $supporting;
    private int $id;

    public function __construct($id, $x1, $y1, $z1, $x2, $y2, $z2) {
        $this->id = $id;
        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->z1 = $z1;
        $this->x2 = $x2;
        $this->y2 = $y2;
        $this->z2 = $z2;
        $this->fallen = false;
        $this->supporting = [];
    }

    public function add(array &$tetris): void {
        for ($x = $this->getX1(); $x <= $this->getX2(); $x++) {
            for ($y = $this->getY1(); $y <= $this->getY2(); $y++) {
                for ($z = $this->getZ1(); $z <= $this->getZ2(); $z++) {
                    $tetris[$x][$y][$z] = $this;
                }
            }
        }
    }

    public function remove(array &$tetris): void {
        for ($x = $this->getX1(); $x <= $this->getX2(); $x++) {
            for ($y = $this->getY1(); $y <= $this->getY2(); $y++) {
                for ($z = $this->getZ1(); $z <= $this->getZ2(); $z++) {
                    if ($tetris[$x][$y][$z] != $this) {
                        throw new RuntimeException("Can't remove a block which isn't set");
                    }
                    $tetris[$x][$y][$z] = null;
                }
            }
        }
    }

    public function fall(array &$tetris) {
        if ($this->z2 < $this->z1) {
            throw new RuntimeException("Expecting z1 to always be the lower z");
        }
        $fallAmount = -1;
        $supportBlocks = [];
        while (empty($supportBlocks)) {
            $fallAmount++;
            // try and fall 1 more.
            $z = $this->z1 - $fallAmount - 1;
            if ($z == 0) {
                // This is on the ground, can't fall more than this.
                break;
            }
            for ($x = $this->getX1(); $x <= $this->getX2(); $x++) {
                for ($y = $this->getY1(); $y <= $this->getY2(); $y++) {
                    // If any locations would block it, then we can't fall.
                    if ($tetris[$x][$y][$z]) {
                        $supportBlocks[] = $tetris[$x][$y][$z];
                    }
                }
            }
        }
        $this->supportBlocks = array_unique($supportBlocks, SORT_REGULAR);
        $this->z1 -= $fallAmount;
        $this->z2 -= $fallAmount;
        $this->fallen = true;
    }

    public function getX1(): int {
        return $this->x1;
    }

    public function getX2(): int {
        return $this->x2;
    }

    public function getY1(): int {
        return $this->y1;
    }

    public function getY2(): int {
        return $this->y2;
    }

    public function getZ1(): int {
        return $this->z1;
    }

    public function getZ2(): int {
        return $this->z2;
    }

    public function hasFallen() {
        return $this->fallen;
    }

    public function getSupportBlocks(): array {
        return $this->supportBlocks;
    }

    public function addAbove(Block $block) {
        $this->supporting[] = $block;
    }

    public function canDisintegrate() {
        // Check the blocks which this block is supporting.
        // We want to make sure they would still be supported without this one.
        /** @var Block $block */
        foreach($this->supporting as $block) {
            if (count($block->getSupportBlocks()) == 1) {
                // This block is the only one supporting block.
                return false;
            }
        }
        // If all the blocks supported by this one are also supported by another we can disintegrate.
        return true;
    }

    public function getId() {
        return $this->id;
    }

    public function disintegrateAndGetFallCount() {
        // We need to maintain a unique set of blocks which would fall
        $visited = [];
        $total = 0;
        // This block is disintegrated, so should be considered fallen (not supporting anything any more)
        $visited[$this->getId()] = true;
        // Now recursively check if any blocks can fall.
        $couldFall = $this->supporting;
        while($couldFall) {
            $unmoved = [];
            $next = [];
            /** @var Block $b */
            foreach($couldFall as $b) {
                if (isset($visited[$b->getId()])) {
                    // Already fallen.
                    continue;
                }

                $hasSupport = $b->stillSupported($visited);
                if ($hasSupport) {
                    // Some block is still holding $b up, but it could still fall in future.
                    $unmoved[] = $b;
                } else if (!$hasSupport) {
                    // Block $b will fall.
                    $visited[$b->getId()] = true;
                    // count it
                    $total++;
                    // Queue up the ones above it for considering
                    foreach ($b->supporting as $b2) {
                        $next[] = $b2;
                    }
                }
            }
            if (empty($next)) {
                // There was no additional blocks which might be able to fall.
                // the configuration has become stable.
                return $total;
            }
            $couldFall = array_merge($next, $unmoved);
        }
        return $total;
    }

    public function getSupporting() {
        return $this->supporting;
    }

    private function stillSupported(array $visited) {
        // check if any of the blocks supporting this one are still holding it up.
        foreach ($this->supportBlocks as $b) {
            if (!isset($visited[$b->getId()])) {
                // Block $b is not fallen, so will hold this one up.
                return true;
            }
        }
        // Could not find a block which is supporting this one.
        return false;
    }
}

// parse the lines into blocks.
$blocks = [];
foreach ($lines as $b => $line) {
    [$l,$r] = explode("~", $line);
    [$x1,$y1,$z1] = explode(",", $l);
    [$x2,$y2,$z2] = explode(",", $r);

    $blocks[] = new Block(
        $b,
        intval($x1),
        intval($y1),
        intval($z1),
        intval($x2),
        intval($y2),
        intval($z2),
    );
}

// Setup the 3d grid
$tetris = [];
for ($y = 0; $y <= 10; $y++) {
    for ($x = 0; $x <= 10; $x++) {
        $tetris[$x][$y] = array_fill(0, 1000, null);
    }
}

/** @var Block $block */
foreach ($blocks as $block) {
    $block->add($tetris);
}

// Falling logic?
// Need to start at the bottom and lower bricks as they are found?
for ($z = 1; $z < 1000; $z++) {
    for ($x = 0; $x <= 10; $x++) {
        for ($y = 0; $y <= 10; $y++) {
            if ($tetris[$x][$y][$z]) {
                /** @var Block $block */
                $block = $tetris[$x][$y][$z];
                if (!$block->hasFallen()) {
                    $b = $block->getId();
                    echo("Block $b is falling now\n");
                    // lower this block as much as possible.
                    // Remember to clear the area it was occupying as well.
                    $block->remove($tetris);
                    $block->fall($tetris);
                    $block->add($tetris);
                }
            };
        }
    }
}

/** @var Block $block */
foreach ($blocks as $b=>$block) {
    if (!$block->hasFallen()) {
        echo("All blocks should have fallen by this point, block $b hasn't\n");
    }
    /** @var Block $support */
    foreach ($block->getSupportBlocks() as $support) {
        $support->addAbove($block);
    }
}

// Get number of bricks which would fall if each brick was disintegrated?
/** @var Block $block */
foreach ($blocks as $block) {
    $fallCount = $block->disintegrateAndGetFallCount();
//    echo("Block " . $block->getId() . " has a fallCount: $fallCount\n");
    if ($fallCount == 0) {
        if (!$block->canDisintegrate()) {
            echo("Block " . $block->getId() . " can't disintegrate? but has no fallCount\n");
            foreach ($block->getSupporting() as $b) {
                echo("Above: " . $b->getId() . " which has " . count($b->getSupportBlocks()) . " supporting blocks\n");
            }
        }
        $part1++;
    }
    $part2 += $fallCount;
}

echo("Part 1: $part1" . PHP_EOL);

// 84878 is too high (wasn't considering alternative stable columns of support)
// 4577 is too low (bug in the code, wasn't iterating through all the items in $couldFall, just the first one then calculating next.
// 63166 was just right
echo("Part 2: $part2" . PHP_EOL);
