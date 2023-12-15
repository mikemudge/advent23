<?php

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
        $this->data = array_map('str_split', $lines);
        $this->oobValue = null;
    }

    public function getWidth(): int {
        return $this->width;
    }

    public function getHeight(): int {
        return $this->height;
    }
    public function get(int $x, int $y) {
        if ($y < 0 || $y >= $this->height) {
            return $this->oobValue;
        }
        if ($x < 0 || $x >= $this->width) {
            return $this->oobValue;
        }

        return $this->data[$y][$x];
    }

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