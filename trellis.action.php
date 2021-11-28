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

class action_trellis extends APP_GameAction {
    // Constructor: please do not modify
    public function __default() {
        if (self::isArg('notifwindow'))
        {
            $this->view = "common_notifwindow";
            $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
        }
        else
        {
            $this->view = "trellis_trellis";
            self::trace("Complete reinitialization of board game");
        }
    }

    public function plant() {
        self::setAjaxMode();

        $tile_id = self::getArg("tile_id", AT_posint, true);
        $x = self::getArg("x", AT_int, true);
        $y = self::getArg("y", AT_int, true);
        $angle = self::getArg("angle", AT_enum, true, null, [0, 60, 120, 180, 240, 300]);

        $this->game->actPlant($tile_id, $x, $y, $angle);

        self::ajaxResponse();
    }

    public function plantChooseBloom() {
        self::setAjaxMode();

        $selectionAJAX = self::getArg('selection', AT_json, true);
        $selection = [];
        foreach ($selectionAJAX as $vine_color => $player_id)
        {
            if (!is_string($vine_color))
            {
                throw new \feException("Invalid value for bloom selection - vine color", true, true, FEX_bad_input_argument);
            }

            if (!is_numeric($player_id))
            {
                throw new \feException("Non-numeric value for bloom selection - player ID", true, true, FEX_bad_input_argument);
            }

            if ((int)$player_id <= 0)
            {
                throw new \feException("Negative value for bloom selection - player ID", true, true, FEX_bad_input_argument);
            }

            $selection[$vine_color] = (int)$player_id;
        }

        $this->game->actPlantChooseBloom($selection);

        self::ajaxResponse();
    }

    public function claim() {
        self::setAjaxMode();

        $x = self::getArg("x", AT_int, true);
        $y = self::getArg("y", AT_int, true);
        $position = self::getArg("position", AT_enum, true, null, ['top', 'topleft', 'topright', 'bottomleft', 'bottomright', 'bottom']);

        $this->game->actClaim($x, $y, $position);

        self::ajaxResponse();
    }
}
