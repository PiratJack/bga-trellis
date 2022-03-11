<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Trellis implementation : © Jacques de Metz <demetz.jacques@gmail.com>.
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

require_once('modules/constants.inc.php');

$game_options = [
];


$game_preferences = [
    TRL_PREF_ZOOM_LEVEL => [
        'name' => totranslate('Zoom level'),
        'needReload' => false,
        'values' => [
            1 => [ 'name' => totranslate('10%') ],
            2 => [ 'name' => totranslate('20%') ],
            3 => [ 'name' => totranslate('30%') ],
            4 => [ 'name' => totranslate('40%') ],
            5 => [ 'name' => totranslate('50%') ],
            6 => [ 'name' => totranslate('60%') ],
            7 => [ 'name' => totranslate('70%') ],
            8 => [ 'name' => totranslate('80%') ],
            9 => [ 'name' => totranslate('90%') ],
            10 => [ 'name' => totranslate('100%') ],
            11 => [ 'name' => totranslate('110%') ],
            12 => [ 'name' => totranslate('120%') ],
            13 => [ 'name' => totranslate('130%') ],
            14 => [ 'name' => totranslate('140%') ],
            15 => [ 'name' => totranslate('150%') ],
            16 => [ 'name' => totranslate('160%') ],
            17 => [ 'name' => totranslate('170%') ],
            18 => [ 'name' => totranslate('180%') ],
            19 => [ 'name' => totranslate('190%') ],
            20 => [ 'name' => totranslate('200%') ],
        ],
        'default' => 10
    ],
    TRL_PREF_MY_TILES => [
        'name' => totranslate('Display of my tiles'),
        'needReload' => false,
        'values' => [
            TRL_PREF_MY_TILES_ABOVE => [ 'name' => totranslate('Above the board') ],
            TRL_PREF_MY_TILES_BELOW => [ 'name' => totranslate('Below the board') ],
        ],
        'default' => TRL_PREF_MY_TILES_ABOVE
    ],

];
