<?php

class GridLocation {
    private mixed $data;
    private ?string $key;
    private int $x;
    private int $y;
    private Grid $grid;

    public function __construct(Grid $grid, int $x, int $y, ?string $key) {
        $this->grid = $grid;
        $this->x = $x;
        $this->y = $y;
        $this->key = $key;
        $this->data = null;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }

    public function getKey() {
        return $this->key;
    }

    public function getLocationString() {
        return "$this->x,$this->y";
    }

    public function __toString() {
        return "$this->x,$this->y ($this->key)";
    }

    public function north(): ?GridLocation {
        return $this->grid->get($this->x, $this->y - 1);
    }

    public function south(): ?GridLocation {
        return $this->grid->get($this->x, $this->y + 1);
    }

    public function west(): ?GridLocation {
        return $this->grid->get($this->x - 1, $this->y);
    }

    public function east(): ?GridLocation {
        return $this->grid->get($this->x + 1, $this->y);
    }

    public function getDir(int $dir): ?GridLocation {
        return match ($dir) {
            0 => $this->north(),
            1 => $this->east(),
            2 => $this->south(),
            3 => $this->west(),
            default => throw new RuntimeException("Unknown dir $dir"),
        };
    }

    public function setKey(string $key) {
        $this->key = $key;
    }

    public function getX(): int {
        return $this->x;
    }

    public function getY(): int {
        return $this->y;
    }
}

class Grid {
    private int $height;
    private int $width;
    private array $data;
    private GridLocation $oobValue;

    /**
     * @param string[] $lines
     */
    public function __construct(array $lines) {
        $this->height = count($lines);
        $this->width = strlen($lines[0]);
        $this->data = [];
        for ($y = 0; $y < $this->height; $y++) {
            $line = $lines[$y];
            $this->data[$y] = [];
            for ($x = 0; $x < $this->width; $x++) {
                $this->data[$y][] = new GridLocation($this, $x, $y, $line[$x]);
            }
        }
        $this->oobValue = new GridLocation($this, PHP_INT_MIN, PHP_INT_MIN, null);
    }

    public function getWidth(): int {
        return $this->width;
    }

    public function getHeight(): int {
        return $this->height;
    }
    public function get(int $x, int $y): ?GridLocation {
        if ($y < 0 || $y >= $this->height) {
            return $this->oobValue;
        }
        if ($x < 0 || $x >= $this->width) {
            return $this->oobValue;
        }

        return $this->data[$y][$x];
    }

    /**
     * @return GridLocation[]
     */
    public function getAdjacent(int $x, int $y): array {
        $result = [
            $this->get($x - 1, $y - 1),
            $this->get($x, $y - 1),
            $this->get($x + 1, $y - 1),
            $this->get($x - 1, $y),
            $this->get($x + 1, $y),
            $this->get($x - 1, $y + 1),
            $this->get($x, $y + 1),
            $this->get($x + 1, $y + 1),
        ];
        return $result;
    }

    public function find(string $string): ?GridLocation {
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                if ($this->data[$y][$x]->getKey() == $string) {
                    return $this->data[$y][$x];
                }
            }
        }
        return $this->oobValue;
    }
}