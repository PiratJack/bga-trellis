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

$stats_type = [

    // Statistics global to table
    'table' => [
        'who_won' => [
            'id'=> TRL_STAT_WHO_WON,
            'name' => totranslate('Who won?'),
            'type' => 'int',
            'display' => 'limited'
        ],
    ],

    // Statistics existing for each player
    'player' => [
        'tiles_placed' => [
            'id'=> TRL_STAT_TILES_PLACED,
            'name' => totranslate('Tiles placed'),
            'type' => 'int'
        ],
        'flowers_placed' => [
            'id'=> TRL_STAT_FLOWERS_PLACED,
            'name' => totranslate('Flowers placed'),
            'type' => 'int'
        ],
        'flowers_bloomed' => [
            'id'=> TRL_STAT_FLOWERS_BLOOMED,
            'name' => totranslate('Flowers bloomed (in own turn)'),
            'type' => 'int'
        ],
        'gifts_given' => [
            'id'=> TRL_STAT_GIFTS_GIVEN,
            'name' => totranslate('Gifts given'),
            'type' => 'int'
        ],
        'flowers_received' => [
            'id'=> TRL_STAT_FLOWERS_RECEIVED,
            'name' => totranslate('Flowers given by other players'),
            'type' => 'int'
        ],
    ],

    // Labels for "first player won?"
    'value_labels' => [
        TRL_STAT_WHO_WON => [
            0 => totranslate('Unknown'),
            21 => totranslate('First player in 2-player game'),
            31 => totranslate('First player in 3-player game'),
            41 => totranslate('First player in 4-player game'),
            22 => totranslate('Second player in 2-player game'),
            32 => totranslate('Second player in 3-player game'),
            42 => totranslate('Second player in 4-player game'),
            33 => totranslate('Third player in 3-player game'),
            34 => totranslate('Third player in 4-player game'),
            44 => totranslate('Fourth player in 4-player game'),
        ]
    ]
];
