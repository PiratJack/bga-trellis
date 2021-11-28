/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * TrellisPiratJack implementation : © Jacques de Metz <demetz.jacques@gmail.com>.
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */


define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("trellis.flowers", null, {
        ///////////////////////////////////////////////////
        //// Player actions

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

        notif_flowerBlooms: function(args) {
            // Display on board, with fading so the player sees what happens
            this.flowers[args.args.flower.flower_id] = args.args.flower;

            var newFlower = this.renderFlower(args.args.flower);
            dojo.style(newFlower, 'opacity', 0);
            dojo.fadeIn({
                node: newFlower
            }).play();
        },
    });
});