-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- TrellisPiratJack implementation : © <Your name here> <Your email address here>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

CREATE TABLE IF NOT EXISTS `tiles` (
  `tile_id`        INT    UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique tile ID',
  `tile_type`      INT    UNSIGNED NOT NULL COMMENT 'corresponds to material file $tile_types key',
  `location`       VARCHAR(16)     NOT NULL COMMENT 'Either board, deck or player ID',
  `location_order` INT                      COMMENT 'Order in the deck',
  `x`              INT(3)                   COMMENT 'X position on board (0 is starting tile)',
  `y`              INT(3)                   COMMENT 'Y position on board (0 is starting tile)',
  `angle`          INT(3)                   COMMENT 'angle of the tile on the board',
  PRIMARY KEY (`tile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `flowers` (
  `flower_id` INT    UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique flower ID',
  `player_id` INT    UNSIGNED NOT NULL COMMENT 'Flower player ID',
  `x`         INT(3)                   COMMENT 'X position on board (0 is starting tile)',
  `y`         INT(3)                   COMMENT 'Y position on board (0 is starting tile)',
  `position_on_tile` VARCHAR(16)       COMMENT 'Must be top/bottom + left/right/nothing',
  PRIMARY KEY (`flower_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE `player` ADD `gift_points`  INT UNSIGNED NOT NULL DEFAULT '0';