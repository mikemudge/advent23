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
//    '19, 13, 30 @ -2,  1, -2',
//    '18, 19, 22 @ -1, -1, -2',
//    '20, 25, 34 @ -2, -2, -4',
//    '12, 31, 28 @ -1, -2, -1',
//    '20, 19, 15 @  1, -5, -3',
//];

class Hail {

    public int $x;
    public int $y;
    public int $z;
    public int $vx;
    public int $vy;
    public int $vz;

    public function __construct(int $x, int $y, int $z, int $vx, int $vy, int $vz) {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->vx = $vx;
        $this->vy = $vy;
        $this->vz = $vz;
    }

    public function __toString(): string {
        return "$this->x,  $this->y, $this->z @ $this->vx, $this->vy, $this->vz";
    }

    public function pathCollideXY(Hail $h2): array {
        // solve simultaneous equations to determine if paths cross.
        // $px = $h2->x + $h2->vx * $t2 = $this->x + $this->vx * $t1;
        // $py = $h2->y + $h2->vy * $t2 = $this->y + $this->vy * $t1;
        // $t2 = ($this->x - $h2->x + $this->vx * $t1) / $h2->vx;
        // $t2 = ($this->y - $h2->y + $this->vy * $t1) / $h2->vy;
        // ($this->x - $h2->x + $this->vx * $t1) / $h2->vx = ($this->y - $h2->y + $this->vy * $t1) / $h2->vy;
        // ($this->x - $h2->x + $this->vx * $t1) * $h2->vy = ($this->y - $h2->y + $this->vy * $t1) * $h2->vx;
        if (($this->vx * $h2->vy - $this->vy * $h2->vx) == 0) {
            // Parallel paths will never cross.
            return [false, false];
        }

        $t1 = ($this->y * $h2->vx - $h2->y * $h2->vx - $this->x * $h2->vy + $h2->x * $h2->vy) / ($this->vx * $h2->vy - $this->vy * $h2->vx);
        $t2 = ($this->x - $h2->x + $this->vx * $t1) / $h2->vx;
        return [$t1, $t2];
   }

   public function getLocationAtTime(float $t) {
        return [$this->x + $this->vx * $t, $this->y + $this->vy * $t];
   }
}

$hail = [];
foreach($lines as $line) {
    [$position, $velocity] = explode(" @ ", $line);
    [$x, $y, $z] = array_map('intval', explode(", ", $position));
    [$vx, $vy, $vz] = array_map('intval', explode(", ", $velocity));

    $hail[] = new Hail($x, $y, $z, $vx, $vy, $vz);
}

/** @var Hail $h1 */
foreach($hail as $i => $h1) {
    /** @var Hail $h2 */
    foreach($hail as $i2 => $h2) {
        if ($i >= $i2) {
            continue;
        }

//        echo("Hailstone A: $h1\n");
//        echo("Hailstone B: $h2\n");

        [$t, $t2] = $h1->pathCollideXY($h2);
        if ($t === false) {
//            echo("Hailstones' paths are parallel; they never intersect.\n");
        } else if ($t < 0) {
            if ($t2 < 0) {
//                echo("Hailstones' paths crossed in the past for both hailstones.\n");
            } else {
//                echo("Hailstones' paths crossed in the past for hailstone A\n");
            }
        } else if ($t2 < 0) {
//            echo("Hailstones' paths crossed in the past for hailstone B\n");
        } else {
            $loc = $h1->getLocationAtTime($t);
            $where = "outside";
            if ($loc[0] >= 200000000000000 && $loc[0] <= 400000000000000) {
                if ($loc[1] >= 200000000000000 && $loc[1] <= 400000000000000) {
                    $part1++;
                    $where = "inside";
                }
            }
//            echo("Hailstones' paths will cross $where the test area (at x=$loc[0], y=$loc[1])\n");
        }
//        echo("\n");
    }
}

echo("Part 1: $part1" . PHP_EOL);

// Identify a new hailstone which will hit every single hailstone?
// There is some $t[] which meets the following constraints.

function calculatePossibleGradients(int $vx, int $diff): array {
    // Given 2 parallel lines which are $diff apart, what gradients could intersect both paths.
    $possible = [];

    // For a more efficient result
    // get factors of $diff, and offset them by $vx.
    for ($i = 1; $i <= sqrt($diff); $i++) {
        if ($diff % $i == 0) {
            // this is a factor (as is $diff / $i)
            $possible[] = $vx + $i;
            $possible[] = $vx - $i;
            $other = $diff / $i;
            if ($other != $i) {
                // Don't add the sqrt again.
                $possible[] = $vx + $other;
                $possible[] = $vx - $other;
            }
        }
    }
    sort($possible);
    return $possible;
}

class GradientChecker {
    private array $parallels;
    private array $gradients;
    private string $axis;

    public function __construct(string $axis) {
        $this->axis = $axis;
        $this->parallels = [];
        $this->gradients = [];
    }

    public function check(int $v, $d) {
        if (isset($this->parallels[$v])) {
            $diff = abs($d - $this->parallels[$v]);
            echo("Lines $diff apart with a grad of $v\n");
            // If the lines are the same, then any gradient could hit them both.
            if ($diff !== 0) {
                $posGrads = calculatePossibleGradients($v, $diff);
                if (!$this->gradients) {
                    $this->gradients = $posGrads;
                } else {
                    $this->gradients = array_values(array_intersect($this->gradients, $posGrads));
                    echo("Possible $this->axis gradients = " . join(",", $this->gradients) . "\n");
                }
            }
        }
        $this->parallels[$v] = $d;
    }

    public function getGradients() {
        return $this->gradients;
    }
}

$parallelXs = [];
$parallelYs = [];
$parallelZs = [];

$xGradients = [];
$yGradients = [];
$zGradients = [];
$xGrad = null;
$x = new GradientChecker('x');
$y = new GradientChecker('y');
$z = new GradientChecker('z');
foreach ($hail as $h) {
    $x->check($h->vx, $h->x);
    if (count($x->getGradients()) == 1) {
        break;
    }
}
foreach ($hail as $h) {
    $y->check($h->vy, $h->y);
    if (count($y->getGradients()) == 1) {
        break;
    }
}
foreach ($hail as $h) {
    $z->check($h->vz, $h->z);
    if (count($z->getGradients()) == 1) {
        break;
    }
}

$xGrad = $x->getGradients();
$yGrad = $y->getGradients();
$zGrad = $z->getGradients();

// Should only be 1 value in this set.
echo("Intersection of x gradients = " . join(",", $xGrad) . "\n");
echo("Intersection of y gradients = " . join(",", $yGrad) . "\n");
echo("Intersection of z gradients = " . join(",", $zGrad) . "\n");

// Intersection of x gradients = -86
// Intersection of y gradients = -143
// Intersection of z gradients = 289

$vx = $xGrad[0];
$vy = $yGrad[0];
$vz = $zGrad[0];
foreach ($hail as $h) {
    if ($h->vx == $vx) {
        echo("X must start at $h->x\n");
    }
    if ($h->vy == $vy) {
        echo("Y must start at $h->y\n");
    }
    if ($h->vz == $vz) {
        echo("Z must start at $h->z\n");
    }
}

$x = 334948624416533;
//should be able to calculate time using this.
foreach ($hail as $i => $h) {
    $dvx = $h->vx - $vx;
    $dvy = $h->vy - $vy;
    $dvz = $h->vz - $vz;
    if ($dvx != 0) {
        $t = ($x - $h->x) / $dvx;
        $y = $h->y + $t * $dvy;
        $z = $h->z + $t * $dvz;
        echo ("Hail i starting y = $y and z = $z hits at time $t\n");
        $part2 = $x + $y + $z;
    } else {
        echo ("Hail i at any time\n");
    }
}

// Need to calcuate a time so we can calculate missed axis;


echo("Part 2: $part2" . PHP_EOL);
