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
        'description' => clienttranslate('${actplayer} must plant a tile. You can place your next tile'),
        'descriptionmyturn' => clienttranslate('${you} must plant a tile'),
        'type' => 'activeplayer',
        'action' => 'stPlant',
        'args' => 'argPlant',
        'possibleactions' => [ 'plant', 'prePlant'],
        'transitions' => [ '' => TRL_STATE_PLANT_BLOOM ],
        'updateGameProgression' => true
    ],

    TRL_STATE_PLANT_BLOOM => [
        'name' => 'plantBloom',
        'description' => clienttranslate('Blooming vines'),
        'type' => 'game',
        'action' => 'stPlantBloom',
        'transitions' => [ 'bloomingDone' => TRL_STATE_CHECK_CLAIM_VINE, 'choiceNeeded' => TRL_STATE_PLANT_CHOOSE, 'endGame' => 99 ]
    ],

    TRL_STATE_PLANT_CHOOSE => [
        'name' => 'plantChooseBloom',
        'description' => clienttranslate('${actplayer} must choose which flower blooms. You can place your next tile'),
        'descriptionmyturn' => clienttranslate('${you} must choose which flower blooms'),
        'type' => 'activeplayer',
        'args' => 'argPlantChooseBloom',
        'possibleactions' => [ 'plantChooseBloom', 'prePlant' ],
        'transitions' => [ 'continueGame' => TRL_STATE_CHECK_CLAIM_VINE, 'endGame' => 99 ]
    ],


    // Check if a claim can be done (is there a free vine?)
    TRL_STATE_CHECK_CLAIM_VINE => [
        'name' => 'checkClaimVine',
        'description' => clienttranslate('Checking that a vine can be claimed'),
        'type' => 'game',
        'action' => 'stCheckClaimVine',
        'transitions' => [ 'claimPossible' => TRL_STATE_CLAIM_VINE, 'claimImpossible' => TRL_STATE_END_TURN ]
    ],

    // Claim a vine on the new tile
    TRL_STATE_CLAIM_VINE => [
        'name' => 'claim',
        'description' => clienttranslate('${actplayer} must claim a vine. You can place your next tile'),
        'descriptionmyturn' => clienttranslate('${you} must claim a vine'),
        'type' => 'activeplayer',
        'args' => 'argClaim',
        'possibleactions' => [ 'claim', 'prePlant' ],
        'transitions' => [ 'continueGame' => TRL_STATE_CLAIM_VINE_BLOOM, 'endGame' => 99 ]
    ],

    TRL_STATE_CLAIM_VINE_BLOOM => [
        'name' => 'claimBloom',
        'description' => clienttranslate('Blooming vines'),
        'type' => 'game',
        'action' => 'stClaimBloom',
        'transitions' => [ 'giftReceived' => TRL_STATE_CLAIM_GIFT, 'noGiftReceived' => TRL_STATE_END_TURN, 'endGame' => 99 ]
        // The blooming is automatic because it's not possible to have a choice
        // The player could have a choice if an empty vine could get 2 colors
        // However it's not possible when placing a flower: that vine would have been bloomed already
    ],


    // Claim a gift if possible
    TRL_STATE_CLAIM_GIFT => [
        'name' => 'claimGift',
        'description' => clienttranslate('${actplayer} claims ${gift_points} gift(s). You can place your next tile'),
        'descriptionmyturn' => clienttranslate('${you} claim ${gift_points} gift(s)'),
        'type' => 'activeplayer',
        'args' => 'argClaimGift',
        'possibleactions' => [ 'claimGift', 'endGame', 'prePlant' ],
        'transitions' => [ 'continueGame' => TRL_STATE_CLAIM_GIFT_BLOOM, 'endGame' => 99 ]
    ],

    TRL_STATE_CLAIM_GIFT_BLOOM => [
        'name' => 'claimGiftBloom',
        'description' => clienttranslate('Blooming vines'),
        'type' => 'game',
        'action' => 'stClaimGiftBloom',
        'transitions' => [ 'bloomingDone' => TRL_STATE_END_TURN, 'endGame' => 99 ]
        // See above: choiceNeeded is not possible
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
