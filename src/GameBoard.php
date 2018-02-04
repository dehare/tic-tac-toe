<?php

namespace Dehare\TicTacGame;


use Dehare\Advent\Matrix;

class GameBoard extends Matrix
{
    const DIMENSION = 3;

    public function __construct(array $matrix = [], int $width = self::DIMENSION, int $height = self::DIMENSION, array $values = [0, 1])
    {
        parent::__construct($matrix, $width, $height, $values);
    }

    /**
     * @return int
     */
    public function getOpenSlots(): int
    {
        $slots = 0;
        $this->loop(function ($x, $y, $value) use (&$slots) {
            if (empty($value)) {
                $slots++;
            }
            return $value;
        });

        return $slots;
    }

    /**
     * Check all vectors for (given) chain
     *
     * @param null|string $value
     *
     * @return bool|string
     */
    public function getChain(?string $value = null)
    {
        // check rows and columns
        for ($i = 0; $i < self::DIMENSION; $i++) {
            $row = $this->getVector(Matrix::VECTOR_ROW, $i);
            if ($this->vectorHasChain($row, $value)) {
                return $value ?: array_unique($row)[0];
            }
            $col = $this->getVector(Matrix::VECTOR_COL, $i);
            if ($this->vectorHasChain($col, $value)) {
                return $value ?: array_unique($col)[0];
            }
        }

        // check diagonal lines
        $diagVector = [
            [
                [0, 0],
                [1, 1],
                [2, 2],
            ], [
                [2, 0],
                [1, 1],
                [0, 2],
            ],
        ];
        foreach ($diagVector as $coords) {
            $vector = [];
            foreach ($coords as $coord) {
                list($x, $y) = $coord;
                $vector[] = $this->getIndex($x, $y);
            }

            if ($this->vectorHasChain($vector, $value)) {
                return $value ?: array_unique($vector)[0];
            }
        }

        return false;
    }

    /**
     * Check if vector contains (given) chain
     *
     * @param array $vector
     * @param null  $value
     *
     * @return bool
     */
    private function vectorHasChain(array $vector, ?string $value = null): bool
    {
        $unique = array_unique($vector);
        if (count($unique) == 1) {
            return strtolower($unique[0]) == strtolower($value);
        }

        return false;
    }
}