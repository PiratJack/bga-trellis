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

    // Determines where flowers can be placed, based on a given tile
    // if tile is full, then all tiles will be considered
    private function getPossibleFlowerSpots($tile) {
        //TODO: Flowers > getPossibleFlowerSpots
    }

    // Puts a flower on a given position
    private function placeFlower($flowers) {
        // Generate SQL
        $sql = 'INSERT INTO flowers (player_id, x, y, position_on_tile) VALUES ';
        $value = '("${player_id}", ${x}, ${y}, "${position_on_tile}")';
        $values = [];

        foreach ($flowers as $flower)
        {
            $values[] = '("'.$flower['player_id'].'", '.$flower['x'].', '.$flower['y'].', "'.$flower['position_on_tile'].'")';
        }
        $sql .= implode(', ', $values);

        self::DbQuery($sql);

        $this->reloadFlowers();
    }

    // Remove all flowers from the board
    private function removeAllFlowers() {
        $sql = 'TRUNCATE tiles';
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

    // Gets flowers from a given location
    private function getFlowersFromLocation($x, $y) {
        return $this->getFlowers(['x' => $x, 'y' => $y]);
    }

    // Reloads flowers from the DB
    private function reloadFlowers() {
        $this->flowers = self::getCollectionFromDB('SELECT flower_id, player_id, x, y, position_on_tile FROM flowers');

        foreach ($this->flowers as $id => $flower)
        {
            $this->flowers[$id]['x']     = intval($flower['x']);
            $this->flowers[$id]['y']     = intval($flower['y']);
            $this->flowers[$id]['angle'] = intval($flower['angle']);
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
