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
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg_hexagon" style="display: none;">
    <path id="hexopath" d="M26 2, 75 2, 98 43, 75 84.6, 25 84.6, 2 43 z">
    </path>
    <clipPath id="hexoclip">
        <use href="#hexopath" fill="none" stroke="currentColor" clip-path="url(#hexoclip)" </use>
    </clipPath>
    <use id="hexo" class="hexo" href="#hexopath" fill="none" stroke="currentColor" clip-path="url(#hexoclip)">
    </use>
    <use class="hexo_filled" href="#hexopath" stroke-width="1" fill="currentColor">
    </use>
</svg>
<div id="trl_board">
    <div id="map_container">
        <div id="map_scrollable"></div>
        <div id="map_surface"></div>
        <div id="map_scrollable_oversurface"></div>
        <div id="trl_hand" class="whiteblock scrollmap_zoomed">
            <h3 style="text-align: left; margin-left: 10px; margin-top: 0px; font-size: calc(16px/var(--scrollmap_zoom));">{MY_TILES}</h3><!-- class="scrollmap_unzoomed" -->
            <div id="trl_hand_tiles"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    const jstpl_tile = '<div id="${div_id}" class="trl_tile trl_tile_actual_tile" data-id="${id}" data-x="${x}" data-y="${y}" style="top: ${top}px; left: ${left}px; background-position: ${bg_x}% ${bg_y}%; transform: rotate(${angle}deg);" data-angle="${angle}"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="hexagon" viewBox="2 2 96 82.6"><use href="#hexo"></use></svg></div>';

    const jstpl_possible_spot = '<div id="possible_spot_${x}_${y}" class="trl_tile trl_tile_possible_spot" data-x="${x}" data-y="${y}" style="top: ${top}px; left: ${left}px;"><div class="hexagon clickable"></div></div>';

    const jstpl_rotation_arrows = '<div id="trl_tile_rotate" data-x="${x}" data-y="${y}"><div id="trl_tile_rotate_counterclockwise" data-direction="-1" class="clickable">↶</div><div id="trl_tile_rotate_clockwise" data-direction="1" class="clickable">↷</div></div>';

    const jstpl_flower = '<div id="trl_flower_${flower_id}" class="trl_flower trl_flower_${player_color} trl_flower_angle_${angle}"></div>';

    const jstpl_flower_spot_container = '<div id="trl_flower_spot_container_${tile_id}" class="trl_tile trl_flower_spot_container" data-tile="${tile_id}" style="top: ${top}px; left: ${left}px;"><div class="hexagon"></div></div>';

    const jstpl_flower_spot = '<div id="trl_flower_spot_${tile_id}_${vine_color}" class="trl_flower trl_flower_spot trl_flower_angle_${angle} clickable" data-vine="${vine_color}" data-players="${players}"></div>';

    const jstpl_player_board = '<span id="trl_gift_${player_id}">${gift_points}</span><div class="trl_gift"></div><span id="trl_flowers_left_${player_id}">${flowers_left}</span><div class="trl_flower_${player_color} trl_flowers_left"></div>';
</script>

{OVERALL_GAME_FOOTER}