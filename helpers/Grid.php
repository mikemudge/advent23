<?php

class GridLocation {
    private mixed $data;
    private int $x;
    private int $y;

    public function __construct(int $x, int $y, mixed $data) {
        $this->x = $x;
        $this->y = $y;
        $this->data = $data;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }

    public function getLocationString() {
        return "$this->x,$this->y";
    }
}

class Grid {
    private int $height;
    private int $width;
    private array $data;
    private null $oobValue;

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
                $this->data[$y][] = new GridLocation($x, $y, $line[$x]);
            }
        }
        $this->oobValue = null;
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
}