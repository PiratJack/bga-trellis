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

namespace Trellis;

trait StatesTrait {
    // Returns possible moves for the active player
    public function argPlant() {
        return [
            'possibleTileSpots' => $this->getPossibleTileSpots()
        ];
    }

    // The player plants a tile
    public function actPlant($tile_id, $x, $y, $angle) {
        // User allowed to do this?
        $this->gamestate->checkPossibleAction('plant');

        // Check tile exists
        $tile = $this->getTileById($tile_id);
        if ($tile === null) {
            throw new \BgaVisibleSystemException(self::_('This tile does not exist'));
        }


        // Check tile is the player's one
        if ($tile['location'] != $this->getActivePlayerId()) {
            throw new \BgaUserException(self::_('This tile is not in your hand'));
        }
        $possible_spots = $this->getPossibleTileSpots();

        // Check this spot is available
        if (!in_array(['x' => $x, 'y' => $y], $possible_spots)) {
            throw new \BgaUserException(self::_('This tile can\'t be placed here'));
        }

        $this->plantTile($tile, $x, $y, $angle);

        $this->gamestate->nextState('');
    }

    // The player plays its pre-selected card
    public function stPlant() {
        $active_player_id = $this->getActivePlayerId();
        $pre_planted_tile = self::getUniqueValueFromDB('SELECT pre_planted_tile FROM player WHERE player_id="'.$active_player_id.'"');
        if (!$pre_planted_tile) {
            return;
        }

        $pre_planted_tile = explode(';', $pre_planted_tile);
        $tile = $pre_planted_tile[0];
        $x = $pre_planted_tile[1];
        $y = $pre_planted_tile[2];
        $angle = $pre_planted_tile[3];

        try {
            $this->actPlant($tile, $x, $y, $angle);
        } catch (\BgaUserException $e) {
            // Simply ignore it: we'll go to argPlant and allow the player to play
            // Exceptions mean that the selected spot is no longer valid
        }
    }

    // An inactive player plants a tile
    public function actPrePlant($tile_id, $x, $y, $angle) {
        // User allowed to do this?
        $this->gamestate->checkPossibleAction('prePlant');
        $current_player_id = $this->getCurrentPlayerId();

        // Deleted previous choice?
        if ($tile_id == 0) {
            $this->resetPrePlantedTile($current_player_id);

            self::notifyPlayer(
                $current_player_id,
                'prePlantTile',
                _('Your pre-planting has been cancelled'),
                [
                    'action' => 'cancelled',
                ]
            );
            return;
        }

        // Check tile exists
        $tile = $this->getTileById($tile_id);
        if ($tile === null) {
            throw new \BgaVisibleSystemException(self::_('This tile does not exist'));
        }


        // Check tile is the player's one
        if ($tile['location'] != $this->getCurrentPlayerId()) {
            throw new \BgaUserException(self::_('This tile is not in your hand'));
        }
        $possible_spots = $this->getPossibleTileSpots();

        // Check this spot is available
        if (!in_array(['x' => $x, 'y' => $y], $possible_spots)) {
            throw new \BgaUserException(self::_('This tile can\'t be placed here'));
        }

        // Update the "last_tile_placed" on the player table
        $pre_planted_tile = strval($tile_id).';'.strval($x).';'.strval($y).';'.strval($angle);
        $this->setPrePlantedTile($current_player_id, $pre_planted_tile);

        self::notifyPlayer(
            $current_player_id,
            'prePlantTile',
            _('Your pre-planting has been recorded'),
            [
                'action' => 'confirmed',
                'pre_planted_tile' => [
                    'tile_id' => $tile_id,
                    'x' => $x,
                    'y' => $y,
                    'angle' => $angle,
                    'location' => 'board',

                ]
            ]
        );
    }

    // Blooms flowers after player puts a vine
    public function stPlantBloom() {
        $tile_id = $this->getGameStateValue('last_tile_planted');
        $possible_bloom = $this->getBloomForTile($tile_id);

        $need_choice = false;
        foreach ($possible_bloom as $color => $flowers) {
            if (count($flowers['players']) == 1) {
                $player_id = current($flowers['players']);
                $flower = [
                    'player_id' => $player_id,
                    'tile_id' => $tile_id,
                    'vine' => $color,
                ];
                $new_flower = $this->bloomFlower($flower);
                $vines = $this->getBloomForFlower($new_flower['flower_id']);

                foreach ($vines as $vine_color => $tile_ids) {
                    foreach ($tile_ids as $add_tile_id) {
                        $additional_flower = [
                            'player_id' => $player_id,
                            'tile_id' => $add_tile_id,
                            'vine' => $vine_color,
                        ];
                        $f = $this->bloomFlower($additional_flower);
                    }
                }
            } else {
                $need_choice = true;
            }
        }

        if ($this->checkPlayerWon()) {
            $this->gamestate->nextState('endGame');
        } elseif ($need_choice) {
            $this->gamestate->nextState('choiceNeeded');
        } else {
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
            ],
            'possibleTileSpots' => $this->getPossibleTileSpots()
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
        foreach ($selection as $vine_color => $player_id) {
            if (!in_array($vine_color, array_keys($this->color_translated))) {
                throw new \BgaUserException(self::_('Unknown vine color'), true, true, FEX_bad_input_argument);
            }

            if (!in_array($player_id, $possible_blooms[$vine_color]['players'])) {
                throw new \BgaUserException(self::_('Placing this flower here is impossible'), true, true, FEX_bad_input_argument);
            }

            if (!array_key_exists($player_id, $this->players)) {
                throw new \BgaUserException(self::_('This player does not exist'), true, true, FEX_bad_input_argument);
            }

            $this->bloomFlower(['player_id' => $player_id, 'tile_id' => $tile_id, 'vine' => $vine_color]);
        }
        // Check all vines have a flower now
        foreach ($possible_blooms as $vine_color => $blooming) {
            if (!array_key_exists($vine_color, $selection)) {
                throw new \BgaUserException(str_replace('${vine_color}', $vine_color, self::_('Missing choice for vine ${vine_color}')), true, true, FEX_bad_input_argument);
            }
        }

        $this->transitionIfPlayerWon('endGame', 'continueGame');
    }


    // Check if a vine can be claimed (enough room?)
    public function stCheckClaimVine() {
        $tile_id = $this->getGameStateValue('last_tile_planted');
        $possible_flower_spots = $this->getPossibleFlowerSpots($tile_id);
        if (count($possible_flower_spots) == 0) {
            self::notifyAllPlayers(
                'message',
                clienttranslate('${player_name} can\'t claim any flower as there is no room left'),
                [
                    'player_name' => $this->getActivePlayerName(),
                ]
            );
            $this->gamestate->nextState('claimImpossible');
        } else {
            $this->gamestate->nextState('claimPossible');
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
            ],
            'possibleTileSpots' => $this->getPossibleTileSpots(),
        ];
    }

    // The player claims a vine
    public function actClaim($tile_id, $vine_color) {
        $this->checkAction('claim');

        $possibleFlowerSpots = $this->getPossibleFlowerSpots($tile_id);

        if (!array_key_exists($tile_id, $possibleFlowerSpots)) {
            throw new \BgaUserException(self::_('That tile is not available'), true, true, FEX_bad_input_argument);
        }
        if (!array_key_exists($vine_color, $possibleFlowerSpots[$tile_id])) {
            throw new \BgaUserException(self::_('That vine is not available'), true, true, FEX_bad_input_argument);
        }

        $this->claimVine(['player_id' => self::getActivePlayerId(), 'tile_id' => $tile_id, 'vine' => $vine_color]);

        $this->transitionIfPlayerWon('endGame', 'continueGame');
    }

    // Blooms flowers after player claims a vine
    public function stClaimBloom() {
        $blooms = $this->getBloomForFlower($this->getGameStateValue('last_flower_claimed'));
        foreach ($blooms as $vine_color => $tile_ids) {
            $flowers = array_map(function ($tile_id) use ($vine_color) {
                return [
                    'player_id' => $this->getActivePlayerId(),
                    'tile_id' => $tile_id,
                    'vine' => $vine_color
                ];
            }, $tile_ids);

            foreach ($flowers as $flower) {
                $this->bloomFlower($flower);
            }
        }

        $this->reloadPlayersInfos();

        if ($this->checkPlayerWon()) {
            $this->gamestate->nextState('endGame');
        } elseif ($this->players[$this->getActivePlayerId()]['gift_points'] != 0) {
            $this->gamestate->nextState('giftReceived');
        } else {
            $this->gamestate->nextState('noGiftReceived');
        }
    }



    // Return which vines can be claimed by active player (through gifts)
    public function argClaimGift() {
        $tile_id = $this->getGameStateValue('last_tile_planted');
        $player_id = $this->getActivePlayerId();
        $gift_points = $this->loadPlayersInfos()[$player_id]['gift_points'];

        return [
            '_private' => [
                'active' => [
                    'possibleFlowerSpots' => $this->getPossibleFlowerSpots($tile_id, $gift_points),
                    'mainTile' => $tile_id,
                ]
            ],
            'gift_points' => $gift_points,
            'possibleTileSpots' => $this->getPossibleTileSpots(),
        ];
    }

    // The player claims gift(s)
    public function actClaimGift($selection) {
        $this->checkAction('claimGift');

        $last_tile_id = $this->getGameStateValue('last_tile_planted');
        $player_id = $this->getActivePlayerId();
        $gift_points = $this->loadPlayersInfos()[$player_id]['gift_points'];
        $possible_spots = $this->getPossibleFlowerSpots($last_tile_id, $gift_points);
        $count_possible_spots = array_sum(array_map(function ($val) {
            return count($val);
        }, $possible_spots));

        // Check enough gift points were claimed
        $selection_count = array_sum(array_map(function ($v) {
            return count($v);
        }, $selection));

        if ($count_possible_spots > $gift_points) {
            // Enough available spots to take all gifts
            if ($selection_count < $gift_points) {
                throw new \BgaUserException(self::_('You received more gifts, please choose additional spots'));
            }
        } else {
            // Not enough available spots
            if ($selection_count != $count_possible_spots) {
                throw new \BgaUserException(self::_('You received more gifts, please choose additional spots'));
            }
        }
        if ($selection_count > $gift_points) {
            throw new \BgaUserException(self::_('You received less gifts, please choose less spots'));
        }

        // Check the player took all the "last tile played" gifts
        if (array_key_exists($last_tile_id, $possible_spots)) {
            if (count($possible_spots[$last_tile_id]) <= $gift_points) {
                if (!array_key_exists($last_tile_id, $selection) || count($possible_spots[$last_tile_id]) != count($selection[$last_tile_id])) {
                    throw new \BgaUserException(self::_('You must claim all vines from the last tile placed before claiming others'));
                }
            }
        }

        $vines_claimed = [];
        foreach ($selection as $tile_id => $vines) {
            if (!array_key_exists($tile_id, $possible_spots)) {
                throw new \BgaUserException(str_replace('${tile}', $tile_id, self::_('Tile ${tile} can\'t be selected')));
            }
            foreach ($vines as $vine_color) {
                if (!array_key_exists($vine_color, $possible_spots[$tile_id])) {
                    throw new \BgaUserException(str_replace('${vine}', $vine_color, str_replace('${tile}', $tile_id, self::_('Tile ${tile} does not have a ${vine} vine'))));
                }
                $vines_claimed[] = ['player_id' => self::getActivePlayerId(), 'tile_id' => $tile_id, 'vine' => $vine_color];
            }
        }

        $this->claimVines($vines_claimed, $player_id);

        $this->transitionIfPlayerWon('endGame', 'continueGame');
    }

    // Blooms flowers after player claims gifts
    public function stClaimGiftBloom() {
        $flowers = $this->getFlowers();
        $last_flower_claimed = $this->getGameStateValue('last_flower_claimed');
        $new_flowers = array_filter($flowers, function ($f) use ($last_flower_claimed) {
            return $f['flower_id'] >= $last_flower_claimed;
        });

        foreach ($new_flowers as $flower) {
            $blooms = $this->getBloomForFlower($flower['flower_id']);
            foreach ($blooms as $vine_color => $tile_ids) {
                $flowers = array_map(function ($tile_id) use ($vine_color) {
                    return [
                        'player_id' => $this->getActivePlayerId(),
                        'tile_id' => $tile_id,
                        'vine' => $vine_color
                    ];
                }, $tile_ids);

                foreach ($flowers as $flower) {
                    $this->bloomFlower($flower);
                }
            }

            $this->reloadPlayersInfos();
        }

        $this->transitionIfPlayerWon('endGame', 'bloomingDone');
    }

    // Draw to 3 tiles and end a player's turn
    public function stEndTurn() {
        $active_player_id = $this->getActivePlayerId();
        // Pick a tile for player's hand
        $new_tile = $this->pickTiles(1, "deck", $active_player_id);

        self::notifyPlayer(
            $active_player_id,
            'pickTile',
            '',
            [
                'tile' => current($new_tile),
            ]
        );

        $this->activeNextPlayer();
        $active_player_id = $this->getActivePlayerId();
        self::giveExtraTime($active_player_id);

        $this->transitionIfPlayerWon('endGame', 'nextPlayer');
    }

    // Transitions to different game states if a player has won or not
    public function transitionIfPlayerWon($if_won, $if_not_won) {
        if ($this->checkPlayerWon()) {
            $this->gamestate->nextState($if_won);
        } else {
            $this->gamestate->nextState($if_not_won);
        }
    }
}
