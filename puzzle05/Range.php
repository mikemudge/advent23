<?php

class Range {

    private int $start;
    private int $end;

    public function __construct(int $start, int $end) {
        $this->start = $start;
        $this->end = $end;
    }

    public function getStart() {
        return $this->start;
    }

    public function getEnd() {
        return $this->end;
    }

    public function __toString() {
        return $this->start . '->' . $this->end;
    }

    public function contains(int $val): bool {
        return $val >= $this->start && $val < $this->end;
    }
}