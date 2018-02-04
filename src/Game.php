<?php

namespace Dehare\TicTacGame;

class Game
{
    const PLAYERS = ['X', 'O'];

    /** @var GameBoard */
    private $board;

    /**
     * Game constructor.
     *
     * @param array $board
     */
    public function __construct(array $board = [])
    {
        $this->board = new GameBoard($board, 3, 3, array_merge([''], static::PLAYERS));
    }

    /**
     * @param bool $asArray
     *
     * @return array|GameBoard
     */
    public function getBoard($asArray = true)
    {
        return $asArray ? $this->board->matrix : $this->board;
    }

    public function getStatus()
    {
        $result = [
            'board' => $this->board->matrix,
        ];

        foreach (static::PLAYERS as $key => $player) {
            if ($this->board->getChain($this->board->values[$key + 1])) {
                return $result + ['status' => 'win', 'player' => $key + 1];
            }
        }
        if (!$this->board->getOpenSlots()) {
            return $result + ['status' => 'draw'];
        }

        $playerMoves = array_combine(static::PLAYERS, [0, 0]);
        $this->board->loop(function ($x, $y, $value) use (&$playerMoves) {
            if (isset($playerMoves[$value])) {
                $playerMoves[$value]++;
            }
            return $value;
        });

        $currentPlayer = 1;
        if ($playerMoves[static::PLAYERS[0]] > $playerMoves[static::PLAYERS[1]]) {
            $currentPlayer = 1;
        } elseif ($playerMoves[static::PLAYERS[0]] < $playerMoves[static::PLAYERS[1]]) {
            $currentPlayer = 2;
        }

        return $result + [
                'status' => null,
                'player' => $currentPlayer,
            ];
    }

    /**
     * @param string $coord
     * @param int    $player
     *
     * @return bool|array
     */
    public function playerMove(string $coord, string $player)
    {
        $this->board->{$coord} = $this->board->values[$player];

        if ($this->board->getChain($this->board->values[$player])) {
            return ['status' => 'win', 'player' => $player];
        }
        if (!$this->board->getOpenSlots()) {
            return ['status' => 'draw'];
        }

        return true;
    }
}
