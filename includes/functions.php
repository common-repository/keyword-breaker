<?php
// Add new columns
function cjkb_ja_add_post_columns($defaults)
{
    //remove standart columns
    $removing_colums = cjkb_get_my_settings('removing_colums');
    $new_colums = array_diff_key($defaults, array_flip($removing_colums));

    $adding_date = $new_colums['date'];
    unset($new_colums['date']);

    //add new colums with displayed titles
    $adding_colums = cjkb_get_my_settings('adding_colums');
    foreach ($adding_colums as $key=>$value) {
        $new_colums[$key] = __($value);
    }
    $new_colums['date'] = $adding_date;

    return $new_colums;
}

// Populate the new columns with values
function cjkb_ja_get_post_column_values($column_name, $postID)
{
    $custom_colums = array_flip(cjkb_get_my_settings('adding_colums'));
    $competition = cjkb_get_my_settings('competition');

    foreach($custom_colums as $column) {
        if($column_name === $column && $column_name !== 'competition') {
            echo get_post_meta($postID, $column, true);
        }
        elseif ($column_name === 'competition') {
            $competition_id = get_post_meta($postID, $column, true);
            echo $competition[$competition_id];
        }
    }
}

function cjkb_custom_columns_sortable($columns)
{

    // Add our columns to $columns array
    $columns['competition'] = 'competition';
    $columns['m_searches'] = 'm_searches';
    $columns['kw'] = 'kw';
    return $columns;
}

function cjkb_add_column_views_request( $object )
{
	if($object->get('orderby') == 'm_searches') {
            
            $object->set('meta_key', 'm_searches');
            $object->set('orderby', 'meta_value_num');
        }
        elseif($object->get('orderby') == 'kw') {
            $object->set('meta_key', 'kw');
            $object->set('orderby', 'meta_value');
        }
}

function cjkb_ja_filter_form()
{
    $post_type = filter_input(INPUT_GET, 'post_type', FILTER_SANITIZE_STRING);
    $type = ($post_type) ? $post_type : 'post';
    $competition = cjkb_get_my_settings('competition');
    $out = '';

    if ($type =='post' && $post_status!=='draft') {
        $out.='<select name="comp_from">';
        $out.='<option value="0">Competition From</option>';
        $comp_from = filter_input(INPUT_GET, 'comp_from', FILTER_SANITIZE_NUMBER_INT);
        $current_f = ($comp_from) ? $comp_from : 0;
        foreach ($competition as $value => $label) {
            $selected = ($value == $current_f) ? 'selected="selected"' : '';
            $out.='<option value="'.$value.'" '.$selected.'>'.$label.'</option>';
        }
        $out.='</select>';

        $out.='<select name="comp_to">';
        $out.='<option value="0">Competition To</option>';
        $comp_to = filter_input(INPUT_GET, 'comp_to', FILTER_SANITIZE_NUMBER_INT);
        $current_t = ($comp_to) ? $comp_to : 0;
        foreach ($competition as $value => $label) {
            $selected = ($value == $current_t) ? 'selected="selected"' : '';
            $out.='<option value="'.$value.'" '.$selected.'>'.$label.'</option>';
        }
        $out.='</select>';
        
        $m_searches = filter_input(INPUT_GET, 'm_searches', FILTER_SANITIZE_NUMBER_INT);
        $out.='<input name="m_searches" value="'.$m_searches.'" placeholder="Minimum Monthly Searches" type="search">';
    }
    echo $out;
}

function cjkb_ja_posts_filter( $query )
{
    global $pagenow;
    $post_type = filter_input(INPUT_GET, 'post_type', FILTER_SANITIZE_STRING);
    $type = ($post_type) ? $post_type : 'post';
    $m_searches = '';
    $comp_from = '';
    $comp_to = 7;

    if(isset($_GET['m_searches']) && $_GET['m_searches']!=='') {
        $m_searches = filter_input(INPUT_GET, 'm_searches', FILTER_SANITIZE_NUMBER_INT);
    }
    
    if(isset($_GET['comp_from']) && $_GET['comp_from'] != '') {
        $comp_from = filter_input(INPUT_GET, 'comp_from', FILTER_SANITIZE_NUMBER_INT);
    }
    
    if(isset($_GET['comp_to']) && $_GET['comp_to'] != '' && $_GET['comp_to'] != '0') {
        $comp_to = filter_input(INPUT_GET, 'comp_to', FILTER_SANITIZE_NUMBER_INT);
    }

    if ($type=='post' && is_admin() && $pagenow=='edit.php') {
        $meta_query_args = array(
            'relation' => 'AND',
            array(
                'relation' => 'OR',
                array(
                        'key'     => 'competition',
                        'value'   => array($comp_from, $comp_to),
                        'compare' => 'BETWEEN',
                        'type'    => 'NUMERIC',
                    ),
                array(
                        'key'     => 'competition',
                        'value'   => array($comp_to, $comp_from),
                        'compare' => 'BETWEEN',
                        'type'    => 'NUMERIC',
                    ),
            ),
            array(
                    'key'     => 'm_searches',
                    'value'   => (int)$m_searches,
                    'compare' => '>=',
                    'type'    => 'NUMERIC',
                ),
        );
        $query->query_vars['meta_query'] = $meta_query_args;
    }
}

// add_action('user_register', 'set_user_metaboxes');
function cjkb_set_user_metaboxes($user_id=NULL)
{
    // These are the metakeys we will need to update
    $meta_key['order'] = 'meta-box-order_post';
    $meta_key['hidden'] = 'metaboxhidden_post';

    // So this can be used without hooking into user_register
    if ( ! $user_id)
        $user_id = get_current_user_id();

    // Set the default order if it has not been set yet
    if (!get_user_meta( $user_id, $meta_key['order'], true) ) {
        $meta_value = array(
            'side' => 'ja-detail-boxes,submitdiv',
            'normal' => '',
            'advanced' => '',
        );
        update_user_meta( $user_id, $meta_key['order'], $meta_value );
    }

    // Set the default hiddens if it has not been set yet
    if (!get_user_meta( $user_id, $meta_key['hidden'], true) ) {
        $meta_value = array('postcustom','tagsdiv-post_tag','postexcerpt','formatdiv','trackbacksdiv','commentstatusdiv','commentsdiv','slugdiv','authordiv','revisionsdiv','postimagediv','formatdiv','categorydiv');
        update_user_meta( $user_id, $meta_key['hidden'], $meta_value );
    }
}

function cjkb_add_views_column_css()
{
    if( get_current_screen()->id == 'edit-post') {
        echo '<style type="text/css">'
        . '.column-title{width:580px;}'
        . 'input[name="m_searches"]{width:210px;}'
        . '</style>';
    }
}

