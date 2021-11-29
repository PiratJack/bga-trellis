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
        $tile = $this->getTileById($tile_id);
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
        $tile_id = $this->getGameStateValue('last_tile_planted');
        return [
            '_private' => [
                'active' => [
                    'possibleBlooms' => [$tile_id => $this->getBloomForTile($tile_id)]
                ]
            ]
        ];
    }

    // Player chose which flower blooms
    public function actPlantChooseBloom($selection) {
        // User allowed to do this?
        $this->checkAction('plantChooseBloom');

        $this->loadPlayersInfos();

        $tile_id = $this->getGameStateValue('last_tile_planted');
        $possible_blooms = $this->getBloomForTile($tile_id);

        // Check values provided are correct
        foreach ($selection as $vine_color => $player_id)
        {
            if (!in_array($vine_color, array_keys($this->color_translated)))
            {
                throw new \BgaUserException("Unknown vine color", true, true, FEX_bad_input_argument);
            }

            if (!in_array($player_id, $possible_blooms[$vine_color]['players']))
            {
                throw new \BgaUserException("Placing this flower here is impossible", true, true, FEX_bad_input_argument);
            }

            if (!array_key_exists($player_id, $this->players))
            {
                throw new \BgaUserException("This player does not exist", true, true, FEX_bad_input_argument);
            }

            $this->bloomFlower(['player_id' => $player_id, 'tile_id' => $tile_id, 'vine' => $vine_color]);
        }
        // Check all vines have a flower now
        foreach ($possible_blooms as $vine_color => $blooming)
        {
            if (!array_key_exists($vine_color, $selection))
            {
                throw new \BgaUserException("Missing choice for vine ".$vine_color, true, true, FEX_bad_input_argument);
            }
        }

        if ($this->checkPlayerWon())
        {
            $this->gamestate->nextState('endGame');
        }
        else
        {
            $this->gamestate->nextState('continueGame');
        }
    }



    // Return which vines can be claimed by active player (regular move)
    public function argClaim() {
        $tile_id = $this->getGameStateValue('last_tile_planted');
        return [
            '_private' => [
                'active' => [
                    'possibleFlowerSpots' => $this->getPossibleFlowerSpots($tile_id)
                ]
            ]
        ];
    }

    // The player claims a vine
    public function actClaim($tile_id, $vine_color) {
        $tile_id = $this->getGameStateValue('last_tile_planted');
        $possibleFlowerSpots = $this->getPossibleFlowerSpots($tile_id);

        if (!array_key_exists($tile_id, $possibleFlowerSpots))
        {
            throw new \BgaUserException("That tile is not available", true, true, FEX_bad_input_argument);
        }
        if (!array_key_exists($vine_color, $possibleFlowerSpots[$tile_id]))
        {
            throw new \BgaUserException("That vine is not available", true, true, FEX_bad_input_argument);
        }

        $this->bloomFlower(['player_id' => self::getActivePlayerId(), 'tile_id' => $tile_id, 'vine' => $vine_color]);

        if ($this->checkPlayerWon())
        {
            $this->gamestate->nextState('endGame');
        }
        else
        {
            $this->gamestate->nextState('continueGame');
        }
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
