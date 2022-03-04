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

trait FlowersTrait {
    // Sets up flowers for a new game
    private function flowers_setupNewGame($players, $options = []) {
        $this->flowers = [];
    }

    // Determines which flowers should bloom
    // Returns structure: vine_color => ['players' => $player_ids, 'angle' => $angle]
    private function getBloomForTile($tile_id) {
        $tile = $this->getTileById($tile_id);

        // Get data on vines & flowers
        $vines = $this->rotateTileType($this->tile_types[$tile['tile_type']], $tile['angle'])['vines'];
        $flowers_on_tile = $this->getFlowers(['tile_id' => $tile['tile_id']]);
        $occupied_vines = array_map(function ($v) {
            return $v['vine'];
        }, $flowers_on_tile);

        // Remove occupied spots
        $available_vines = array_filter($vines, function ($vine_color) use ($occupied_vines) {
            return !in_array($vine_color, $occupied_vines);
        }, ARRAY_FILTER_USE_KEY);

        $blooming_flowers = [];
        foreach ($available_vines as $vine_color => $angles)
        {
            foreach ($angles as $angle)
            {
                // Get neighbor in that direction
                $neighbor = $this->getTileNeighborByAngle($tile, $angle);
                if ($neighbor === null)
                {
                    continue 1;
                }

                // A vine of the same color exists
                $neighbor_type = $this->rotateTileType($this->tile_types[$neighbor['tile_type']], $neighbor['angle']);
                if (!array_key_exists($vine_color, $neighbor_type['vines']))
                {
                    continue 1;
                }

                // The vine is in the right angle
                if (!in_array(($angle+180)%360, $neighbor_type['vines'][$vine_color]))
                {
                    continue 1;
                }

                // There is a flower on that vine
                $neighbor_flower = $this->getFlowers(['tile_id' => $neighbor['tile_id'], 'vine' => $vine_color]);
                if (count($neighbor_flower) != 1)
                {
                    continue 1;
                }

                // Define the target flower
                $target = [
                    'tile_id' => $tile['tile_id'],
                    'flower_id' => 0,
                ];
                $blooming_flower = $target + current($neighbor_flower);

                $blooming_flowers[$vine_color]['angle'] = $angle;
                $blooming_flowers[$vine_color]['players'][] = $blooming_flower['player_id'];
                $blooming_flowers[$vine_color]['players'] = array_unique($blooming_flowers[$vine_color]['players']);
            }
        }
        return $blooming_flowers;
    }

    // Determines where flowers can be placed, based on a given tile
    // if tile is full, then all tiles will be considered
    // Returns $tile_id => [$vine_color => $angles]
    private function getPossibleFlowerSpots($tile_id, $gift_points = 0) {
        $tile = $this->getTileById($tile_id);

        // Get data on vines & flowers
        $vines = $this->rotateTileType($this->tile_types[$tile['tile_type']], $tile['angle'])['vines'];
        $flowers_on_tile = $this->getFlowers(['tile_id' => $tile['tile_id']]);
        $occupied_vines = array_map(function ($v) {
            return $v['vine'];
        }, $flowers_on_tile);

        // Remove occupied spots
        $available_vines = [$tile_id => array_filter($vines, function ($vine_color) use ($occupied_vines) {
            return !in_array($vine_color, $occupied_vines);
        }, ARRAY_FILTER_USE_KEY)];

        // No spot left on this tile, so we can plant anywhere
        // OR I get more gifts than the tile can provide
        if ($available_vines == [] || $available_vines[$tile_id] == [] || count($available_vines[$tile_id]) < $gift_points)
        {
            $tiles = $this->getTilesFromLocation('board');
            foreach ($tiles as $tile_id => $tile)
            {
                // Get data on vines & flowers
                $vines = $this->rotateTileType($this->tile_types[$tile['tile_type']], $tile['angle'])['vines'];
                $flowers_on_tile = $this->getFlowers(['tile_id' => $tile['tile_id']]);
                $occupied_vines = array_map(function ($v) {
                    return $v['vine'];
                }, $flowers_on_tile);

                // Remove occupied spots
                $available_vines[$tile_id] = array_filter($vines, function ($vine_color) use ($occupied_vines) {
                    return !in_array($vine_color, $occupied_vines);
                }, ARRAY_FILTER_USE_KEY);

                if ($available_vines[$tile_id] == [])
                {
                    unset($available_vines[$tile_id]);
                }
            }
        }

        return $available_vines;
    }

    // Determines where a given flower will bloom
    // Returns structure: vine_color => [$tile_ids]
    public function getBloomForFlower($flower_id) {
        // Get data on flower & tile
        $flower = $this->getFlowerById($flower_id);
        $vine_color = $flower['vine'];
        $tile = $this->getTileById($flower['tile_id']);
        $tile_type = $this->rotateTileType($this->tile_types[$tile['tile_type']], $tile['angle']);
        $angles = $tile_type['vines'][$flower['vine']];

        // Travel through the vine in both directions
        $blooming_flowers = [];
        foreach ($angles as $direction)
        {
            $angle = $direction;
            $neighbor = $tile;

            // The 10 is random, to avoid infinite loops
            for ($i = 0; $i <= 10; $i++)
            {
                $neighbor = $this->getTileNeighborByAngle($neighbor, $angle);
                if ($neighbor == null)
                {
                    break;
                }

                // A vine of the same color exists
                $neighbor_type = $this->rotateTileType($this->tile_types[$neighbor['tile_type']], $neighbor['angle']);
                if (!array_key_exists($vine_color, $neighbor_type['vines']))
                {
                    break;
                }

                // The vine is in the right angle
                if (!in_array(($angle+180)%360, $neighbor_type['vines'][$vine_color]))
                {
                    break;
                }

                // Check there is no flower there already. If there is one, stop (because we can't go further anyway)
                $existing_flower = array_filter($this->flowers, function ($f) use ($neighbor, $vine_color) {
                    return $f['tile_id'] == $neighbor['tile_id'] && $f['vine'] == $vine_color;
                });
                if (count($existing_flower) != 0)
                {
                    break;
                }

                // Add spot to list
                $blooming_flowers[$vine_color][] = $neighbor['tile_id'];

                // Continue the file
                $angle = array_filter($neighbor_type['vines'][$vine_color], function ($a) use ($angle) {
                    return $a != ($angle+180)%360;
                });
                if (count($angle) == 0)
                {
                    break;
                }

                $angle = current($angle);
            }
        }
        return $blooming_flowers;
    }


    // Puts a flower on a given position
    private function placeFlower($flower) {
        // Generate SQL
        $sql = 'INSERT INTO flowers (player_id, tile_id, vine) VALUES ';
        $sql .= '('.$flower['player_id'].', '.$flower['tile_id'].', "'.$flower['vine'].'")';

        self::DbQuery($sql);
        $flower_id = $this->DbGetLastId();

        $this->addPoints($flower['player_id'], 1);

        $this->reloadFlowers();
        return $this->getFlowerById($flower_id);
    }

    // Blooms a flower & notifies the players
    private function bloomFlower($flower) {
        $flower = $this->placeFlower($flower);
        if ($flower['player_id'] == $this->getActivePlayerId())
        {
            $message = clienttranslate('Vine ${vine_color} blooms for ${player_name}');
            $this->incStat(1, 'flowers_bloomed', $this->getActivePlayerId());
        }
        else
        {
            $message = clienttranslate('Vine ${vine_color} blooms for ${player_name}. ${player_name2} gets a gift point.');
            $this->addGiftPoints($this->getActivePlayerId(), 1);
            $this->incStat(1, 'flowers_received', $flower['player_id']);
        }

        self::notifyAllPlayers(
            'flowerBlooms',
            $message,
            [
                'vine_color' => $flower['vine'],
                'player_name' => self::getPlayerNameById($flower['player_id']),
                'player_name2' => self::getActivePlayerName(),
                'flower' => $flower,
                'i18n' => ['vine_color'],
            ]
        );

        $this->reloadFlowers();
        $this->notifScores();
        return $flower;
    }

    // Claims a flower & notify players
    private function claimVine($flower) {
        $flower = $this->placeFlower($flower);
        $this->incStat(1, 'flowers_placed', $flower['player_id']);

        $this->setGameStateValue('last_flower_claimed', $flower['flower_id']);

        self::notifyAllPlayers(
            'flowerBlooms',
            clienttranslate('${player_name} claims vine ${vine_color}'),
            [
                'vine_color' => $flower['vine'],
                'player_name' => self::getActivePlayerName(),
                'flower' => $flower,
                'i18n' => ['vine_color'],
            ]
        );

        $this->reloadFlowers();
        $this->notifScores();

        return $flower;
    }

    // Claims all gifts at once
    private function claimVines($vines_claimed, $player_id) {
        self::notifyAllPlayers(
            'message',
            clienttranslate('${player_name} claims ${gift_points} gift(s)'),
            [
                'player_name' => $this->loadPlayersInfos()[$player_id]['player_name'],
                'gift_points' => count($vines_claimed),
            ]
        );

        $first_flower = true;
        foreach ($vines_claimed as $vine)
        {
            $flower = $this->claimVine($vine);
            if ($first_flower)
            {
                $this->setGameStateValue('last_flower_claimed', $flower['flower_id']);
            }
            $first_flower = false;
        }

        $this->resetGiftPoints($player_id);
    }


    // Remove all flowers from the board
    private function removeAllFlowers() {
        $sql = 'DELETE FROM flowers WHERE 1';
        self::DbQuery($sql);

        $this->players_removeAllFlowers();

        $this->flowers = [];
    }

    // Gets flowers based on some parameters
    private function getFlowers($params = []) {
        $this->loadFlowers();

        foreach ($params as $key => $value)
        {
            if (!is_array($value))
            {
                $params[$key] = [$value];
            }
        }

        $flowers = array_filter($this->flowers, function ($flower) use ($params) {
            foreach ($params as $key => $value)
            {
                if (!in_array($flower[$key], $value))
                {
                    return false;
                }
            }

            return true;
        });

        return $flowers;
    }

    // Gets a single flower based on its ID
    private function getFlowerById($flower_id) {
        return $this->getFlowers(['flower_id' => $flower_id])[$flower_id];
    }

    // Reloads flowers from the DB
    private function reloadFlowers() {
        $this->flowers = self::getCollectionFromDB('SELECT flower_id, player_id, tile_id, vine FROM flowers');
        $this->getTiles();

        foreach ($this->flowers as $id => $flower)
        {
            $tile = $this->tiles[$flower['tile_id']];
            $tile_type = $this->tile_types[$tile['tile_id']];

            $this->flowers[$id]['angle'] = current($tile_type['vines'][$flower['vine']]);
        }

        return $this->flowers;
    }

    // Get flowers data, with caching
    private function loadFlowers() {
        if (!isset($this->flowers))
        {
            $this->reloadFlowers();
        }

        return $this->flowers;
    }

    // Returns all flowers data for getAllDatas
    private function flowers_getAllDatas() {
        return $this->getFlowers();
    }
}
