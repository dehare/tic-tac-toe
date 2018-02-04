<?php

namespace Dehare\TicTacGame;

use Symfony\Component\HttpFoundation\Request;

interface ResponseInterface
{
    public function __construct(Request $request);

    function setResponse($body);
}