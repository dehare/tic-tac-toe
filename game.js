var game = new function() {
    this.currentPlayer = 1;
    this.wins          = [0, 0];

    this.initialize = function() {
        this.wins = [0, 0];
        $.get('index.php/game/status').done(function(data) {
            game.setBoard(data.board);
            if (['win', 'draw'].indexOf(data.status) != -1) {
                game.finishGame(data);
                return;
            }

            game.initializeHandlers();
            game.currentPlayer = data.player;
        });

        $('.btn-new').click(function() {
            game.reset();
        });
    };

    this.reset = function() {
        $.get('index.php/game/restart').done(function(data) {
            if (data.message === undefined) {
                game.setBoard(data);
            } else {
                game.refreshBoard();
            }
        });

        $('.status').text('');

        this.currentPlayer = 1;
        game.initializeHandlers();
    };

    this.initializeHandlers = function() {
        $('body').off('click', '.col.interactive').on('click', '.col.interactive', function() {
            $(this).removeClass('interactive');
            game.registerMove($(this).data('coord'));
        });
    };

    this.setBoard = function(board) {
        $('.board').html('');
        for (var row in board) {
            var $row = $('<div/>').addClass('row');
            for (var col in board[row]) {
                var coord = 'x' + col + 'y' + row;
                var $col  = $('<div/>')
                    .addClass('col')
                    .data({coord: coord})
                    .append($('<span/>')
                        .text(board[row][col])
                    );
                if (!$col.text()) {
                    $col.addClass('interactive');
                }
                $row.append($col);
            }

            $('.board').append($row);
        }
    }

    this.refreshBoard = function() {
        $.get('index.php/game/board').done(function(data) {
            game.setBoard(data);
        });
    };

    this.registerMove = function(coord) {
        $.post('index.php/player/move', {coord: coord, player: game.currentPlayer}).done(function(data) {
            if (data.status != undefined) {
                game.finishGame(data);
            }
            game.refreshBoard();
        });

        this.currentPlayer = this.currentPlayer === 1 ? 2 : 1;
    };

    this.finishGame = function(data) {
        $('.col', '.board').removeClass('interactive');

        var msg = 'Game over';
        if (data.status == 'win') {
            msg += '. Player #' + (data.player) + ' wins!';
            this.wins[data.player - 1]++;
        }

        $('.player-0').text(this.wins[0]);
        $('.player-1').text(this.wins[1]);

        $('.status').text(msg);
    };
};