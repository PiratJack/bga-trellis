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
    private function getBloomForTile($tile_id) {
        $tile = $this->getTile(['tile_id' => $tile_id]);

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

                $blooming_flowers[$vine_color][] = $blooming_flower;
            }
        }
        return $blooming_flowers;
    }

    // Determines where flowers can be placed, based on a given tile
    // if tile is full, then all tiles will be considered
    private function getPossibleFlowerSpots($tile) {
        //TODO: Flowers > getPossibleFlowerSpots
    }

    // Puts a flower on a given position
    private function placeFlower($flower) {
        // Generate SQL
        $sql = 'INSERT INTO flowers (player_id, tile_id, vine) VALUES ';
        $sql .= '('.$flower['player_id'].', '.$flower['tile_id'].', "'.$flower['vine'].'")';

        self::DbQuery($sql);
        $flower_id = $this->DbGetLastId();

        $this->reloadFlowers();
        return $this->getFlowerById($flower_id);
    }

    // Blooms a flower & notifies the players
    private function bloomFlower($flowers) {
        if (array_key_exists('player_id', $flowers))
        {
            $flowers = [$flowers];
        }

        foreach ($flowers as $flower)
        {
            $flower = $this->placeFlower($flower);
            self::notifyAllPlayers(
                'flowerBlooms',
                clienttranslate('The ${vine_color} vine blooms a flower for ${player_name}'),
                [
                    'vine_color' => $flower['vine'],
                    'vine_color_translated' => $this->color_translated[$flower['vine']],
                    'player_name' => self::getPlayerNameById($flower['player_id']),
                    'flower' => $flower,
                    'preserve' => [ 'vine_color_translated' ],
                ]
            );
        }

        $this->reloadFlowers();
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
        if (!isset($this->flowers))
        {
            $this->reloadFlowers();
        }

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
