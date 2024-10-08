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


require_once(APP_GAMEMODULE_PATH.'module/table/table.game.php');
require_once('modules/states.php');
require_once('modules/players.php');
require_once('modules/tiles.php');
require_once('modules/flowers.php');


class Trellis extends Table {
    use Trellis\StatesTrait;
    use Trellis\PlayersTrait;
    use Trellis\TilesTrait;
    use Trellis\FlowersTrait;

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

        self::initStat('table', 'who_won', 0);

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    // Returns all relevant game data at once
    protected function getAllDatas() {
        $result = [];

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

        return (15-min($flowers_left)) / 15 * 100;
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////

    // Returns whether any of the player won
    public function checkPlayerWon() {
        $this->loadFlowers();
        $this->loadPlayersInfos();
        $winner = false;
        foreach ($this->players as $player_id => $player) {
            $flowers = array_filter($this->flowers, function ($v) use ($player_id) {
                return $v['player_id'] == $player_id;
            });
            if (count($flowers) >= 15) {
                $winner = $player_id;
            }
        }

        if ($winner) {
            // Statistic: first_player_won
            $winner_order = $this->players[$winner]['player_no'];
            $nb_players = count($this->players);
            $stat_value = $nb_players * 10 + $winner_order;
            self::setStat($stat_value, 'who_won');
        }

        return $winner;
    }

    public function getRandomValue($array) {
        shuffle($array);
        return reset($array);
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    // Zombie turn: just play randomly
    public function zombieTurn($state, $active_player) {
        if ($state['type'] === "activeplayer") {
            switch ($state['name']) {
                case 'plant':
                    // Select a tile
                    $possible_tiles = $this->getTiles(['location' => $active_player]);
                    $selected_tile = $this->getRandomValue($possible_tiles);

                    // Select a spot
                    $possible_spots = $this->argPlant()['possibleTileSpots'];
                    $selected_spot = $this->getRandomValue($possible_spots);

                    // Select an angle
                    $angles = range(0, 300, 60);
                    $selected_angle = $this->getRandomValue($angles);

                    // Actually plant
                    $this->plantTile($selected_tile, $selected_spot['x'], $selected_spot['y'], $selected_angle);

                    $this->gamestate->nextState("");
                    break;

                case 'plantChooseBloom':
                    $possible_blooms = $this->argPlantChooseBloom()['_private']['active']['possibleBlooms'];
                    foreach ($possible_blooms as $tile_id => $vines) {
                        foreach ($vines as $vine_color => $vine) {
                            $selected_player = $this->getRandomValue($vine['players']);
                            $this->bloomFlower(['player_id' => $selected_player, 'tile_id' => $tile_id, 'vine' => $vine_color]);
                        }
                    }

                    $this->transitionIfPlayerWon('endGame', 'continueGame');
                    break;

                case 'claim':
                    $possible_claims = $this->argClaim()['_private']['active']['possibleFlowerSpots'];

                    foreach ($possible_claims as $tile_id => $tile) {
                        $selected_vine = $this->getRandomValue(array_keys($tile));
                        $this->claimVine(['player_id' => $active_player, 'tile_id' => $tile_id, 'vine' => $selected_vine]);
                    }

                    $this->transitionIfPlayerWon('endGame', 'continueGame');
                    break;

                case 'claimGift':
                    $gift_info = $this->argClaimGift();
                    $gift_points = $gift_info['gift_points'];
                    $possible_flower_spots = $gift_info['_private']['active']['possibleFlowerSpots'];
                    $main_tile = $gift_info['_private']['active']['mainTile'];

                    $vines_claimed = [];
                    for ($i = 0; $i < $gift_points; $i++) {
                        // Force to select the tile that was just placed
                        if (array_key_exists($main_tile, $possible_flower_spots)) {
                            $tile_id = $main_tile;
                        } else {
                            $tile_id = $this->getRandomValue(array_keys($possible_flower_spots));
                        }

                        // Select a random vine and claim it
                        $selected_vine_color = $this->getRandomValue(array_keys($possible_flower_spots[$tile_id]));
                        $vines_claimed[] = ['player_id' => $active_player, 'tile_id' => $tile_id, 'vine' => $selected_vine_color];
                        unset($possible_flower_spots[$tile_id][$selected_vine_color]);
                        if (count($possible_flower_spots[$tile_id]) == 0) {
                            unset($possible_flower_spots[$tile_id]);
                        }
                    }
                    $this->claimVines($vines_claimed, $active_player);

                    $this->transitionIfPlayerWon('endGame', 'continueGame');
                    break;
            }

            return;
        }

        throw new BgaUserException(str_replace('${state_name}', $state['name'], self::_('Zombie mode not supported at this game state: ${state_name}')));
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    public function upgradeTableDb($from_version) {
        // Added display of last tile placed
        if ($from_version <= 2203131458) {
            $sql = 'ALTER TABLE DBPREFIX_player ADD `last_tile_placed`  INT UNSIGNED DEFAULT NULL';
            self::applyDbUpgradeToAllDB($sql);
        }
        // Added pre-planting of tiles
        if ($from_version <= 2304301628) {
            $sql = 'ALTER TABLE DBPREFIX_player ADD `pre_planted_tile`  VARCHAR(50) DEFAULT NULL';
            self::applyDbUpgradeToAllDB($sql);
        }
    }
}
