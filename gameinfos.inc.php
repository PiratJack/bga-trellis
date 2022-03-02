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

$gameinfos = [
    'game_name' => "Trellis",

    'designer' => 'Teale Fristoe',
    'artist' => 'Vikki Chu',

    'year' => 2018,

    'publisher' => 'Breaking Games',
    'publisher_website' => 'http://breakinggames.com/',
    'publisher_bgg_id' => 230,
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

    'complexity' => 1,
    'luck' => 2,
    'strategy' => 4,
    'diplomacy' => 0,

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

    'presentation' => [
        totranslate("Grow a beautiful garden of tiles on which flower meeples bloom down connecting vines of the same color. Players race to be the first to play all their flowers first in this cascade of color and fun for 2-4 players. The twist? When growing the garden, helping others bloom can be really good for the active player - even secure the immediate win! Well chosen tile placement is the key to success in this evolving puzzle of a game that will grow on you."),
        totranslate("In Trellis, each player has a supply of flower meeples in their unique color that they are working to place in the garden. Each player also has a hand of hex tiles depicting a tangled weave of differently colored vines."),
        totranslate("On their turn, the active player must plant a tile on the table, growing the size of the garden. Once a tile is placed, automatic blooms happen first. All newly connected vines will bloom flower meeples, if the newly connected vine matches color with the neighbor tile and only if the vine on the neighbor tile has already been claimed with a flower."),
        totranslate("The active player then places a flower claiming a vine on the tile they placed that has not yet bloomed. Immediately, each unclaimed vine of the same color on neighboring tiles connected to the vine just claimed will automatically bloom for that player."),
        totranslate("Finally, if any opponents had flowers bloom due to the tile placement, then the active player gets to place a bonus flower - one for each flower bloom they helped their opponent place! If there are no unclaimed vines available on the tile the active player placed, they get to place a bloom on any unclaimed vine anywhere in the garden!"),
        totranslate("The first player to play all fifteen of their flowers immediately wins. Which vines to claim and when to help opponents grow flowers in order to gain bonus blooms for yourself is very furtile soil to ponder during a quick play of Trellis."),
    ],

    'tags' => [ 2, 11, 105, 206 ],

    'is_sandbox' => false,
    'turnControl' => 'simple'
];
