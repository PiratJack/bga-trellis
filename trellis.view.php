<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * TrellisPiratJack implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

  require_once(APP_BASE_PATH."view/common/game.view.php");

  class view_trellis_trellis extends game_view {
      public function getGameName() {
          return "trellis";
      }
      public function build_page($viewArgs) {
          // Get players & players number
          $players = $this->game->loadPlayersBasicInfos();
          $players_nbr = count($players);

          $this->tpl['MY_TILES'] = self::_("My tiles");
      }
  }