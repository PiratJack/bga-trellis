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
        "ebg/scrollmap",
        g_gamethemeurl + "modules/tiles.js",
        g_gamethemeurl + "modules/flowers.js",
    ],
    function(dojo, declare) {
        return declare("bgagame.trellis", [ebg.core.gamegui, trellis.tiles, trellis.flowers], {
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

                    case 'claim':
                        if (this.isCurrentPlayerActive()) {
                            this.possibleFlowerSpots = args.args._private.possibleFlowerSpots;
                            this.displayFlowerSpots(this.possibleFlowerSpots);
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
                        delete(this.possibleTileSpots);
                        break;

                    case 'plantChooseBloom':
                        dojo.query('.trl_flower_spot_container').forEach(dojo.destroy);
                        delete(this.possibleBlooms);
                        break;

                    case 'claim':
                        dojo.query('.trl_flower_spot_container').forEach(dojo.destroy);
                        delete(this.possibleFlowerSpots);
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

                        case 'claim':
                            this.addActionButton('confirm_claim', _('Confirm'), 'onConfirmClaim');
                            break;

                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Player actions


            ///////////////////////////////////////////////////
            //// Utility methods

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
                            args['vine_color'] = '<span style="color: ' + args['vine_color'] + ';" class="trl_vine_color">&nbsp;' + _(args['vine_color']) + '&nbsp;</span>';
                    }
                } catch (e) {
                    console.error(log, args, "Exception thrown", e.stack);
                }
                return this.inherited(arguments);
            },

        });
    });