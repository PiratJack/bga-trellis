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

# ASSUMPTION: Each vine has vines on each sides
$this->tile_types = [
    [
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
    [
        'vines' => [
            'purple' => [270, 210],
            'yellow' => [90, 150],
            'orange'    => [330, 30],
        ],
        'sprite_position' => [
            'x' => 1,
            'y' => 0,
        ]
    ],
    [
        'vines' => [
            'pink'   => [210, 150],
            'yellow' => [270, 30],
            'green'  => [330, 90],
        ],
        'sprite_position' => [
            'x' => 2,
            'y' => 0,
        ]
    ],
    [
        'vines' => [
            'pink'   => [210, 150],
            'orange' => [270, 330],
            'blue'   => [30, 90],
        ],
        'sprite_position' => [
            'x' => 3,
            'y' => 0,
        ]
    ],
    [
        'vines' => [
            'purple' => [270, 210],
            'blue'   => [30, 330],
            'green'  => [150, 90],
        ],
        'sprite_position' => [
            'x' => 4,
            'y' => 0,
        ]
    ],
];

//REAL_ART start
for ($j = 0; $j < 14; $j++)
{
    for ($i = 0; $i < 4; $i++)
    {
        $this->tile_types[] = $this->tile_types[$i+1];
    }
}
//REAL_ART end

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
    150 => ['x' => -1, 'y' => 1], // Bottom left
    210 => ['x' => -1, 'y' => -1],  // Top left
    270 => ['x' => 0,  'y' => -2], // Top
    330 => ['x' => 1,  'y' => -1], // Top right
];

# Number of tiles horizontally & vertically in tiles sprite file
$this->sprite_size = [5, 1];
