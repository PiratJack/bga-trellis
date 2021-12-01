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


require_once(APP_GAMEMODULE_PATH.'module/table/table.game.php');
require_once('modules/states.php');
require_once('modules/players.php');
require_once('modules/tiles.php');
require_once('modules/flowers.php');


class TrellisPiratJack extends Table {
    use TrellisPiratJack\StatesTrait;
    use TrellisPiratJack\PlayersTrait;
    use TrellisPiratJack\TilesTrait;
    use TrellisPiratJack\FlowersTrait;

    public function __construct() {
        parent::__construct();

        self::initGameStateLabels([
            'last_tile_planted' => 10,
            'last_flower_claimed' => 11,
        ]);
    }

    protected function getGameName() {
        return "trellis";
    }

    // Sets up a new game
    protected function setupNewGame($players, $options = []) {
        $this->players_setupNewGame($players, $options);
        $this->tiles_setupNewGame($players, $options);
        $this->flowers_setupNewGame($players, $options);

        self::reloadPlayersBasicInfos();

        self::setGameStateInitialValue('last_tile_planted', 0);

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    // Returns all relevant game data at once
    protected function getAllDatas() {
        $result = [];

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        $result['players'] = $this->players_getAllDatas();
        $result['tiles'] = $this->tiles_getAllDatas();
        $result['flowers'] = $this->flowers_getAllDatas();

        return $result;
    }

    // Returns % of completion - basically highest score / target score
    public function getGameProgression() {
        $this->loadPlayersInfos();

        $flowers_left = array_map(function ($player) {
            return $player['flowers_left'];
        }, $this->players);

        return (15-min($flowers_left)) * 100;
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////

    // Returns whether any of the player won
    public function checkPlayerWon() {
        $this->loadFlowers();
        $this->loadPlayersInfos();
        $winner = 0;
        foreach ($this->players as $player_id => $player)
        {
            $flowers = array_filter($this->flowers, function ($v) use ($player_id) {
                return $v['player_id'] == $player_id;
            });
            if (count($flowers) >= 15)
            {
                $winner = $player_id;
            }
        }

        return ($winner != 0);
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    // Zombie turn: just play randomly
    public function zombieTurn($state, $active_player) {
        $statename = $state['name'];

        if ($state['type'] === "activeplayer")
        {
            switch ($statename) {
                default:
                    $this->gamestate->nextState("zombiePass");
                    break;
            }

            return;
        }

        throw new BgaUserException("Zombie mode not supported at this game state: ".$statename);
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    public function upgradeTableDb($from_version) {
    }
}
