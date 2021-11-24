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

namespace TrellisPiratJack;

trait StatesTrait {


    // Returns possible moves for the active player
    public function argPlant() {
        //TODO: states > argPlant
        return [
            '_private' => [
                'active' => [
                    'possibleTileSpots' => $this->getPossibleTileSpots()
                ]
            ]
        ];
    }

    // The player plants a tile
    public function actPlant($tile_id, $x, $y, $angle) {
        // User allowed to do this?
        $this->checkAction('plant');

        // Check tile exists & nothing was tampered with
        $tile = $this->getTiles(['tile_id' => $tile_id]);
        if (count($tile) != 1)
        {
            throw new BgaVisibleSystemException(_('This tile does not exist'));
        }


        // Check tile is the player's one
        if ($tile[$tile_id]['location'] != $this->getCurrentPlayerId())
        {
            throw new \BgaUserException(_('This tile is not in your hand'));
        }
        $possible_spots = $this->getPossibleTileSpots();

        $tile = $tile[$tile_id];

        // Check this spot is available
        if (!in_array(['x' => $x, 'y' => $y], $possible_spots))
        {
            throw new \BgaUserException(_('This tile can\'t be placed here'));
        }

        // Place the tile there
        $target = [
            'location' => 'board',
            'x' => $x,
            'y' => $y,
            'location_order' => 0,
            'angle' => $angle,
        ];

        $this->moveTilesToLocation($tile['tile_id'], $target);
        $tile = $target + $tile;

        self::notifyAllPlayers(
            'playTileToBoard',
            clienttranslate('${player_name} plays a tile to the table'),
            [
                'player_id' => $this->getCurrentPlayerId(),
                'player_name' => self::getActivePlayerName(),
                'tile' => $tile,
            ]
        );
        $this->setGameStateValue('last_tile_planted', $tile['tile_id']);

        $this->gamestate->nextState('');
    }

    // Blooms flowers after player puts a vine
    public function stPlantBloom() {
        //TODO: states > stPlantBloom

        // Transition: [ 'bloomingDone' , 'choiceNeeded' , 'endGame' ]
    }

    // If multiple bloom positions are possible, returns the possible ones
    public function argPlantChooseBloom() {
        //TODO: states > argPlantChooseBloom
    }

    // Player chose which flower blooms
    public function actPlantChooseBloom($x, $y, $position_on_tile) {
        //TODO: states > actPlantChooseBloom

        // Transition: 'continueGame', 'endGame'
    }



    // Return which vines can be claimed by active player (regular move)
    public function argClaim() {
        //TODO: states > argClaim
    }

    // The player claims a vine
    public function actClaim($x, $y, $angle) {
        //TODO: states > actClaim

        // Transition: 'continueGame', 'endGame'
    }

    // Blooms flowers after player claims a vine
    public function stClaimBloom() {
        //TODO: states > stClaimBloom

        // Transition: 'bloomingDone', 'choiceNeeded', 'endGame'
    }

    // If multiple bloom positions are possible, returns the possible ones
    public function argClaimChooseBloom() {
        //TODO: states > argClaimChooseBloom
    }

    // Player chose which flower blooms
    public function actClaimChooseBloom() {
        //TODO: states > actClaimChooseBloom

        // Transition: 'bloomingDone', 'noGiftReceived', 'endGame'
    }



    // Return which vines can be claimed by active player (through gifts)
    public function argClaimGift() {
        //TODO: states > argClaimGift
    }

    // The player claims a vine
    public function actClaimGift($x, $y, $angle) {
        //TODO: states > actClaimGift

        // Transition: 'continueGame', 'endGame'
    }

    // Blooms flowers after player claims gifts
    public function stClaimGiftBloom() {
        //TODO: states > stClaimGiftBloom

        // Transition: 'bloomingDone', 'choiceNeeded', 'endGame'
    }

    // If multiple bloom positions are possible, returns the possible ones
    public function argClaimGiftChooseBloom() {
        //TODO: states > argClaimGiftChooseBloom
    }

    // Player chose which flower blooms
    public function actClaimGiftChooseBloom() {
        //TODO: states > actClaimGiftChooseBloom

        // Transition: 'continueGame', 'endGame'
    }


    // Draw to 3 tiles and end a player's turn
    public function stEndTurn() {
        //TODO: states > stEndTurn

        // Transition: 'nextPlayer', 'endGame'
    }
}
