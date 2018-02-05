# Tic Tac Toe

Game, set, match. Let's play a little game of Tic Tac Toe. 

A simple implementation using PHP and Javascript to render and control a two player board game.
Includes a little front controller and request resolver to keep the code clean. What's left is a Symfony-esc micro framework able to strictly handle requests and render different types of responses.

Using the Matrix abstraction layer I'd written in 2016 for the Advent of Code, the base concept came together in under an hour. Because manually handling requests (grabbing $_GET, $_POST etc) is fugly, I've used the HTTP Foundation and YAML converter to write a simplistic request resolver.

Total time (incl frontend): 4 hours 

__Requirements__
* PHP 7.2