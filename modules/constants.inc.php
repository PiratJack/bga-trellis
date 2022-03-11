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

if (!defined('TRL_STATE_SETUP'))
{
    define('TRL_STATE_PLANT', 20);
    define('TRL_STATE_PLANT_BLOOM', 24);
    define('TRL_STATE_PLANT_CHOOSE', 28);

    define('TRL_STATE_CLAIM_VINE', 30);
    define('TRL_STATE_CLAIM_VINE_BLOOM', 34);

    define('TRL_STATE_CLAIM_GIFT', 40);
    define('TRL_STATE_CLAIM_GIFT_BLOOM', 44);

    define('TRL_STATE_END_TURN', 80);


    define('TRL_PREF_ZOOM_LEVEL', 100);

    define('TRL_PREF_MY_TILES', 101);
    define('TRL_PREF_MY_TILES_ABOVE', 1);
    define('TRL_PREF_MY_TILES_BELOW', 2);
}
