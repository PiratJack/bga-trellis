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

trait PlayersTrait {
    // Sets up players for a new game
    private function players_setupNewGame($players, $options = []) {
        /*** Create players with proper colors ***/
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = [];
        foreach ($players as $player_id => $player)
        {
            $color = array_shift($default_colors);
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes($player['player_name'])."','".addslashes($player['player_avatar'])."')";
        }
        $sql .= implode($values, ',');
        self::DbQuery($sql);
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        $this->reloadPlayersInfos(true);
    }

    // Gives regular points to a player
    private function addPoints($player_id, $nb_points) {
        self::DbQuery('UPDATE player SET player_score = player_score + '.$nb_points.' WHERE player_id = '.$player_id);
        self::reloadPlayersInfos();

        $this->notifScores();
    }

    // Gives gift points to a player
    private function addGiftPoints($player_id, $nb_points) {
        self::DbQuery('UPDATE player SET gift_points = gift_points + '.$nb_points.' WHERE player_id = '.$player_id);
        self::reloadPlayersInfos();
    }

    // Resets gift points to 0 for a player
    private function resetGiftPoints($player_id) {
        self::DbQuery('UPDATE player SET gift_points = 0 WHERE player_id = '.$player_id);
        self::reloadPlayersInfos();
    }

    // Returns all players data for getAllDatas
    private function players_getAllDatas() {
        return self::loadPlayersInfos();
    }

    // Loads player data, with caching
    private function loadPlayersInfos() {
        $this->players = self::loadPlayersBasicInfos();
        if (!array_key_exists('gift_points', current($this->players)))
        {
            $this->reloadPlayersInfos();
        }

        return $this->players;
    }

    // Reloads all database data for players
    private function reloadPlayersInfos($newGame = false) {
        self::reloadPlayersBasicInfos();
        $this->players = self::loadPlayersBasicInfos();
        $data = self::getCollectionFromDB('SELECT player_id, player_score, gift_points, 15 as flowers_left FROM player');

        foreach ($this->players as $player_id => $player)
        {
            $this->players[$player_id] = array_merge($player, $data[$player_id]);
        }

        if (!$newGame)
        {
            $this->loadFlowers();

            foreach (array_keys($data) as $player_id)
            {
                $flowers_left = 15 - count(array_filter($this->flowers, function ($v) use ($player_id) {
                    return $v['player_id'] == $player_id;
                }));
                $this->players[$player_id]['flowers_left'] = $flowers_left;
            }
        }

        return $this->players;
    }

    // Updated flower count on players
    private function players_removeAllFlowers() {
        $this->loadPlayersInfos();

        foreach ($this->players as $player_id => $player)
        {
            $this->players[$player_id]['flowers_left']= 15;
        }
    }

    // Updated flower count on players
    private function notifScores() {
        $this->loadPlayersInfos();

        $scores = array_map(function ($v) {
            return $v['player_score'];
        }, $this->players);

        self::notifyAllPlayers('playerScores', '', ['score' => $scores]);
    }
}
