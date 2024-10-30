<?php
$metabox_fields = cjkb_get_my_settings('adding_colums');

foreach($metabox_fields as $key => $value) {
    $new_meta_boxes[$key]['name'] = $key;
    $new_meta_boxes[$key]['title'] = __($value);
}

function cjkb_new_meta_boxes()
{
    global $post, $new_meta_boxes;

    foreach($new_meta_boxes as $meta_box) {

        $meta_box_value = get_post_meta($post->ID, $meta_box['name'], true);

        echo'<div class="meta-field">';
        echo'<input type="hidden" name="'.$meta_box['name'].'_noncename" id="'.$meta_box['name'].'_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
        echo'<p><strong>'.$meta_box['title'].'</strong></p>';

        if($meta_box['name'] == 'competition') {
            $competition_options = cjkb_get_my_settings('competition');
            echo '<select id="'.$meta_box['name'].'" name="'.$meta_box['name'].'">';
            foreach($competition_options as $key => $value) {
               $selected = ($key == $meta_box_value) ? 'selected="selected"' : '';
               echo'<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
            }
            echo '</select>';
        }
        elseif ($meta_box['name'] == 'aspects') {
            echo '<textarea name="'.$meta_box['name'].'" id="'.$meta_box['name'].'" rows="6" cols="30">'.$meta_box_value.'</textarea>';
        }
        else {
            echo'<input type="text" name="'.$meta_box['name'].'" id="'.$meta_box['name'].'" value="'.$meta_box_value.'" /><br />';
        }
        echo'</div>';
    }
    echo'<br style="clear:both" />';
}

function cjkb_create_meta_box()
{
    if ( function_exists('add_meta_box') ) {
        add_meta_box( 'ja-detail-boxes', __('Details','ja-import'), 'cjkb_new_meta_boxes', 'post', 'normal', 'high' );
    }
}

function cjkb_save_postdata( $post_id )
{
    global $post, $new_meta_boxes;

    foreach($new_meta_boxes as $meta_box) {

        $post_type = filter_input(INPUT_POST, 'post_type', FILTER_SANITIZE_STRING);
        $meta_box_noncename = filter_input(INPUT_POST, $meta_box['name'].'_noncename', FILTER_SANITIZE_STRING);
        // Verify
        if ( !wp_verify_nonce( $meta_box_noncename, plugin_basename(__FILE__) )) {
            return $post_id;
        }
        if ( 'page' == $post_type && !current_user_can( 'edit_page', $post_id )) {
            return $post_id;
        }

        $data = filter_input(INPUT_POST, $meta_box['name'], FILTER_SANITIZE_STRING);
        if(get_post_meta($post_id, $meta_box['name']) == "") {
                add_post_meta($post_id, $meta_box['name'], $data, true);
        }
        elseif($data != get_post_meta($post_id, $meta_box['name'], true)) {
                update_post_meta($post_id, $meta_box['name'], $data);
        }
        elseif(empty($data)) {
                delete_post_meta($post_id, $meta_box['name'], get_post_meta($post_id, $meta_box['name'], true));
        }
    }
}

add_action('admin_menu', 'cjkb_create_meta_box');
add_action('save_post', 'cjkb_save_postdata');

