<?php

namespace Dehare\Advent;

/**
 * Matrix calculation used in Advent of Code 2016 challenges
 *
 * Abstraction layer for creating and populating a grid using PHP arrays
 *
 * @package Dehare\Advent
 */
class Matrix
{
    const VECTOR_COL = 0;
    const VECTOR_ROW = 1;

    public $matrix = [];
    public $width  = 1;
    public $height = 1;
    public $values = [0, 1];

    public function __construct($matrix = [], $width = 1, $height = 1, array $values = [0, 1])
    {
        $this->width  = $width;
        $this->height = $height;
        $this->values = $values;

        if (!empty($matrix)) {
            $this->matrix = $matrix;
            $this->height = count($matrix);
            $this->width  = count($matrix[key($matrix)]);
        } else {
            $this->fill([0, 0], $width, $height);
        }
    }

    /**
     * Gets value at coordinate
     *
     * Quick handle to access coordinates as properties. Use getIndex() for better performance
     *
     * @param  string $coord xy coords
     *
     * @return bool|mixed
     * @example X15Y5
     *
     * @see     getIndex()
     */
    public function __get($coord)
    {
        if (preg_match('/x(\d+)y(\d+)/i', $coord, $m)) {
            return $this->getIndex($m[1], $m[2]);
        }

        return false;
    }

    /**
     * Set coordinate
     * Quick handle to access coordinates as properties. Use setIndex() for better performance
     *
     * @param string $coord
     * @param mixed  $value
     *
     * @return bool
     *
     * @see setIndex()
     */
    public function __set($coord, $value)
    {
        if (preg_match('/x(\d+)y(\d+)/i', $coord, $m)) {
            $this->setIndex($m[1], $m[2], $value);
            return true;
        }

        return false;
    }


    /**
     * Get vector from Matrix
     *
     * @param int $vector
     * @param int $index
     *
     * @return array
     */
    public function getVector($vector, $index) {
        if (($vector == self::VECTOR_COL && $this->width <= $index) || $vector == self::VECTOR_ROW && $this->height <= $index) {
            throw new \InvalidArgumentException('Index out of range');
        }

        if ($vector == self::VECTOR_COL) {
            $this->transpose();
        }

        $result = $this->matrix[$index];

        if ($vector == self::VECTOR_COL) {
            $this->transpose(3);
        }

        return $result;
    }

    /**
     * Translate vector to size
     *
     * @param int $vector
     * @param int $index Optional as rows may differ in length
     *
     * @return int
     */
    public function getVectorSize($vector, $index = null)
    {
        if ($vector == self::VECTOR_ROW) {
            return $index ? count($this->matrix[$index]) : $this->width;
        }

        return $this->height;
    }

    /**
     * Get coordinate
     *
     * @param int $x
     * @param int $y
     *
     * @return mixed
     */
    public function getIndex($x, $y)
    {
        return isset($this->matrix[$y][$x]) ? $this->matrix[$y][$x] : false;
    }

    /**
     * Set coordinate
     *
     * @param int   $x
     * @param int   $y
     * @param mixed $value
     */
    public function setIndex($x, $y, $value)
    {
        $this->matrix[$y][$x] = $value;
    }

    /**
     * Search for matching value within given vertex
     *
     * @param mixed $value
     * @param int   $index
     * @param int   $vertex
     *
     * @return mixed
     */
    public function find($value, $index, $vertex = self::VECTOR_ROW)
    {
        $mx = clone($this);
        if ($vertex == self::VECTOR_COL) {
            $mx->transpose();
        }

        return array_search($value, $mx->matrix[$index]);
    }

    /**
     * Translate coordinate from/to string value
     *
     * @param  int|string|array $x
     * @param  null|int|string  $y
     *
     * @return string|array Translation
     *
     * @example translateCoord(3, 5); => x3y5 <br>
     *          translateCoord([3, 5]); => x3y5 <br>
     *          translateCoord('x3y5'); => [3, 5]
     *
     * @todo    catch invalid arguments
     */
    public function translateCoord($x, $y = null)
    {
        if (!$y && preg_match('/x(\d+)y(\d+)/i', $x, $m)) {
            list($coord, $x, $y) = $m;

            return [$x, $y];
        }

        if (!$y && is_array($x)) {
            list($x, $y) = $x;
        }

        return 'x' . $x . 'y' . $y;
    }

    /**
     * Get adjacent tiles including coordinates and values
     *
     * @param int  $x
     * @param int  $y
     * @param bool $diagonal Lookup diagonal neighbours
     *
     * @return array
     */
    public function getAdjacentTiles($x, $y, $diagonal = false)
    {
        $result = [
            [
                'value' => $this->getIndex($x, $y - 1),
                'coord' => [$x, $y - 1],
            ],
            [
                'value' => $this->getIndex($x, $y + 1),
                'coord' => [$x, $y + 1],
            ],
            [
                'value' => $this->getIndex($x - 1, $y),
                'coord' => [$x - 1, $y],
            ],
            [
                'value' => $this->getIndex($x + 1, $y),
                'coord' => [$x + 1, $y],
            ],
        ];

        if ($diagonal) {
            $diagonal = [
                [
                    'value' => $this->getIndex($x - 1, $y - 1),
                    'coord' => [$x - 1, $y - 1],
                ],
                [
                    'value' => $this->getIndex($x + 1, $y + 1),
                    'coord' => [$x + 1, $y + 1],
                ],
                [
                    'value' => $this->getIndex($x - 1, $y + 1),
                    'coord' => [$x - 1, $y + 1],
                ],
                [
                    'value' => $this->getIndex($x + 1, $y - 1),
                    'coord' => [$x + 1, $y - 1],
                ],
            ];
            $result   = array_merge($result, $diagonal);
        }

        array_walk($result, function (&$v) {
            if ($this->getIndex($v['coord'][0], $v['coord'][1]) === false) {
                $v['value'] = true;
            }
        });

        return $result;
    }

    /**
     * Assign value to each coordinate with an anonymous function
     *
     * @param callable $callback
     * @param bool     $parse_as_value Useful for quickly setting initial declared values to this coordinate
     * @param array    $coords         Starting position for the loop
     * @param null|int $width          Limit X vertex
     * @param null|int $height         Limit Y vertex
     */
    public function loop(callable $callback, $parse_as_value = true, array $coords = [0, 0], $width = null, $height = null)
    {
        $width  = $width ?: ($this->width - $coords[0]);
        $height = $height ?: ($this->height - $coords[1]);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $value = call_user_func_array($callback, [$x, $y, $this->getIndex($x, $y)]);
                if ($value !== null) {
                    $this->matrix[$y + $coords[1]][$x + $coords[0]] = $parse_as_value && (is_bool($value) || is_int($value))
                        ? $this->values[intval($value)]
                        : $value;
                }
            }
        }
    }

    /**
     * Fill area with value
     *
     * @param array $coords   Starting position
     * @param int   $width    Limit X vertex
     * @param int   $height   Limit Y vertex
     * @param int   $valueIdx Index of initialized values
     *
     * @todo Catch out of range
     */
    public function fill(array $coords = [0, 0], $width = 1, $height = 1, $valueIdx = 0)
    {
        if (!isset($this->values[$valueIdx])) {
            throw new \LogicException("No value set for index $valueIdx");
        }

        $value = $this->values[$valueIdx];

        if ($coords[0] == 0 && $coords[1] == 0 && $width == $this->width && $height == $this->height) {
            for ($i = 0; $i < $height; $i++) {
                $this->matrix[] = array_fill(0, $width, $value);
            }
        } else {
            $this->loop(function () use ($value) {
                return $value;
            }, true, $coords, $width, $height);
        }
    }

    /**
     * Compresses all coordinates to two dimensional array, with assigned values
     *
     * @param array    $coords Start position
     * @param null|int $width  Limit X vertex
     * @param null|int $height Limit Y vertex
     *
     * @return array
     */
    public function flatten($coords = [0, 0], $width = null, $height = null)
    {
        $result = [];
        $this->loop(function ($x, $y, $value) use (&$result) {
            $coord          = 'x' . $x . 'y' . $y;
            $result[$coord] = $value;
        }, true, $coords, $width, $height);

        return $result;
    }

    /**
     * Calculate A Star from a starting coordinate
     *
     * Tiles with values are considered walls
     * Note: The algorithm calculates the entire matrix; edge to edge
     *
     * @param array $coord     Starting point
     * @param array $coord_end Return coordinate value at this point
     * @param bool  $diagonal  Move diagonally
     *
     * @return null|int|bool  int: step count, bool: wrong coord end, null: insufficient input
     */
    public function aStar($coord = [0, 0], array $coord_end = [], $diagonal = false)
    {
        $tiles = [
            ['value' => '', 'coord' => $coord],
        ];
        $step  = 0;

        while (count($tiles) > 0) {
            $tc    = $tiles; // copy tiles
            $tiles = []; // clear tiles for repopulation by loop

            foreach ($tc as $tile) {
                list($x, $y) = $tile['coord'];

                if (!$tile['value'] || ($step && $tile['value'] > $step)) {
                    $this->setIndex($x, $y, $step ?: 'S');
                    $tiles = array_merge($tiles, $this->getAdjacentTiles($x, $y, $diagonal));
                }
            }
            $step++;
        }

        if (!empty($coord_end)) {
            return $this->getIndex($coord_end[0], $coord_end[1]);
        }

        return null;
    }

    /**
     * Flip matrix on its side, clockwise
     *
     * @param int $times
     */
    public function transpose($times = 1)
    {
        while ($times) {
            array_unshift($this->matrix, null);
            $this->matrix = call_user_func_array('array_map', $this->matrix);
            $times--;
        }
    }

    /**
     * Shift vector values
     *
     * @param int  $row
     * @param int  $times
     * @param int  $vector
     * @param bool $transpose Use transposing strategy for non-square matrices
     */
    public function rotate($row, $times, $vector = self::VECTOR_ROW, $transpose = true)
    {
        if ($vector == self::VECTOR_COL && $transpose) {
            $this->transpose();
        }

        $v     = $this->getVectorSize($vector, $row);
        $times = $times % $v ?: $times;

        $copy = $this->matrix[$row];
        $head = array_slice($copy, 0, $v - $times);
        $tail = array_slice($copy, 0 - $times);

        $this->matrix[$row] = array_merge($tail, $head);

        if ($vector == self::VECTOR_COL && $transpose) {
            $this->transpose(3);
        }
    }

    /**
     * Print matrix
     *
     * @param  boolean $count
     */
    public function output($count = false)
    {
        $counter = 0;

        if ($this->width / 10 > 1) {
            $sep = '';
            for ($i = 0; $i < $this->width / 10; $i++) {
                $sep .= $i . implode('', range(1, 9));
            }
        } else {
            $sep = str_repeat('=', $this->width);
        }


        echo $sep . nl;
        foreach ($this->matrix as $row) {
            foreach ($row as $col) {
                if ($count !== false && isset($this->values[$count])) {
                    $counter += intval($col == $this->values[$count]);
                }
                echo $col;
            }
            // echo implode('', $r).nl;
            echo nl;
        }
        echo $sep . nl;

        if ($counter) {
            echo $counter . ' values' . nl;
        }
    }
}