<?php
/**
 * @package CodeJa_Import
 * @version 1.0.0
 */
/*
Plugin Name: Keyword Breaker
Plugin URI: https://wordpress.org/plugins/keyword-breaker/
Description: Import the Keyword Breaker data for your niche into your WordPress admin dashboard. This plugin will save the Keyword Breaker questions and keyword research as drafts ready to be written and published on your site.
Version: 1.0.2
Author: CODEJA
Author URI: http://www.codeja.net
License:     GPL2

Keyword Breaker is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Keyword Breaker is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Keyword Breaker. If not, see {URI to Plugin License}.
*/

define('CJKB_PLUGIN', __FILE__);
define('CJKB_PLUGIN_BASENAME', plugin_basename(CJKB_PLUGIN));
define('CJKB_PLUGIN_NAME', trim(dirname(CJKB_PLUGIN_BASENAME), '/'));
define('CJKB_PLUGIN_DIR', untrailingslashit(dirname(CJKB_PLUGIN)));

require_once CJKB_PLUGIN_DIR.'/settings.php';
require_once CJKB_PLUGIN_DIR.'/includes/functions.php';
require_once CJKB_PLUGIN_DIR.'/admin/admin.php';
require_once CJKB_PLUGIN_DIR.'/admin/metabox.php';

add_action('admin_menu', 'cjkb_add_to_menu');
add_filter('manage_posts_columns', 'cjkb_ja_add_post_columns', 5);
add_action('manage_posts_custom_column', 'cjkb_ja_get_post_column_values', 5, 2);
add_filter('manage_edit-post_sortable_columns', 'cjkb_custom_columns_sortable');
add_filter('pre_get_posts', 'cjkb_add_column_views_request');
add_action('restrict_manage_posts', 'cjkb_ja_filter_form');
add_action('admin_init', 'cjkb_set_user_metaboxes');
add_action('admin_head', 'cjkb_add_views_column_css');

if($_GET['m_searches'] || $_GET['comp_from'] || $_GET['comp_to']) {
    add_filter('parse_query', 'cjkb_ja_posts_filter');
}