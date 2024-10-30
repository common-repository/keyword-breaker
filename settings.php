<?php

function cjkb_get_my_settings($setting)
{
    $removing_colums = array('author','categories','tags','comments');
    $adding_colums = array(
        'kw'            => 'KW',
        'm_searches'    => 'Monthly Searches',
        'competition'   => 'Competition',
    );
    $competition = array(
        1 => 'Extremely Low',
        2 => 'Very Low',
        3 => 'Low',
        4 => 'Medium',
        5 => 'High',
        6 => 'Very High',
        7 => 'Extremely High',
        0 => ' ',
    );
    
    $settings = array(
        'removing_colums'   => $removing_colums,
        'adding_colums'     => $adding_colums,
        'competition'       => $competition,
    );

    return $settings[$setting];
}
