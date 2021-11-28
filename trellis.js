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
        "dojo", "dojo/_base/declare", "dojo/json",
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
                this.players = gamedatas.players;

                /***** Scrollmap *****/
                this.scrollmap = new ebg.scrollmap();
                this.scrollmap.create($('map_container'), $('map_scrollable'), $('map_surface'), $('map_scrollable_oversurface'));
                this.scrollmap.setupOnScreenArrows(150);

                /***** Tiles *****/
                this.tiles = gamedatas.tiles;
                for (var tile_id in this.tiles) {
                    var tile = this.tiles[tile_id];
                    this.renderTile(tile);
                }

                /***** Flowers *****/
                this.flowers = gamedatas.flowers;
                for (var flower_id in this.flowers) {
                    var flower = this.flowers[flower_id];
                    this.renderFlower(flower);
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
                        if (this.isCurrentPlayerActive()) {
                            this.possibleTileSpots = args.args._private.possibleTileSpots;
                            dojo.query('#trl_hand_tiles .hexagon').connect('onclick', this, 'onClickHandTile');
                            dojo.query('#trl_hand_tiles .hexagon').addClass('clickable');
                        }
                        break;

                    case 'plantChooseBloom':
                        if (this.isCurrentPlayerActive()) {
                            this.possibleBlooms = args.args._private.possibleBlooms;
                            this.displayBloomSpots(this.possibleBlooms);
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

                    case 'plantChooseBloom':
                        dojo.query('.trl_bloom_spot_container').forEach(dojo.destroy);
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

                        case 'plantChooseBloom':
                            this.addActionButton('confirm_bloom', _('Confirm'), 'onConfirmBloom');
                            break;

                    }
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
                    this.displayPossibleTileSpots(this.possibleTileSpots);
                }
            },

            // Display tile on spot + allow to rotate
            onClickPossibleTileSpot: function(evt) {
                var clickedSpot = evt.currentTarget.parentNode;

                // Get the selected tile (will need its ID later)
                var selectedTile = dojo.query('.trl_tile_actual_tile.selected');
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
                    angle: 0,
                }

                if (!{
                        x: position.x,
                        y: position.y
                    } in this.possibleTileSpots) {
                    this.showMessage(_('This spot is not possible'), 'error');
                    return
                }

                this.renderTentativeTile(position);

                dojo.removeClass('confirm_tile_placement', 'disabled');
            },

            // Rotate a tile after placing it
            onClickRotateTile: function(evt) {
                var clickedArrow = evt.currentTarget;
                var selectedSpot = evt.currentTarget.parentNode.parentNode;
                var selectedTile = dojo.query('.trl_tile_actual_tile.selected')[0];
                var selectedTentativeTile = dojo.query('#board_tile_' + selectedTile.dataset.id)[0];

                var currentAngle = parseInt(selectedTentativeTile.dataset.angle);
                var newAngle = (currentAngle + 60 * clickedArrow.dataset.direction) % 360;

                selectedTentativeTile.dataset.angle = newAngle;
                selectedTentativeTile.style.transform = 'rotate(' + newAngle + 'deg)';
            },

            // Confirm button for planting tiles
            onConfirmPlacement: function(evt) {
                if (!this.checkAction('plant'))
                    return

                var selectedTile = dojo.query('.trl_tile_actual_tile.selected')[0];
                var selectedPosition = dojo.query('#board_tile_' + selectedTile.dataset.id)[0];


                this.ajaxcall('/trellis/trellis/plant.html', {
                    tile_id: selectedTile.dataset.id,
                    x: selectedPosition.dataset.x,
                    y: selectedPosition.dataset.y,
                    angle: selectedPosition.dataset.angle,
                    lock: true
                }, this, function(result) {});
            },

            // Confirm button for blooming flowers
            onConfirmBloom: function(evt) {
                // Check all spots have flowers selected
                if (dojo.query('.trl_bloom_spot:not(.selected)').length) {
                    this.showMessage(_('Some spots are missing a flower'), 'error');
                    var missingFlower = dojo.query('.trl_bloom_spot:not(.selected)')[0].parentNode;
                    var x = -parseInt(missingFlower.style.left.substring(0, missingFlower.style.left.length - 2)) - this.tile_width / 2;
                    var y = -parseInt(missingFlower.style.top.substring(0, missingFlower.style.left.length - 2)) - this.tile_height / 2;
                    this.scrollmap.scrollto(x, y);
                    return;
                }

                // Get choices made
                var selectedFlowers = {};
                var allSpots = dojo.query('.trl_bloom_spot').forEach(function(bloomingSpot) {
                    var playerId = bloomingSpot.dataset.selected_player;
                    var vineColor = bloomingSpot.dataset.vine;
                    selectedFlowers[vineColor] = playerId;
                });

                var selection_text = JSON.stringify(selectedFlowers);
                console.log('sending data');
                this.ajaxcall('/trellis/trellis/plantChooseBloom.html', {
                    selection: selection_text,
                    lock: true
                }, this, function(result) {});
            },

            // Confirm button for planting tiles
            onClickBloomSpot: function(evt) {
                var clickedSpot = evt.currentTarget;
                var playerNames = {};
                for (i in clickedSpot.dataset.players.split(',')) {
                    var player_id = clickedSpot.dataset.players.split(',')[i]
                    playerNames[player_id] = '<span style="color: #' + this.players[player_id].player_color + '">' + this.players[player_id].player_name + '</span>';
                }

                this.multipleChoiceDialog(
                    _('Who should get that vine?'), playerNames,
                    dojo.hitch(this, function(player_id) {
                        this.onChooseWhatBlooms(clickedSpot, player_id);
                    }));
            },

            // Choosing who blooms
            onChooseWhatBlooms: function(clickedSpot, player_id) {
                if (clickedSpot.dataset.selected_player != null) {
                    var previousPlayer = this.players[clickedSpot.dataset.selected_player];
                    dojo.removeClass(clickedSpot, 'trl_flower_' + previousPlayer.player_color);
                }
                clickedSpot.dataset.selected_player = player_id;
                dojo.addClass(clickedSpot, 'selected trl_flower_' + this.players[player_id].player_color);
            },


            ///////////////////////////////////////////////////
            //// Utility methods

            // Returns the top position, given an y coordinate
            getTileTopPosition: function(y) {
                return (this.tile_height + this.margin) * y / 2 - this.tile_height / 2;
            },

            // Returns the left position, given an x coordinate
            getTileLeftPosition: function(x) {
                return x * (this.tile_width * 3 / 4 + this.margin) - this.tile_width / 2;
            },

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
                    var position_top = this.getTileTopPosition(tile.y);
                    var position_left = this.getTileLeftPosition(tile.x);

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
                for (i in possibleTiles) {
                    var x = possibleTiles[i].x;
                    var y = possibleTiles[i].y;
                    if (document.getElementById('possible_spot_' + x + '_' + y) === null) {
                        var possibleTileSpot = this.renderPossibleTileSpot(x, y);
                        dojo.query('#possible_spot_' + x + '_' + y + ' .hexagon').connect('onclick', this, 'onClickPossibleTileSpot');
                    }
                }
            },

            // Displays a possible spot in a given location (+ adds JS handlers)
            renderPossibleTileSpot: function(x, y) {
                var position_top = this.getTileTopPosition(tile.y);
                var position_left = this.getTileLeftpPosition(tile.x);

                return dojo.place(this.format_block('jstpl_possible_spot', {
                    tile_type: 'possible_spot',
                    x: x,
                    y: y,
                    top: position_top,
                    left: position_left,
                }), document.getElementById('map_scrollable_oversurface'));
            },

            // Destroys possible spots
            destroyPossibleTileSpots: function() {
                dojo.query('.trl_tile_possible_spot').forEach(dojo.destroy);
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

            // Renders a flower in a given position
            renderFlower: function(flower) {
                return dojo.place(this.format_block('jstpl_flower', {
                    flower_id: flower.flower_id,
                    player_color: this.players[flower.player_id].player_color,
                    angle: flower.angle,
                }), document.getElementById('board_tile_' + flower.tile_id));
            },

            // Displays the possible flower blooms (as white areas)
            displayBloomSpots: function(bloomSpots) {
                for (tile_id in bloomSpots) {
                    var tile = this.tiles[tile_id];

                    var spotContainer = this.renderBloomSpotContainer(tile);

                    for (vine_color in bloomSpots[tile_id]) {
                        var data = bloomSpots[tile_id][vine_color]
                        var angle = data.angle;
                        var players = data.players;

                        var bloomSpot = {
                            'tile_id': tile_id,
                            'vine_color': vine_color,
                            'angle': data.angle,
                            'players': data.players,
                            'container': spotContainer,
                        }

                        var bloomSpotDiv = this.renderBloomSpot(bloomSpot);
                        dojo.connect(bloomSpotDiv, 'onclick', this, 'onClickBloomSpot');
                    }
                }
            },

            // Renders an empty tile for blooming (easier to place this way)
            renderBloomSpotContainer: function(tile) {
                var position_top = this.getTileTopPosition(tile.y);
                var position_left = this.getTileLeftPosition(tile.x);

                return dojo.place(this.format_block('jstpl_bloom_spot_container', {
                    'tile_id': tile.tile_id,
                    'top': position_top,
                    'left': position_left,
                }), document.getElementById('map_scrollable_oversurface'));
            },

            // Renders a white box for bloom spots (when choice is needed)
            renderBloomSpot: function(bloomSpot) {
                return dojo.place(this.format_block('jstpl_bloom_spot', {
                    'tile_id': bloomSpot.tile_id,
                    'vine_color': bloomSpot.vine_color,
                    'angle': bloomSpot.angle,
                    'players': bloomSpot.players,
                }), bloomSpot.container);
            },


            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            // Setup notifications
            setupNotifications: function() {
                console.log('notifications subscriptions setup');

                dojo.subscribe('playerScores', this, 'notif_playerScores');

                dojo.subscribe('playTileToBoard', this, "notif_playTileToBoard");
                this.notifqueue.setSynchronous('playTileToBoard', 500);

                dojo.subscribe('flowerBlooms', this, "notif_flowerBlooms");
                this.notifqueue.setSynchronous('flowerBlooms', 500);
            },

            notif_playTileToBoard: function(args) {
                if (this.isCurrentPlayerActive()) {
                    // Remove from hand, display on board
                    var hand_tile_div_id = 'hand_tile_' + args.args.tile.tile_id;
                    this.fadeOutAndDestroy(hand_tile_div_id);
                    this.renderTile(args.args.tile);
                } else {
                    // Display on board, with fading so the player sees what happens
                    this.tiles[args.args.tile.tile_id] = args.args.tile;

                    var newTile = this.renderTile(args.args.tile);
                    dojo.style(newTile, 'opacity', 0);
                    dojo.fadeIn({
                        node: newTile
                    }).play();
                }
            },

            notif_flowerBlooms: function(args) {
                // Display on board, with fading so the player sees what happens
                this.flowers[args.args.flower.flower_id] = args.args.flower;

                var newFlower = this.renderFlower(args.args.flower);
                dojo.style(newFlower, 'opacity', 0);
                dojo.fadeIn({
                    node: newFlower
                }).play();
            },

            // Notify about scores
            notif_playerScores: function(args) {
                for (var playerId in args.args.score) {
                    var score = args.args.score[playerId];
                    this.scoreCtrl[playerId].toValue(score);
                }
            },

            // Display vine_color with the actual color

            format_string_recursive: function(log, args) {
                try {
                    if (log && args && !args.processed) {
                        args.processed = true;

                        // list of special keys we want to replace with images
                        if ('vine_color' in args)
                            args['vine_color'] = '<span style="color: ' + args['vine_color'] + ';" class="trl_vine_color">&nbsp;' + args['vine_color_translated'] + '&nbsp;</span>';
                    }
                } catch (e) {
                    console.error(log, args, "Exception thrown", e.stack);
                }
                return this.inherited(arguments);
            },

        });
    });