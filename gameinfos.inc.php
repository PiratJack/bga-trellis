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

$gameinfos = [
    'game_name' => "Trellis",

    'publisher' => 'Breaking Games',
    'publisher_website' => 'http://breakinggames.com/',
    'publisher_bgg_id' => 29082,
    'bgg_id' => 202582,


    'players' => [ 2,3,4 ],
    'suggest_player_number' => null,
    'not_recommend_player_number' => null,


    // Estimated game duration, in minutes (used only for the launch, afterward the real duration is computed)
    'estimated_duration' => 25,

    'fast_additional_time' => 30,
    'medium_additional_time' => 40,
    'slow_additional_time' => 50,

    // If you are using a tie breaker in your game (using "player_score_aux"), you must describe here
    // the formula used to compute "player_score_aux". This description will be used as a tooltip to explain
    // the tie breaker to the players.
    // Note: if you are NOT using any tie breaker, leave the empty string.
    //
    // Example: 'tie_breaker_description' => totranslate( "Number of remaining cards in hand" ),
    'tie_breaker_description' => "",

    // If in the game, all losers are equal (no score to rank them or explicit in the rules that losers are not ranked between them), set this to true
    // The game end result will display "Winner" for the 1st player and "Loser" for all other players
    'losers_not_ranked' => false,

    'solo_mode_ranked' => false,

    'is_beta' => 1,
    'is_coop' => 0,

    'language_dependency' => false,

    // Colors attributed to players
    'player_colors' => [ 'ff775f', '168d63', 'ffffff', 'ffd26b'],
    // Corresponds to "orange", "green", "white", "yellow" ,
    'favorite_colors_support' => true,

    'disable_player_order_swap_on_rematch' => false,

    // Game interface width range (pixels)
    'game_interface_width' => [
        'min' => 500,
        'max' => null
    ],

    'is_sandbox' => false,
    'turnControl' => 'simple'
];
