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

# Colors: blue, purple, yellow, orange, pink, green

# Structure is:
# tile_type_id (0 for starting tile) => [
#       vines => [ color => position of vine's extremities],
#       sprite_position => [ x, y position in the sprite image file]
# ]
# sprite_position starts at 0!!

# angles are taken so that:
# - direction in the right is 0
# - angles are counted clockwise (as per CSS conventions)

# ASSUMPTION: Each vine has 2 ends on the same tile (which is true)
$this->tile_types = [
    [
        # This is the starting tile
        'vines' => [
            'purple' => [30],
            'orange' => [90],
            'green'  => [150],
            'blue'   => [210],
            'pink'   => [270],
            'yellow' => [330],
        ],
        'sprite_position' => [
            'x' => 0,
            'y' => 0,
        ]
    ],
    ['vines' =>['pink'=>[30, 150],  'orange'=>[90, 270], 'yellow'=>[210, 330],],'sprite_position' =>['x' =>1, 'y' => 0]],
    ['vines' =>['yellow'=>[30, 150],'green'=>[90, 270],  'orange'=>[210, 330],],'sprite_position' =>['x' =>2, 'y' => 0]],
    ['vines' =>['orange'=>[30, 150],'purple'=>[90, 270], 'yellow'=>[210, 330],],'sprite_position' =>['x' =>3, 'y' => 0]],
    ['vines' =>['orange'=>[30, 150],'pink'=>[90, 270],   'blue'=>[210, 330],],  'sprite_position' =>['x' =>4, 'y' => 0]],
    ['vines' =>['blue'=>[30, 150],  'purple'=>[90, 270], 'orange'=>[210, 330],],'sprite_position' =>['x' =>5, 'y' => 0]],
    ['vines' =>['pink'=>[30, 150],  'green'=>[90, 270],  'orange'=>[210, 330],],'sprite_position' =>['x' =>6, 'y' => 0]],
    ['vines' =>['purple'=>[30, 150],'orange'=>[90, 270], 'green'=>[210, 330],], 'sprite_position' =>['x' =>7, 'y' => 0]],
    ['vines' =>['pink'=>[30, 150],  'yellow'=>[90, 270], 'blue'=>[210, 330],],  'sprite_position' =>['x' =>0, 'y' => 1]],
    ['vines' =>['purple'=>[30, 150],'yellow'=>[90, 270], 'blue'=>[210, 330],],  'sprite_position' =>['x' =>1, 'y' => 1]],
    ['vines' =>['pink'=>[30, 150],  'yellow'=>[90, 270], 'green'=>[210, 330],], 'sprite_position' =>['x' =>2, 'y' => 1]],
    ['vines' =>['pink'=>[30, 150],  'blue'=>[90, 270],   'green'=>[210, 330],], 'sprite_position' =>['x' =>3, 'y' => 1]],
    ['vines' =>['blue'=>[30, 150],  'orange'=>[90, 270], 'yellow'=>[210, 330],],'sprite_position' =>['x' =>4, 'y' => 1]],
    ['vines' =>['purple'=>[30, 150],'blue'=>[90, 270],   'pink'=>[210, 330],],  'sprite_position' =>['x' =>5, 'y' => 1]],
    ['vines' =>['green'=>[30, 150], 'blue'=>[90, 270],   'purple'=>[210, 330],],'sprite_position' =>['x' =>6, 'y' => 1]],
    ['vines' =>['purple'=>[30, 150],'pink'=>[90, 270],   'green'=>[210, 330],], 'sprite_position' =>['x' =>7, 'y' => 1]],
    ['vines' =>['yellow'=>[30, 270],'orange'=>[90, 330], 'blue'=>[150, 210],],  'sprite_position' =>['x' =>0, 'y' => 2]],
    ['vines' =>['orange'=>[30, 270],'yellow'=>[90, 330], 'pink'=>[150, 210],],  'sprite_position' =>['x' =>1, 'y' => 2]],
    ['vines' =>['blue'=>[30, 270],  'orange'=>[90, 330], 'pink'=>[150, 210],],  'sprite_position' =>['x' =>2, 'y' => 2]],
    ['vines' =>['blue'=>[30, 270],  'green'=>[90, 330],  'orange'=>[150, 210],],'sprite_position' =>['x' =>3, 'y' => 2]],
    ['vines' =>['orange'=>[30, 270],'blue'=>[90, 330],   'purple'=>[150, 210],],'sprite_position' =>['x' =>4, 'y' => 2]],
    ['vines' =>['pink'=>[30, 270],  'orange'=>[90, 330], 'green'=>[150, 210],], 'sprite_position' =>['x' =>5, 'y' => 2]],
    ['vines' =>['pink'=>[30, 270],  'purple'=>[90, 330], 'orange'=>[150, 210],],'sprite_position' =>['x' =>6, 'y' => 2]],
    ['vines' =>['green'=>[30, 270], 'purple'=>[90, 330], 'orange'=>[150, 210],],'sprite_position' =>['x' =>7, 'y' => 2]],
    ['vines' =>['yellow'=>[30, 270],'blue'=>[90, 330],   'green'=>[150, 210],], 'sprite_position' =>['x' =>0, 'y' => 3]],
    ['vines' =>['blue'=>[30, 270],  'purple'=>[90, 330], 'yellow'=>[150, 210],],'sprite_position' =>['x' =>1, 'y' => 3]],
    ['vines' =>['yellow'=>[30, 270],'green'=>[90, 330],  'pink'=>[150, 210],],  'sprite_position' =>['x' =>2, 'y' => 3]],
    ['vines' =>['pink'=>[30, 270],  'purple'=>[90, 330], 'yellow'=>[150, 210],],'sprite_position' =>['x' =>3, 'y' => 3]],
    ['vines' =>['green'=>[30, 270], 'purple'=>[90, 330], 'yellow'=>[150, 210],],'sprite_position' =>['x' =>4, 'y' => 3]],
    ['vines' =>['pink'=>[30, 270],  'green'=>[90, 330],  'blue'=>[150, 210],],  'sprite_position' =>['x' =>5, 'y' => 3]],
    ['vines' =>['green'=>[30, 270], 'purple'=>[90, 330], 'pink'=>[150, 210],],  'sprite_position' =>['x' =>6, 'y' => 3]],
    ['vines' =>['blue'=>[30, 330],  'orange'=>[90, 270], 'yellow'=>[150, 210],],'sprite_position' =>['x' =>7, 'y' => 3]],
    ['vines' =>['yellow'=>[30, 330],'pink'=>[90, 270],   'orange'=>[150, 210],],'sprite_position' =>['x' =>0, 'y' => 4]],
    ['vines' =>['yellow'=>[30, 330],'purple'=>[90, 270], 'orange'=>[150, 210],],'sprite_position' =>['x' =>1, 'y' => 4]],
    ['vines' =>['orange'=>[30, 330],'pink'=>[90, 270],   'blue'=>[150, 210],],  'sprite_position' =>['x' =>2, 'y' => 4]],
    ['vines' =>['orange'=>[30, 330],'purple'=>[90, 270], 'blue'=>[150, 210],],  'sprite_position' =>['x' =>3, 'y' => 4]],
    ['vines' =>['green'=>[30, 330], 'orange'=>[90, 270], 'pink'=>[150, 210],],  'sprite_position' =>['x' =>4, 'y' => 4]],
    ['vines' =>['orange'=>[30, 330],'green'=>[90, 270],  'purple'=>[150, 210],],'sprite_position' =>['x' =>5, 'y' => 4]],
    ['vines' =>['yellow'=>[30, 330],'green'=>[90, 270],  'blue'=>[150, 210],],  'sprite_position' =>['x' =>6, 'y' => 4]],
    ['vines' =>['yellow'=>[30, 330],'purple'=>[90, 270], 'blue'=>[150, 210],],  'sprite_position' =>['x' =>7, 'y' => 4]],
    ['vines' =>['green'=>[30, 330], 'yellow'=>[90, 270], 'pink'=>[150, 210],],  'sprite_position' =>['x' =>0, 'y' => 5]],
    ['vines' =>['purple'=>[30, 330],'yellow'=>[90, 270], 'pink'=>[150, 210],],  'sprite_position' =>['x' =>1, 'y' => 5]],
    ['vines' =>['purple'=>[30, 330],'green'=>[90, 270],  'yellow'=>[150, 210],],'sprite_position' =>['x' =>2, 'y' => 5]],
    ['vines' =>['green'=>[30, 330], 'blue'=>[90, 270],   'pink'=>[150, 210],],  'sprite_position' =>['x' =>3, 'y' => 5]],
    ['vines' =>['green'=>[30, 330], 'blue'=>[90, 270],   'purple'=>[150, 210],],'sprite_position' =>['x' =>4, 'y' => 5]],
    ['vines' =>['green'=>[30, 330], 'pink'=>[90, 270],   'purple'=>[150, 210],],'sprite_position' =>['x' =>5, 'y' => 5]],
    ['vines' =>['pink'=>[30, 90],   'orange'=>[150, 210],'yellow'=>[270, 330],],'sprite_position' =>['x' =>6, 'y' => 5]],
    ['vines' =>['green'=>[30, 90],  'orange'=>[150, 210],'yellow'=>[270, 330],],'sprite_position' =>['x' =>7, 'y' => 5]],
    ['vines' =>['purple'=>[30, 90], 'orange'=>[150, 210],'yellow'=>[270, 330],],'sprite_position' =>['x' =>0, 'y' => 6]],
    ['vines' =>['pink'=>[30, 90],   'orange'=>[150, 210],'blue'=>[270, 330],],  'sprite_position' =>['x' =>1, 'y' => 6]],
    ['vines' =>['green'=>[30, 90],  'orange'=>[150, 210],'blue'=>[270, 330],],  'sprite_position' =>['x' =>2, 'y' => 6]],
    ['vines' =>['purple'=>[30, 90], 'orange'=>[150, 210],'blue'=>[270, 330],],  'sprite_position' =>['x' =>3, 'y' => 6]],
    ['vines' =>['purple'=>[30, 90], 'orange'=>[150, 210],'green'=>[270, 330],], 'sprite_position' =>['x' =>4, 'y' => 6]],
    ['vines' =>['pink'=>[30, 90],   'yellow'=>[150, 210],'blue'=>[270, 330],],  'sprite_position' =>['x' =>5, 'y' => 6]],
    ['vines' =>['purple'=>[30, 90], 'yellow'=>[150, 210],'blue'=>[270, 330],],  'sprite_position' =>['x' =>6, 'y' => 6]],
    ['vines' =>['green'=>[30, 90],  'yellow'=>[150, 210],'pink'=>[270, 330],],  'sprite_position' =>['x' =>7, 'y' => 6]],
    ['vines' =>['purple'=>[30, 90], 'yellow'=>[150, 210],'pink'=>[270, 330],],  'sprite_position' =>['x' =>0, 'y' => 7]],
    ['vines' =>['purple'=>[30, 90], 'yellow'=>[150, 210],'green'=>[270, 330],], 'sprite_position' =>['x' =>1, 'y' => 7]],
    ['vines' =>['green'=>[30, 90],  'blue'=>[150, 210],  'pink'=>[270, 330],],  'sprite_position' =>['x' =>2, 'y' => 7]],
    ['vines' =>['purple'=>[30, 90], 'blue'=>[150, 210],  'pink'=>[270, 330],],  'sprite_position' =>['x' =>3, 'y' => 7]],
    ['vines' =>['purple'=>[30, 90], 'blue'=>[150, 210],  'green'=>[270, 330],], 'sprite_position' =>['x' =>4, 'y' => 7]],


];

$this->color_translated = [
    'purple' => clienttranslate('purple'),
    'orange' => clienttranslate('orange'),
    'green'  => clienttranslate('green'),
    'blue'   => clienttranslate('blue'),
    'pink'   => clienttranslate('pink'),
    'yellow' => clienttranslate('yellow'),
];

// x goes up from left to right
// y goes up from top to bottom
$this->directions = [
    30  => ['x' => 1,  'y' => 1],  // Bottom right
    90  => ['x' => 0,  'y' => 2],  // Bottom
    150 => ['x' => -1, 'y' => 1],  // Bottom left
    210 => ['x' => -1, 'y' => -1], // Top left
    270 => ['x' => 0,  'y' => -2], // Top
    330 => ['x' => 1,  'y' => -1], // Top right
];
