/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Trellis implementation : © Jacques de Metz <demetz.jacques@gmail.com>.
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
                this.resizeBoard();

                // Margin between tiles
                this.margin = 0; //4;

                // Number of tiles in the sprite
                this.sprite_size_x = 8;
                this.sprite_size_y = 8;

                // Resizing the screen ==> adjust tile sizes
                dojo.connect(window, 'resize', () => this.resizeBoard());
            },

            setup: function(gamedatas) {
                /***** Player boards *****/
                this.players = gamedatas.players;

                /***** Scrollmap *****/
                this.scrollmap = new ebg.scrollmap();
                this.scrollmap.create($('map_container'), $('map_scrollable'), $('map_surface'), $('map_scrollable_oversurface'));
                this.scrollmap.setupOnScreenArrows(150);

                dojo.connect($('enlargedisplay'), 'onclick', this, 'onIncreaseDisplayHeight');
                dojo.connect($('reducedisplay'), 'onclick', this, 'onDecreaseDisplayHeight');

                this.trl_zoom = 1;
                dojo.connect($('zoomplus'), 'onclick', () => this.onZoomButton(0.1));
                dojo.connect($('zoomminus'), 'onclick', () => this.onZoomButton(-0.1));

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

                /***** Player board *****/
                for (var playerId in gamedatas.players) {
                    var player_data = gamedatas.players[playerId];
                    var player_board_div = $('player_board_' + playerId);
                    dojo.place(this.format_block('jstpl_player_board', {
                        player_id: playerId,
                        gift_points: gamedatas.players[playerId].gift_points,
                        flowers_left: 15 - gamedatas.players[playerId].score,
                        player_color: gamedatas.players[playerId].color,
                    }), player_board_div);
                    this.addTooltip('trl_gift_' + playerId, _('Gifts points won'), '')
                    this.addTooltip('trl_flowers_left_' + playerId, _('Remaining flowers'), '')

                    if (player_data.last_tile_placed) {
                        if (this.player_id != playerId) {
                            dojo.addClass('board_tile_' + player_data.last_tile_placed, 'border_' + player_data.player_color);
                        }
                    }
                }

                /***** Notifications *****/
                this.setupNotifications();

                // User preferences
                this.setupUserPreferences();
            },

            // Resizes the board and cards based on the screen size
            resizeBoard: function() {
                if (window.matchMedia("(min-width: 1350px)").matches)
                    this.tile_width = 316;
                else if (window.matchMedia("(min-width: 1000px) and (max-width: 1350px)").matches)
                    this.tile_width = 158;
                else if (window.matchMedia("(max-width: 1000px)").matches)
                    this.tile_width = 100;

                this.sin_60 = 0.8660; // sin(60°) = 0.8660
                this.tile_height = this.tile_width * this.sin_60;

                all_tiles = dojo.query('.trl_tile').forEach(
                    (tile) => {
                        tile.style.top = this.getTileTopPosition(tile.dataset.y) + 'px';
                        tile.style.left = this.getTileLeftPosition(tile.dataset.x) + 'px';
                    }
                );
            },

            // Changes zoom value
            onZoomButton: function(deltaZoom) {
                zoom = this.trl_zoom + deltaZoom;
                zoom = zoom <= 0.2 ? 0.2 : zoom >= 2 ? 2 : zoom;
                this.onPreferenceChange(100, (zoom * 10).toFixed());

                // Trigger the change for the server
                const newEvt = document.createEvent('HTMLEvents');
                newEvt.initEvent('change', false, true);
                $('preference_control_100').dispatchEvent(newEvt);
            },

            // Applies the new zoom
            onZoomChange: function(newZoom) {
                this.trl_zoom = newZoom;

                dojo.style($('map_scrollable'), 'transform', 'scale(' + this.trl_zoom + ')');
                dojo.style($('map_scrollable_oversurface'), 'transform', 'scale(' + this.trl_zoom + ')');
            },

            ///////////////////////////////////////////////////
            //// Game & client states

            onEnteringState: function(stateName, args) {
                switch (stateName) {
                    case 'plant':
                    case 'plantChooseBloom':
                    case 'claim':
                    case 'claimGift':
                        var methodName = "onEnteringState_" + stateName;
                        this[methodName](args.args);
                        break;
                }
            },

            onLeavingState: function(stateName) {
                switch (stateName) {
                    case 'plant':
                    case 'plantChooseBloom':
                    case 'claim':
                    case 'claimGift':
                        var methodName = "onLeavingState_" + stateName;
                        this[methodName]();
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

                        case 'claimGift':
                            this.addActionButton('confirm_claim_gift', _('Confirm'), 'onConfirmClaimGift');
                            break;
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            // Scolls to a given element
            scrollTo: function(element) {
                var x = -parseInt(element.style.left.substring(0, element.style.left.length - 2)) - this.tile_width / 2;
                var y = -parseInt(element.style.top.substring(0, element.style.left.length - 2)) - this.tile_height / 2;
                this.scrollmap.scrollto(x, y);
            },

            onIncreaseDisplayHeight: function(evt) {
                evt.preventDefault();

                var current_height = toint(dojo.style($('map_container'), 'height'));
                dojo.style($('map_container'), 'height', (current_height + 300) + 'px');
            },

            onDecreaseDisplayHeight: function(evt) {
                evt.preventDefault();

                var current_height = toint(dojo.style($('map_container'), 'height'));
                dojo.style($('map_container'), 'height', Math.max((current_height - 300), 100) + 'px');
            },

            ///////////////////////////////////////////////////
            //// User preferences

            // Defines handlers when user changes values
            setupUserPreferences: function() {
                // Extract the ID and value from the UI control
                var _this = this;

                function onchange(e) {
                    var match = e.target.id.match(/^preference_[cf]ontrol_(\d+)$/);
                    if (!match) {
                        return;
                    }
                    var prefId = +match[1];
                    var prefValue = +e.target.value;
                    _this.prefs[prefId].value = prefValue;
                    dojo.query('#preference_control_' + prefId)[0].value = prefValue;
                    dojo.query('#preference_fontrol_' + prefId)[0].value = prefValue;
                    _this.onPreferenceChange(prefId, prefValue);
                }

                // Call onPreferenceChange() when any value changes
                dojo.query(".preference_control").connect("onchange", onchange);

                // Call onPreferenceChange() now to initialize the setup
                dojo.forEach(
                    dojo.query("#ingame_menu_content .preference_control"),
                    function(el) {
                        onchange({
                            target: el
                        });
                    }
                );
            },

            // Applies preference changes in the game
            onPreferenceChange: function(prefId, prefValue) {
                // Preferences that change display
                switch (prefId) {
                    // Zoom level
                    case 100:
                        this.onZoomChange(prefValue / 10);
                        dojo.query('#preference_control_' + prefId)[0].value = prefValue;
                        dojo.query('#preference_fontrol_' + prefId)[0].value = prefValue;
                        break;

                        // Display my tiles above/below
                    case 101:
                        if (prefValue == 1)
                            dojo.place('trl_hand', 'map_container', 'before');
                        else
                            dojo.place('trl_hand', 'map_container', 'after');
                        break;
                }
            },

            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            // Setup notifications
            setupNotifications: function() {
                dojo.subscribe('playerScores', this, 'notif_playerScores');

                dojo.subscribe('playerGifts', this, 'notif_playerGifts');

                dojo.subscribe('playTileToBoard', this, "notif_playTileToBoard");
                this.notifqueue.setSynchronous('playTileToBoard', 500);

                dojo.subscribe('flowerBlooms', this, "notif_flowerBlooms");
                this.notifqueue.setSynchronous('flowerBlooms', 500);

                dojo.subscribe('claimVine', this, "notif_flowerBlooms");
                this.notifqueue.setSynchronous('claimVine', 500);

                dojo.subscribe('pickTile', this, "notif_pickTile");
                this.notifqueue.setSynchronous('pickTile', 500);
            },

            // Notify about scores
            notif_playerScores: function(args) {
                for (var playerId in args.args.score) {
                    var score = args.args.score[playerId];
                    this.scoreCtrl[playerId].toValue(score);
                    $('trl_flowers_left_' + playerId).innerText = 15 - score;
                }
            },

            // Notify about gift points
            notif_playerGifts: function(args) {
                for (var playerId in args.args.giftPoints) {
                    var giftPoints = args.args.giftPoints[playerId];
                    $('trl_gift_' + playerId).innerText = giftPoints;
                }
            },

            // Display vine_color and tile with the actual color
            format_string_recursive: function(log, args) {
                try {
                    if (log && args && !args.processed) {
                        args.processed = true;

                        // Replace vine color with an image
                        if ('vine_color' in args)
                            args['vine_color'] = '<div class="trl_vine_color trl_vine_color_' + args['vine_color'] + '" title="' + args['vine_color'] + '"></div>';

                        // Replace a tile with an image
                        if ('tile_log' in args) {
                            var tile = args.tile_log;
                            tile.location = 'notification';
                            args['tile_log'] = this.renderTile(tile);
                        }
                    }
                } catch (e) {
                    console.error(log, args, "Exception thrown", e.stack);
                }
                return this.inherited(arguments);
            },
        });
    });