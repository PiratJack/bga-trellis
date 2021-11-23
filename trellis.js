/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * TrellisPiratJack implementation : © Jacques de Metz <demetz.jacques@gmail.com>.
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

define([
        "dojo", "dojo/_base/declare",
        "ebg/core/gamegui",
        "ebg/counter",
        "ebg/scrollmap"
    ],
    function(dojo, declare) {
        return declare("bgagame.trellis", ebg.core.gamegui, {
            constructor: function() {
                // Tile sizes
                this.tile_width = 314;
                this.sin_60 = 0.8660; // sin(60°) = 0.8660
                this.tile_height = this.tile_width * this.sin_60;

                // Margin between tiles
                this.margin = 4;

                //REAL_ART: sprite dimensions
                this.sprite_size_x = 5;
                this.sprite_size_y = 1;
            },

            setup: function(gamedatas) {
                /***** Player boards *****/
                for (var player_id in gamedatas.players) {
                    var player = gamedatas.players[player_id];
                }

                /***** Scrollmap *****/
                this.scrollmap = new ebg.scrollmap();
                this.scrollmap.create($('map_container'), $('map_scrollable'), $('map_surface'), $('map_scrollable_oversurface'));
                this.scrollmap.setupOnScreenArrows(150);

                /***** Tiles *****/
                this.tiles = gamedatas.tiles;
                for (var tile_id in gamedatas.tiles) {
                    var tile = gamedatas.tiles[tile_id];
                    this.renderTile(tile);
                }

                /***** Notifications *****/
                this.setupNotifications();
            },


            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function(stateName, args) {
                switch (stateName) {
                    case 'plant':
                        // Can player select a card?
                        if (this.isCurrentPlayerActive()) {
                            this.possibleTileSpots = args.args._private.possibleTileSpots;
                            for (i in this.possibleTileSpots) {
                                dojo.query('#hand_tile_' + i + ' .hexagon').connect('onclick', this, 'onClickHandTile');
                                dojo.query('#hand_tile_' + i).addClass('clickable');
                            }
                        }
                        break;
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function(stateName) {
                switch (stateName) {
                    case 'plant':
                        this.destroyPossibleTileSpots();
                        this.destroyTentativeTiles();
                        dojo.query('.selected').removeClass('selected');
                        dojo.query('.clickable').removeClass('clickable');
                        break;
                }
            },

            // Action buttons on the top bar
            onUpdateActionButtons: function(stateName, args) {
                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {
                        case 'plant':
                            this.addActionButton('confirm_tile_placement', _('Confirm'), 'onConfirmPlacement');
                            dojo.addClass('confirm_tile_placement', 'disabled');
                            break;

                    }
                    //TODO: 'plant' => Add buttons to confirm selection
                }
            },

            ///////////////////////////////////////////////////
            //// Player actions

            // Stores clicked tile + displays relevant spots
            onClickHandTile: function(evt) {
                var clickedTile = evt.currentTarget.parentNode;
                var tileId = clickedTile.dataset.id;
                var wasSelected = dojo.hasClass(clickedTile, 'selected');

                // Clear up everything from previous steps
                dojo.query('#trl_hand_tiles .selected').removeClass('selected');
                this.destroyPossibleTileSpots();
                this.destroyTentativeTiles();
                dojo.addClass('confirm_tile_placement', 'disabled');

                if (!wasSelected) {
                    dojo.addClass(clickedTile, 'selected');
                    this.displayPossibleTileSpots(this.possibleTileSpots[tileId]);
                }
            },

            // Display tile on spot + allow to rotate
            onClickPossibleTileSpot: function(evt) {
                var clickedSpot = evt.currentTarget.parentNode;

                // Get the selected tile (will need its ID later)
                var selectedTile = dojo.query('.trl_tile_actualTile.selected');
                if (selectedTile.length == 0) {
                    this.showMessage(_('Please choose a tile first'), 'error');
                    return;
                }
                // Destroy other elements
                this.destroyTentativeTiles();

                var tile_id = selectedTile[0].dataset.id;

                var position = {
                    tile_id: tile_id,
                    x: clickedSpot.dataset.x,
                    y: clickedSpot.dataset.y,
                    location: 'board',
                }


                if (typeof this.possibleTileSpots[tile_id] == 'undefined') {
                    this.showMessage(_('This spot is not possible'), 'error');
                    return
                }
                if (typeof this.possibleTileSpots[tile_id][position.x] == 'undefined') {
                    this.showMessage(_('This spot is not possible'), 'error');
                    return
                }
                if (typeof this.possibleTileSpots[tile_id][position.x][position.y] == 'undefined') {
                    this.showMessage(_('This spot is not possible'), 'error');
                    return
                }

                position.angle = this.possibleTileSpots[tile_id][position.x][position.y][0];

                this.renderTentativeTile(position);

                dojo.removeClass('confirm_tile_placement', 'disabled');
            },

            // Rotate a tile after placing it
            onClickRotateTile: function(evt) {
                var clickedArrow = evt.currentTarget;
                var selectedSpot = evt.currentTarget.parentNode.parentNode;
                var selectedTile = dojo.query('.trl_tile_actualTile.selected')[0];
                var selectedTentativeTile = dojo.query('#board_tile_' + selectedTile.dataset.id)[0];

                var currentAngle = parseInt(selectedTentativeTile.dataset.angle);

                var possibleAngles = selectedSpot.dataset.angles.split(',');

                if (clickedArrow.dataset.direction > 0) {
                    var temp = possibleAngles.filter(angle => parseInt(angle) > currentAngle);
                    if (temp.length == 0)
                        temp = possibleAngles;

                    var newAngle = Math.min(...temp);
                } else {
                    var temp = possibleAngles.filter(angle => parseInt(angle) < currentAngle);
                    if (temp.length == 0)
                        temp = possibleAngles;

                    var newAngle = Math.max(...temp);
                }

                selectedTentativeTile.dataset.angle = newAngle;
                selectedTentativeTile.style.transform = 'rotate(' + newAngle + 'deg)';
            },

            // Confirm button for planting tiles
            onConfirmPlacement: function(evt) {
                if (!this.checkAction('plant'))
                    return

                var selectedTile = dojo.query('.trl_tile_actualTile.selected')[0];
                var selectedPosition = dojo.query('#board_tile_' + selectedTile.dataset.id)[0];


                this.ajaxcall('/trellis/trellis/plant.html', {
                    tile_id: selectedTile.dataset.id,
                    x: selectedPosition.dataset.x,
                    y: selectedPosition.dataset.y,
                    angle: selectedPosition.dataset.angle,
                    lock: true
                }, this, function(result) {});

            },


            ///////////////////////////////////////////////////
            //// Utility methods

            // Renders a tile in a given position (either board or hand)
            renderTile: function(tile) {
                if (this.sprite_size_x == 1)
                    var bg_x = 0;
                else
                    var bg_x = 100 * parseInt(tile.sprite_position.x) / (this.sprite_size_x - 1);

                if (this.sprite_size_y == 1)
                    var bg_y = 0;
                else
                    var bg_y = 100 * parseInt(tile.sprite_position.y) / (this.sprite_size_y - 1);

                if (tile.location == 'board') {
                    var position_top = (this.tile_height + this.margin) * tile.y / 2 - this.tile_height / 2;
                    var position_left = tile.x * (this.tile_width * 3 / 4 + this.margin) - this.tile_width / 2;

                    return dojo.place(this.format_block('jstpl_tile', {
                        div_id: 'board_tile_' + tile.tile_id,
                        id: tile.tile_id,
                        x: tile.x,
                        y: tile.y,
                        top: position_top,
                        left: position_left,
                        bg_x: bg_x,
                        bg_y: bg_y,
                        angle: tile.angle,
                    }), document.getElementById('map_scrollable'));
                } else {
                    return dojo.place(this.format_block('jstpl_tile', {
                        div_id: 'hand_tile_' + tile.tile_id,
                        id: tile.tile_id,
                        x: 0,
                        y: 0,
                        top: 0,
                        left: 0,
                        bg_x: bg_x,
                        bg_y: bg_y,
                        angle: 0,
                    }), document.getElementById('trl_hand_tiles'));
                }
            },

            // Renders a tile in a tentative position
            renderTentativeTile: function(position) {
                // Rotate the tile to the first possible angles
                var newTileData = Object.assign(this.tiles[position.tile_id], position);
                var newTile = this.renderTile(newTileData);
                dojo.addClass(newTile, 'tentative');

                // Display arrows to rotate
                this.destroyRotatingArrows();
                this.renderRotatingArrows(position);
            },

            // Renders the arrows to rotate tiles
            renderRotatingArrows: function(spot) {
                var position_top = (this.tile_height + this.margin) * spot.y / 2 - this.tile_height / 2;
                var position_left = spot.x * (this.tile_width * 3 / 4 + this.margin) - this.tile_width / 2;

                var element = dojo.place(this.format_block('jstpl_rotation_arrows', {
                    x: spot.x,
                    y: spot.y,
                    top: 0,
                    left: 0,
                }), document.getElementById('possible_spot_' + spot.x + '_' + spot.y));

                dojo.connect($('trl_tile_rotate_counterclockwise'), 'onclick', this, 'onClickRotateTile');
                dojo.connect($('trl_tile_rotate_clockwise'), 'onclick', this, 'onClickRotateTile');
            },

            // Displays the possible spots (as white areas)
            displayPossibleTileSpots: function(possibleTiles) {
                for (x in possibleTiles) {
                    for (y in possibleTiles[x]) {
                        if (document.getElementById('possible_spot_' + x + '_' + y) === null) {
                            this.renderPossibleTileSpot(x, y, possibleTiles[x][y]);
                        }
                    }
                }
            },

            // Displays a possible spot in a given location (+ adds JS handlers)
            renderPossibleTileSpot: function(x, y, angles) {
                var position_top = (this.tile_height + this.margin) * y / 2 - this.tile_height / 2;
                var position_left = x * (this.tile_width * 3 / 4 + this.margin) - this.tile_width / 2;

                dojo.place(this.format_block('jstpl_possible_spot', {
                    tile_type: 'possibleSpot',
                    x: x,
                    y: y,
                    top: position_top,
                    left: position_left,
                    angles: angles,
                }), document.getElementById('map_scrollable_oversurface'));

                dojo.query('#possible_spot_' + x + '_' + y + ' .hexagon').connect('onclick', this, 'onClickPossibleTileSpot');
            },

            // Destroys possible spots
            destroyPossibleTileSpots: function() {
                dojo.query('.trl_tile_possibleSpot').forEach(dojo.destroy);
                this.destroyRotatingArrows();
            },

            // Destroys possible spots
            destroyRotatingArrows: function() {
                dojo.query('#trl_tile_rotate').forEach(dojo.destroy);
            },

            // Destroys possible spots
            destroyTentativeTiles: function() {
                dojo.query('.tentative').forEach(dojo.destroy);
            },

            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            // Setup notifications
            setupNotifications: function() {
                console.log('notifications subscriptions setup');

                dojo.subscribe('playTileToBoard', this, "notif_playTileToBoard");
            },

            notif_playTileToBoard: function(args) {
                if (this.isCurrentPlayerActive()) {
                    var hand_tile_div_id = 'hand_tile_' + args.args.tile.tile_id;
                    this.fadeOutAndDestroy(hand_tile_div_id);
                } else
                    this.tiles[args.args.tile.tile_id] = args.args.tile;

                this.renderTile(args.args.tile);
            }
        });
    });