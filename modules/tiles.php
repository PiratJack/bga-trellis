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

trait TilesTrait {
    // Sets up tiles for a new game
    private function tiles_setupNewGame($players, $options = []) {
        $sql = 'INSERT INTO tiles (tile_id, tile_type, location) VALUES ';
        $values = [];
        //REAL_ART : is the tile_type counting still valid? do we need a separate tile_type or not?
        for ($i = 0; $i < count($this->tile_types); $i++)
        {
            $values[] = '('.$i.', '.$i.', "'.($i==0?'board':'deck').'")';
        }
        $sql .= implode(', ', $values);
        self::DbQuery($sql);

        // Shuffle and distribute
        $this->shuffleTilesInLocation('deck');

        foreach ($this->players as $player_id => $player)
        {
            $this->pickTiles(3, 'deck', $player_id);
        }
        $this->reloadTiles();
    }

    // Determines where a tile can be placed, and in which orientation (if empty, will use the player's tiles)
    private function getPossibleTileSpots() {
        // Find all spots near existing tiles
        $spots = [];
        $board_tiles = $this->getTilesFromLocation('board');
        foreach ($board_tiles as $tile_id => $board_tile)
        {
            $tile_type = $this->rotateTileType($this->tile_types[$board_tile['tile_type']], $board_tile['angle']);
            foreach ($this->directions as $angle => $delta_position)
            {
                $neighbor_x = $board_tile['x'] + $delta_position['x'];
                $neighbor_y = $board_tile['y'] + $delta_position['y'];

                $color = array_keys(array_filter($tile_type['vines'], function ($vine) use ($angle) {
                    return in_array($angle, $vine);
                }))[0];
                $neighbor_direction = (180 + $angle) % 360;

                if (!in_array(['x' => $neighbor_x, 'y' => $neighbor_y], $spots))
                {
                    $spots[] = ['x' => $neighbor_x, 'y' => $neighbor_y];
                }
            }
        }

        // Remove occupied spots
        foreach ($board_tiles as $tile_id => $board_tile)
        {
            $spots = array_filter($spots, function ($spot) use ($board_tile) {
                return ($spot['x'] != $board_tile['x'] || $spot['y'] != $board_tile['y']);
            });
        }

        return $spots;
    }

    // Rotates a tile
    private function rotateTileType($tile_type, $angle) {
        $tile_type_rotated = $tile_type;

        $tile_type_rotated['vines'] = array_map(function ($initial_angles) use ($angle) {
            return array_map(function ($initial_angle) use ($angle) {
                return ($initial_angle + $angle) % 360;
            }, $initial_angles);
        }, $tile_type_rotated['vines']);

        return $tile_type_rotated;
    }

    // Get neighbors of a given tile or position
    private function getTileNeighbors($tile) {
        $neighbors = [];
        foreach ($this->directions as $angle => $delta)
        {
            $params = ['x' => $tile['x'] + $delta['x'], 'y' => $tile['y'] + $delta['y'], 'location' => 'board'];
            $neighbor = $this->getTile($params);
            if ($neighbor)
            {
                $neighbors[$neighbor['tile_id']] = $neighbor;
            }
        }
        return $neighbors;
    }

    // Get the neighbor of a given tile or position in a given direction
    // The $angle should have already been rotated
    private function getTileNeighborByAngle($tile, $angle) {
        $neighbors = [];
        $delta = $this->directions[$angle];
        $params = ['x' => $tile['x'] + $delta['x'], 'y' => $tile['y'] + $delta['y'], 'location' => 'board'];
        $neighbor = $this->getTile($params);
        if ($neighbor)
        {
            return $neighbor;
        }

        return null;
    }

    // Returns whether 2 tiles are a match based on their vines & orientation
    private function isMatch($tile1, $tile2) {
        //TODO: Tiles > isMatch
    }

    // Picks the first n tiles from one location to another
    private function pickTiles($nb_tiles, $source, $target) {
        $sql = 'UPDATE tiles SET location = "'.$target.'" WHERE location = "'.$source.'" ORDER BY location_order LIMIT '.$nb_tiles;
        self::DbQuery($sql);
        $this->reloadTiles();
    }

    // Moves some tiles to a location
    // Default for $target: 'location' => 'deck', 'x' => 0, 'y' => 0, 'location_order' => 0, 'angle' => 0
    private function moveTilesToLocation($tiles, $target) {
        // $tiles can be the ID of a single tile, or an array of tile IDs
        if (!is_array($tiles) || !array_key_exists('tile_id', current($tiles)))
        {
            $tiles = $this->getTiles(['tile_id' => $tiles]);
        }

        $tile_ids = array_map(function ($v) {
            return $v['tile_id'];
        }, $tiles);

        // Setup the target & parameters
        $default_target = [
            'location' => 'deck',
            'x' => 0,
            'y' => 0,
            'location_order' => 0,
            'angle' => 0,
        ];
        $params = array_merge($default_target, $target);
        $params['tiles_id'] = implode(', ', $tile_ids);

        // Generate SQL
        $sql = 'UPDATE tiles SET location = "${location}", x = ${x}, y = ${y}, location_order = ${location_order}, angle = ${angle} WHERE tile_id IN (${tiles_id})';

        foreach ($params as $source => $target)
        {
            $sql = str_replace('${' . $source . '}', $target, $sql);
        }

        self::DbQuery($sql);

        $this->reloadTiles();
    }

    // Moves all tiles from one place to another
    private function moveAllTilesToLocation($source, $target) {
        // Assumptions: this is meant to move to either player's hand or deck
        // Therefore, x and y do not make sense

        // Generate SQL
        $sql = 'UPDATE tiles SET location = "${target}", x = 0, y = 0, location_order = 0, angle = 0';
        $sql = str_replace($sql, '${target}', $target);

        if ($source != '')
        {
            $sql .= ' WHERE location = "'.$source.'"';
        }

        self::DbQuery($sql);

        $this->reloadTiles();
    }

    // Shuffles tiles in a given location
    private function shuffleTilesInLocation($location) {
        $tiles = $this->getTilesFromLocation($location);

        $tiles_ids = array_keys($tiles);
        shuffle($tiles_ids);
        foreach ($tiles_ids as $order => $tile_id)
        {
            self::DbQuery('UPDATE tiles SET location_order = '.$order.'. WHERE tile_id = '.$tile_id);
        }

        $this->reloadTiles();
    }

    // Gets tiles based on some parameters
    private function getTiles($params = []) {
        if (!isset($this->tiles))
        {
            $this->reloadTiles();
        }

        foreach ($params as $key => $value)
        {
            if (!is_array($value))
            {
                $params[$key] = [$value];
            }
        }


        $tiles = array_filter($this->tiles, function ($tile) use ($params) {
            foreach ($params as $key => $value)
            {
                if (!in_array($tile[$key], $value))
                {
                    return false;
                }
            }

            return true;
        });

        return $tiles;
    }

    // Gets a single tile based on some parameters - if there are more than 1 result, returns null
    private function getTile($params = []) {
        $tiles = $this->getTiles($params);

        if (count($tiles) != 1)
        {
            return null;
        }

        return current($tiles);
    }

    // Gets tiles from a given location
    private function getTilesFromLocation($location) {
        return $this->getTiles(['location' => $location]);
    }

    // Reloads tiles from the DB
    private function reloadTiles() {
        $this->tiles = self::getCollectionFromDB('SELECT tile_id, tile_type, location, location_order, x, y, angle FROM tiles');

        foreach ($this->tiles as $id => $tile)
        {
            $this->tiles[$id]['x']     = intval($tile['x']);
            $this->tiles[$id]['y']     = intval($tile['y']);
            $this->tiles[$id]['angle'] = intval($tile['angle']);

            $this->tiles[$id]['sprite_position'] = $this->tile_types[$tile['tile_type']]['sprite_position'];
        }
        return $this->tiles;
    }

    // Returns all tile data for getAllDatas
    private function tiles_getAllDatas() {
        return $this->getTilesFromLocation('board') + $this->getTilesFromLocation(self::getCurrentPlayerId());
    }
}
