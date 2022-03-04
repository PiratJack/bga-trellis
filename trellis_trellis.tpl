{OVERALL_GAME_HEADER}

<!--
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Trellis implementation : © Jacques de Metz <demetz.jacques@gmail.com>.
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->
<div id="trl_board">
    <div id="map_container">
        <div id="map_scrollable"></div>
        <div id="map_surface"></div>
        <div id="map_scrollable_oversurface"></div>

        <div class="movetop"></div>
        <div class="movedown"></div>
        <div class="moveleft"></div>
        <div class="moveright"></div>
    </div>

    <div id="trl_hand" class="whiteblock">
        <h3>{MY_TILES}</h3>
        <div id="trl_hand_tiles"></div>
    </div>

    <div id="map_footer" class="whiteblock">
        <a href="#" id="enlargedisplay">↓ {LABEL_ENLARGE_DISPLAY} ↓</a>
    </div>
</div>

<script type="text/javascript">
    const jstpl_tile = '<div id="${div_id}" class="trl_tile trl_tile_actual_tile" data-id="${id}" data-x="${x}" data-y="${y}" style="top: ${top}px; left: ${left}px; background-position: ${bg_x}% ${bg_y}%; transform: rotate(${angle}deg);" data-angle="${angle}"><div class="hexagon"></div></div>';

    const jstpl_possible_spot = '<div id="possible_spot_${x}_${y}" class="trl_tile trl_tile_possible_spot" data-x="${x}" data-y="${y}" style="top: ${top}px; left: ${left}px;"><div class="hexagon clickable"></div></div>';

    const jstpl_rotation_arrows = '<div id="trl_tile_rotate" data-x="${x}" data-y="${y}"><div id="trl_tile_rotate_counterclockwise" data-direction="-1" class="clickable">↶</div><div id="trl_tile_rotate_clockwise" data-direction="1" class="clickable">↷</div></div>';

    const jstpl_flower = '<div id="trl_flower_${flower_id}" class="trl_flower trl_flower_${player_color} trl_flower_angle_${angle}"></div>';

    const jstpl_flower_spot_container = '<div id="trl_flower_spot_container_${tile_id}" class="trl_tile trl_flower_spot_container" data-tile="${tile_id}" style="top: ${top}px; left: ${left}px;"><div class="hexagon"></div></div>';

    const jstpl_flower_spot = '<div id="trl_flower_spot_${tile_id}_${vine_color}" class="trl_flower trl_flower_spot trl_flower_angle_${angle} clickable" data-vine="${vine_color}" data-players="${players}"></div>';

    const jstpl_player_board = '<div class="trl_gift" id="trl_gift_${player_id}">${gift_points}</div>';
</script>

{OVERALL_GAME_FOOTER}