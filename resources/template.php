<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tic tac toe</title>

    <style>
        body {
            font-family:  sans-serif;
            font-size: 120%;
        }
        .container {
            width: 300px;
            height: auto;
            margin: 2rem auto;
            text-align: center;
        }
        .status {
            text-align: center;
            clear: both;
        }
        .scores {
            font-size: 1.5rem;
            text-align: center;
            color: #666;
        }
        .scores .div {
            font-size: 1rem;
            color: #000;
        }
        .btn {
            padding: 5px 10px;
            border: 1px solid #ddd;
            background: green;
            color: #fff;
            text-decoration: none;
            font-size: .5rem;
        }
        .btn-new {
            margin: 1rem;
        }
        .board {
            border: 1px solid #000;
            width: 162px;
            height: 169px;
            padding: 5px;
            margin: 1rem auto 0;
            position: relative;
            clear: both;
            text-align: left;
        }
        .col {
            display: inline-block;
            border: 1px solid #ddd;
            margin: 1px;
            width: 50px;
            height: 50px;
            font-size: 30px;
            position: relative;
        }
        .col.interactive {
            cursor: pointer;
        }
        .col span {
            position: absolute;
            margin: 8px 14px;
        }
    </style>

    <script src="//code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <p class="status"></p>

        <div class="board"></div>

        <p class="scores">
            <span class="player-0">0</span>
            <span class="div">vs</span>
            <span class="player-1">0</span>
        </p>
        <a href="javascript:;" class="btn btn-new">New game</a>
    </div>


    <script src="game.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(function() {
            game.initialize();
        });
    </script>
</body>
</html>