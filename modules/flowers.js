/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Trellis implementation : © Jacques de Metz <demetz.jacques@gmail.com>.
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */


define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("trellis.flowers", null, {
        ///////////////////////////////////////////////////
        //// Game & client states - Only the ones where flowers are the main point

        // Displays selection boxes for flowers
        onEnteringState_plantChooseBloom: function(args) {
            if (this.isCurrentPlayerActive()) {
                this.possibleBlooms = args._private.possibleBlooms;
                this.displayBloomSpots(this.possibleBlooms);
            } else {
                this.onUpdatePrePlant(args);
            }
        },

        // Displays selection boxes for flowers
        onEnteringState_claim: function(args) {
            if (this.isCurrentPlayerActive()) {
                this.possibleFlowerSpots = args._private.possibleFlowerSpots;
                this.displayFlowerSpots(this.possibleFlowerSpots, 'onClickFlowerSpot');
            } else {
                this.onUpdatePrePlant(args);
            }
        },

        // Displays selection boxes for gifts
        onEnteringState_claimGift: function(args) {
            if (this.isCurrentPlayerActive()) {
                this.possibleGiftSpots = args._private.possibleFlowerSpots;
                this.mainTile = args._private.mainTile;
                this.nbGifts = args.gift_points;
                this.displayFlowerSpots(this.possibleGiftSpots, 'onClickGiftSpot');

                // In case we have more gifts than the last tile placed (this.mainTile):
                // this.possibleGiftSpots will have multiple keys (= tiles)
                // Gifts must be placed on this.mainTile first
                var temp = this;
                if (Object.keys(this.possibleGiftSpots).length > 1) {
                    var mainTileSpots = dojo.query('#trl_flower_spot_container_' + this.mainTile + ' .trl_flower_spot');
                    mainTileSpots.forEach(function(node) {
                        // Pre-select those spots
                        node.click();

                        dojo.connect(node, 'onclick', () => {
                            // Prevent de-selecting those spots
                            if (!dojo.hasClass(node, 'selected')) {
                                temp.showMessage(_('You must claim all vines from the last tile placed before claiming others'), 'error');
                                node.click();
                            }
                        });
                    });
                }
            } else {
                this.onUpdatePrePlant(args);
            }
        },

        // Disables interaction & hides possible spots for flowers
        onLeavingState_plantChooseBloom: function() {
            if (this.isCurrentPlayerActive()) {
                dojo.query('.trl_flower_spot_container').forEach(dojo.destroy);
                delete(this.possibleBlooms);
            }
        },

        // Disables interaction & hides possible spots for flowers
        onLeavingState_claim: function() {
            if (this.isCurrentPlayerActive()) {
                dojo.query('.trl_flower_spot_container').forEach(dojo.destroy);
                delete(this.possibleFlowerSpots);
            }
        },

        // Disables interaction & hides possible spots for flowers
        onLeavingState_claimGift: function() {
            if (this.isCurrentPlayerActive()) {
                dojo.query('.trl_flower_spot_container').forEach(dojo.destroy);
                delete(this.possibleGiftSpots);
                delete(this.mainTile);
                delete(this.nbGifts);
            }
        },

        ///////////////////////////////////////////////////
        //// Player actions

        // Confirm button for blooming flowers
        onConfirmBloom: function(evt) {
            // Check all spots have flowers selected
            var missingFlower = dojo.query('.trl_flower_spot:not(.selected)');
            if (missingFlower.length) {
                this.showMessage(_('Some spots are missing a flower'), 'error');
                this.scrollTo(missingFlower[0].parentNode);
                return;
            }

            // Get choices made
            var selectedFlowers = {};
            var allSpots = dojo.query('.trl_flower_spot').forEach(function(bloomingSpot) {
                var playerId = bloomingSpot.dataset.selected_player;
                var vineColor = bloomingSpot.dataset.vine;
                selectedFlowers[vineColor] = playerId;
            });

            var selection_text = JSON.stringify(selectedFlowers);
            this.ajaxcall('/trellis/trellis/plantChooseBloom.html', {
                selection: selection_text,
                lock: true
            }, this, function(result) {});
        },

        // Click on a bloom spot (& get to choose what blooms)
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

        // Click on an empty flower spot
        onClickFlowerSpot: function(evt) {
            var clickedSpot = evt.currentTarget;
            dojo.query('.trl_flower_spot.selected').removeClass('selected trl_flower_' + this.players[this.player_id].player_color);

            dojo.addClass(clickedSpot, 'selected trl_flower_' + this.players[this.player_id].player_color);
        },

        // Confirm button for claming vines
        onConfirmClaim: function(evt) {
            // Check a spot is selected
            var selectedSpot = dojo.query('.trl_flower_spot.selected');
            if (selectedSpot.length != 1) {
                this.showMessage(_('Please choose a spot to claim'), 'error');
                var missingFlower = dojo.query('.trl_flower_spot:not(.selected)')
                if (missingFlower.length > 0)
                    this.scrollTo(missingFlower[0].parentNode);
                return;
            }

            // Get choice made
            var selectedSpot = selectedSpot[0];
            var tileId = selectedSpot.parentNode.dataset.tile;
            var vineColor = selectedSpot.dataset.vine;

            this.ajaxcall('/trellis/trellis/claim.html', {
                tile_id: tileId,
                vine_color: vineColor,
                lock: true
            }, this, function(result) {});
        },

        // Click on an empty flower spot (with in gift state)
        onClickGiftSpot: function(evt) {
            var clickedSpot = evt.currentTarget;

            dojo.toggleClass(clickedSpot, 'selected trl_flower_' + this.players[this.player_id].player_color);
        },

        // Confirm button for claming gifts
        onConfirmClaimGift: function(evt) {
            // Check a spot is selected
            var selectedSpots = dojo.query('.trl_flower_spot.selected');
            var availableSpots = dojo.query('.trl_flower_spot');

            // Player hasn't selected the right number of gifts
            if (availableSpots.length > this.nbGifts) {
                // Enough spots to get all gifts
                if (selectedSpots.length < this.nbGifts)
                    return this.showMessage(_('You received more gifts, please choose additional spots'), 'error');
            } else {
                // Not enough spots ==> need to take all spots
                if (selectedSpots.length != availableSpots.length) {
                    return this.showMessage(_('You received more gifts, please choose additional spots'), 'error');
                }
            }
            if (selectedSpots.length > this.nbGifts)
                return this.showMessage(_('You received less gifts, please choose less spots'), 'error');

            // Player has more gifts than the main tile can accomodate
            // ==> he/she has to claim all vines from the main tile
            var mainTileSpots = dojo.query('#trl_flower_spot_container_' + this.mainTile + ' .trl_flower_spot');
            var mainTileUnSelectedSpots = dojo.query('#trl_flower_spot_container_' + this.mainTile + ' .trl_flower_spot:not(.selected)');
            if (this.nbGifts >= mainTileSpots.length && mainTileUnSelectedSpots.length != 0) {
                this.showMessage(_('You must claim all vines from the last tile placed before claiming others'), 'error');

                this.scrollTo(mainTileUnSelectedSpots[0].parentNode);
                return;
            }

            // Get choice made
            var selectedGifts = {};
            var allSpots = selectedSpots.forEach(function(giftSpot) {
                var tileId = giftSpot.parentNode.dataset.tile;
                var vineColor = giftSpot.dataset.vine;
                if (!(tileId in selectedGifts))
                    selectedGifts[tileId] = [];
                selectedGifts[tileId].push(vineColor);
            });

            var selection_text = JSON.stringify(selectedGifts);
            this.ajaxcall('/trellis/trellis/claimGift.html', {
                selection: selection_text,
                lock: true
            }, this, function(result) {});
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

                var spotContainer = this.renderFlowerSpotContainer(tile);

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

                    var bloomSpotDiv = this.renderFlowerSpot(bloomSpot);
                    dojo.connect(bloomSpotDiv, 'onclick', this, 'onClickBloomSpot');
                }
            }
        },

        // Displays the possible flower spots (as white areas)
        displayFlowerSpots: function(flowerSpots, handler) {
            for (tile_id in flowerSpots) {
                var tile = this.tiles[tile_id];

                var spotContainer = this.renderFlowerSpotContainer(tile);

                for (vine_color in flowerSpots[tile_id]) {
                    var angles = flowerSpots[tile_id][vine_color]

                    var bloomSpot = {
                        'tile_id': tile_id,
                        'vine_color': vine_color,
                        'angle': angles[0],
                        'players': '',
                        'container': spotContainer,
                    }

                    var flowerSpotDiv = this.renderFlowerSpot(bloomSpot);
                    dojo.connect(flowerSpotDiv, 'onclick', this, handler);
                }
            }
        },

        // Renders an empty tile for blooming (easier to place this way)
        renderFlowerSpotContainer: function(tile) {
            var position_top = this.getTileTopPosition(tile.y);
            var position_left = this.getTileLeftPosition(tile.x);

            return dojo.place(this.format_block('jstpl_flower_spot_container', {
                'tile_id': tile.tile_id,
                'top': position_top,
                'left': position_left,
            }), document.getElementById('map_scrollable_oversurface'));
        },

        // Renders a white box for bloom spots (when choice is needed)
        renderFlowerSpot: function(bloomSpot) {
            return dojo.place(this.format_block('jstpl_flower_spot', {
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