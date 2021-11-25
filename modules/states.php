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

        // Check tile exists
        $tile = $this->getTile(['tile_id' => $tile_id]);
        if ($tile === null)
        {
            throw new BgaVisibleSystemException(_('This tile does not exist'));
        }


        // Check tile is the player's one
        if ($tile['location'] != $this->getCurrentPlayerId())
        {
            throw new \BgaUserException(_('This tile is not in your hand'));
        }
        $possible_spots = $this->getPossibleTileSpots();

        // Check this spot is available
        if (!in_array(['x' => $x, 'y' => $y], $possible_spots))
        {
            throw new \BgaUserException(_('This tile can\'t be placed here'));
        }

        $this->plantTile($tile, $x, $y, $angle);

        $this->gamestate->nextState('');
    }

    // Blooms flowers after player puts a vine
    public function stPlantBloom() {
        $possible_bloom = $this->getBloomForTile($this->getGameStateValue('last_tile_planted'));

        self::dump('$possible_bloom', $possible_bloom);

        $need_choice = false;
        foreach ($possible_bloom as $color => $flowers)
        {
            if (count($flowers) == 1)
            {
                $this->bloomFlower(current($flowers));
            }
            else
            {
                $need_choice = true;
            }
        }

        self::dump('$need_choice', $need_choice);

        if ($this->checkPlayerWon())
        {
            $this->gamestate->nextState('endGame');
        }
        elseif ($need_choice)
        {
            //TODO: Test transition plant => bloom choice needed
            $this->gamestate->nextState('choiceNeeded');
        }
        else
        {
            $this->gamestate->nextState('bloomingDone');
        }
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

        // Transition: 'giftReceived', 'noGiftReceived', 'endGame'
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

    // Draw to 3 tiles and end a player's turn
    public function stEndTurn() {
        //TODO: states > stEndTurn

        // Transition: 'nextPlayer', 'endGame'
    }
}
