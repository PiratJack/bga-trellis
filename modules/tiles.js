/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Trellis implementation : © Jacques de Metz <demetz.jacques@gmail.com>.
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */


define(["dojo", "dojo/_base/declare", "dojo/_base/fx"], (dojo, declare) => {
    return declare("trellis.tiles", null, {
        ///////////////////////////////////////////////////
        //// Game & client states - Only the ones where tiles are the main point

        // Allows for tile selection
        onEnteringState_plant: function(args) {
            if (this.isCurrentPlayerActive()) {
                this.possibleTileSpots = args._private.possibleTileSpots;
                this.handTilesHandlers = [];
                dojo.query('#trl_hand_tiles .hexagon').forEach((node) => {
                    this.handTilesHandlers.push(dojo.connect(node, 'onclick', this, 'onClickHandTile'));
                });
                dojo.query('#trl_hand_tiles .hexagon').addClass('clickable');
            }
        },

        // Disables interaction & hides possible spots for tiles
        onLeavingState_plant: function() {
            if (this.isCurrentPlayerActive()) {
                this.destroyPossibleTileSpots();
                this.destroyTentativeTiles();
                dojo.query('.selected').removeClass('selected');
                this.handTilesHandlers.forEach(dojo.disconnect);
                delete(this.handTilesHandlers);
                dojo.query('.clickable').removeClass('clickable');
                delete(this.possibleTileSpots);
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
            var newAngle = (currentAngle + 60 * clickedArrow.dataset.direction); // % 360;

            selectedTentativeTile.dataset.angle = newAngle % 360;
            new dojo.Animation({
                curve: [currentAngle, newAngle],
                onAnimate: function(v) {
                    selectedTentativeTile.style.transform = 'rotate(' + v + 'deg)';
                }
            }).play();
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
            } else if (tile.location == 'notification') { // In log
                return this.format_block('jstpl_tile', {
                    div_id: 'notif_tile_' + tile.tile_id,
                    id: tile.tile_id,
                    x: 0,
                    y: 0,
                    top: 0,
                    left: 0,
                    bg_x: bg_x,
                    bg_y: bg_y,
                    angle: 0,
                });

            } else { // My hand
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
            var position_top = this.getTileTopPosition(y);
            var position_left = this.getTileLeftPosition(x);

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

        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        // Someone plays a tile to a board
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


        // Pick a new tile
        notif_pickTile: function(args) {
            this.tiles[args.args.tile.tile_id] = args.args.tile;
            var newTile = this.renderTile(args.args.tile);
            dojo.style(newTile, 'opacity', 0);
            dojo.fadeIn({
                node: newTile
            }).play();
        },
    });
});