<?php

function cjkb_add_to_menu()
{
    add_menu_page('Keyword Breaker', 'Keyword Breaker', 'manage_options',
    'keyword-breaker', 'cjkb_import_page', 'dashicons-admin-page');
}
function cjkb_import_page()
{
    if (!empty($_FILES)) {
        $valid_types = array("csv","txt");
        $csv_file =  $_FILES['csv_file']['tmp_name'];
        $ext = substr($_FILES['csv_file']['name'], 
			1 + strrpos($_FILES['csv_file']['name'], "."));
        $max_image_size = 3*1024*1024;

        if($_FILES['csv_file']['type']!== "application/vnd.ms-excel") {
             echo "Please, check your file headers";
            exit;
        }
        elseif(!in_array($ext, $valid_types) || filesize($csv_file) > $max_image_size) {
            echo "Please, check your file size and extention";
            exit;
        }
        else {
            $import_result = cjkb_import($csv_file);
        }
    }

    $out = '';
    $out.= '<div class="wrap"><h1>Keyword Breaker</h1>
        <div class="upload-csv">
            <p class="install-help">Import your csv file from Keyword Breaker</p>';
    echo ($import_result) ? $import_result : '';
    $out.= '<form method="post" enctype="multipart/form-data" class="wp-upload-form">
		'.wp_nonce_field('import_csv').'
                <input id="pluginzip" name="csv_file" type="file">
		<input name="import_csv" id="import_csv" class="button button-primary" value="Import" disabled="" type="submit">
            </form>
        </div> 
        </div>';
    echo $out;
}

function cjkb_import($csv_file)
{
    global $wpdb;
    $csv_arr = array();
    $handle = fopen($csv_file, "r");
    while (($line = fgetcsv($handle, 0, ",")) !== FALSE) {
        $csv_arr[] = $line;
    }
    fclose($handle);

    wp_defer_term_counting( true );
    wp_defer_comment_counting( true );
    $wpdb->query('SET autocommit = 0;');

    $import_counters = cjkb_import_posting($csv_arr);

    wp_defer_term_counting( false );
    wp_defer_comment_counting( false );
    $wpdb->query('SET autocommit = 1;');
    
    return $import_counters;
}

function cjkb_import_posting($csv_arr)
{   
    $succ_count = 0;
    global $wpdb;
    for($p=1;$p<count($csv_arr);$p++){
        

        $post_title = $csv_arr[$p][0];
        $kw = (!empty($csv_arr[$p][1])) ? $csv_arr[$p][1] : ' ' ;
        $ms = (!empty($csv_arr[$p][2])) ? $csv_arr[$p][2] : ' ' ;
        $com = (!empty($csv_arr[$p][3])) ? $csv_arr[$p][3] : 0;
        $as = (!empty($csv_arr[$p][4])) ? $csv_arr[$p][4] : ' ' ;
        
        $com_arr = cjkb_get_my_settings('competition');
        foreach($com_arr as $key=>$value) {
            if($value === $com) {
                $com_id = $key;
                break;
            }
            else {
                $com_id = 0;
            }
        }

        $md_hash = md5(implode($csv_arr[$p]));

        $new_post = array(
            'post_title'   => filter_var($post_title, FILTER_SANITIZE_SPECIAL_CHARS),
            'post_content' => '',
            'post_status'  => 'draft',
            'post_name' =>  '',
        );

        $is_post_exist = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key = 'md5' AND meta_value = '$md_hash'");

        if(!$is_post_exist) {

            $id = wp_insert_post($new_post);
            if (!is_wp_error($id) && $id > 0) {
                add_post_meta( $id, 'kw', filter_var($kw, FILTER_SANITIZE_SPECIAL_CHARS) );
                add_post_meta( $id, 'm_searches', filter_var($ms, FILTER_SANITIZE_NUMBER_INT) );
                add_post_meta( $id, 'competition', filter_var($com_id, FILTER_SANITIZE_NUMBER_INT) );
                add_post_meta( $id, 'aspects', filter_var($as, FILTER_SANITIZE_SPECIAL_CHARS) );
                add_post_meta( $id, 'md5', $md_hash );

                $succ_count++;
            }
        }
    }

    $skip_count = count($csv_arr) - 1 - $succ_count;

    $result = '<div id="message" class="updated notice is-dismissible">'
            . '<p>Imported '. $succ_count .' and ignored '. $skip_count .' items</p>'
            . '<button type="button" class="notice-dismiss">'
            . '<span class="screen-reader-text">Hide.</span></button></div>';

    return $result;
}
