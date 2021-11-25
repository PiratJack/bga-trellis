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

require_once('modules/constants.inc.php');

$machinestates = [

    // The initial state. Please do not modify.
    1 => [
        'name' => 'gameSetup',
        'description' => '',
        'type' => 'manager',
        'action' => 'stGameSetup',
        'transitions' => [ '' => TRL_STATE_PLANT ]
    ],


    // Plant a new vine
    TRL_STATE_PLANT => [
        'name' => 'plant',
        'description' => clienttranslate('${actplayer} must plant a tile'),
        'descriptionmyturn' => clienttranslate('${you} must plant a tile'),
        'type' => 'activeplayer',
        'args' => 'argPlant',
        'possibleactions' => [ 'plant' ],
        'transitions' => [ '' => TRL_STATE_PLANT_BLOOM ],
        'updateGameProgression' => true
    ],

    TRL_STATE_PLANT_BLOOM => [
        'name' => 'plantBloom',
        'description' => clienttranslate('Blooming vines'),
        'type' => 'game',
        'action' => 'stPlantBloom',
        'transitions' => [ 'bloomingDone' => TRL_STATE_CLAIM_VINE, 'choiceNeeded' => TRL_STATE_PLANT_CHOOSE, 'endGame' => 99 ]
    ],

    TRL_STATE_PLANT_CHOOSE => [
        'name' => 'plantChooseBloom',
        'description' => clienttranslate('${actplayer} must choose which vine blooms'),
        'descriptionmyturn' => clienttranslate('${you} must choose which vine blooms'),
        'type' => 'activeplayer',
        'args' => 'argPlantChooseBloom',
        'possibleactions' => [ 'plantChooseBloom' ],
        'transitions' => [ 'continueGame' => TRL_STATE_CLAIM_VINE, 'endGame' => 99 ]
    ],


    // Claim a vine on the new tile
    TRL_STATE_CLAIM_VINE => [
        'name' => 'claim',
        'description' => clienttranslate('${actplayer} must claim a vine'),
        'descriptionmyturn' => clienttranslate('${you} must claim a vine'),
        'type' => 'activeplayer',
        'args' => 'argClaim',
        'possibleactions' => [ 'claim' ],
        'transitions' => [ 'continueGame' => TRL_STATE_CLAIM_VINE_BLOOM, 'endGame' => 99 ]
    ],

    TRL_STATE_CLAIM_VINE_BLOOM => [
        'name' => 'claimBloom',
        'description' => clienttranslate('Blooming vines'),
        'type' => 'game',
        'action' => 'stClaimBloom',
        'transitions' => [ 'bloomingDone' => TRL_STATE_CLAIM_GIFT, 'choiceNeeded' => TRL_STATE_CLAIM_VINE_CHOOSE, 'endGame' => 99 ]
    ],

    TRL_STATE_CLAIM_VINE_CHOOSE => [
        'name' => 'claimChooseBloom',
        'description' => clienttranslate('${actplayer} must choose which vine blooms'),
        'descriptionmyturn' => clienttranslate('${you} must choose which vine blooms'),
        'type' => 'activeplayer',
        'args' => 'argClaimChooseBloom',
        'possibleactions' => [ 'claimChooseBloom'],
        'transitions' => [ 'bloomingDone' => TRL_STATE_CLAIM_GIFT, 'noGiftReceived' => TRL_STATE_END_TURN, 'endGame' => 99 ]
    ],


    // Claim a gift if possible
    TRL_STATE_CLAIM_GIFT => [
        'name' => 'claimGift',
        'description' => clienttranslate('${actplayer} claims gift(s)'),
        'descriptionmyturn' => clienttranslate('${you} claims gift(s)'),
        'type' => 'activeplayer',
        'args' => 'argClaimGift',
        'possibleactions' => [ 'claimGift', 'endGame' ],
        'transitions' => [ 'continueGame' => TRL_STATE_CLAIM_GIFT_BLOOM, 'endGame' => 99 ]
    ],

    TRL_STATE_CLAIM_GIFT_BLOOM => [
        'name' => 'claimGiftBloom',
        'description' => clienttranslate('Blooming vines'),
        'type' => 'game',
        'action' => 'stClaimGiftBloom',
        'transitions' => [ 'bloomingDone' => TRL_STATE_END_TURN, 'choiceNeeded' => TRL_STATE_CLAIM_GIFT_CHOOSE, 'endGame' => 99 ]
    ],

    TRL_STATE_CLAIM_GIFT_CHOOSE => [
        'name' => 'plantVineBloom',
        'description' => clienttranslate('${actplayer} must choose which vine blooms'),
        'descriptionmyturn' => clienttranslate('${you} must choose which vine blooms'),
        'type' => 'activeplayer',
        'args' => 'argClaimGiftChooseBloom',
        'possibleactions' => [ 'claimGiftChooseBloom' ],
        'transitions' => [ 'continueGame' => TRL_STATE_END_TURN, 'endGame' => 99 ]
    ],


    // Draw a tile and move to next player
    TRL_STATE_END_TURN => [
        'name' => 'endTurn',
        'description' => clienttranslate('Next player'),
        'type' => 'game',
        'action' => 'stEndTurn',
        'transitions' => [ 'nextPlayer' => TRL_STATE_PLANT, 'endGame' => 99 ]
    ],




    // Final state.
    99 => [
        'name' => 'gameEnd',
        'description' => clienttranslate('End of game'),
        'type' => 'manager',
        'action' => 'stGameEnd',
        'args' => 'argGameEnd'
    ]

];