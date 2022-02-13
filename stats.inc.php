<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * TrellisPiratJack implementation : © Jacques de Metz <demetz.jacques@gmail.com>.
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

$stats_type = [

    // Statistics global to table
    'table' => [
    ],

    // Statistics existing for each player
    'player' => [
        'tiles_placed' => [
            'id'=> 10,
            'name' => totranslate('Tiles placed'),
            'type' => 'int'
        ],
        'flowers_placed' => [
            'id'=> 11,
            'name' => totranslate('Flowers placed'),
            'type' => 'int'
        ],
        'flowers_bloomed' => [
            'id'=> 12,
            'name' => totranslate('Flowers bloomed (in own turn)'),
            'type' => 'int'
        ],
        'gifts_given' => [
            'id'=> 13,
            'name' => totranslate('Gifts given'),
            'type' => 'int'
        ],
        'flowers_received' => [
            'id'=> 14,
            'name' => totranslate('Flowers given by other players'),
            'type' => 'int'
        ],
    ]

];
