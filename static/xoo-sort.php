<?php

/**
* Plugin Name: Xoo Sort
* Plugin URI: http://xoocode.com/xoo-sort?utm_source=plugin
* Description: Sorts the My Sites listings alphabetically.
* Author: Peter Valenta
* Author URI: http://xoocode.com/?utm_source=plugin
* Version: 1.0.0
* License: GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: xoo-xse
* Domain Path: /languages
*/

// If this file is called directly or plugin is already defined, abort.
if (!defined('WPINC')) die;

include 'xoo-common.php';

define('XSE_VERSION', '1.0.0');
define('XSE_FILE_PATH', dirname(__FILE__));
define('XSE_DIR_NAME', basename(XSE_FILE_PATH));
define('XSE_FOLDER', dirname(plugin_basename(__FILE__)));
define('XSE_NAME', plugin_basename(__FILE__));
define('XSE_DIR', WP_CONTENT_DIR . '/plugins/' . XSE_FOLDER);

add_action('wp_before_admin_bar_render', 'xse_test' ) ;
function xse_test()
	{
		global $wp_admin_bar;
		
		xoo_log('1.......');
			foreach($wp_admin_bar->get_nodes() as $node => $menu) {
				$xse_menus = [];
				if($menu->parent == 'new-content') {
					$xse_menus[] = $menu;
					$wp_admin_bar->remove_menu($menu->id);
					
					xoo_log('$nodes_')	;		
					xoo_log($nodes);
					xoo_log('$node_');				
					xoo_log($node);
				}
			}
		
			usort($xse_menus, function ($a, $b) {
				return strcasecmp($a->title, $b->title);
			});
			
			foreach($xse_menus as $menu) {
				 $wp_admin_bar->add_node($menu);
			}			
			
			//$wp_admin_bar->remove_menu( 'comments' );

			//$wp_admin_bar->remove_menu( 'my-account' );

			//$wp_admin_bar->remove_menu( 'updates' );

			//$wp_admin_bar->remove_menu( 'wp-logo' );

		    //$wp_admin_bar->remove_menu( 'new-content' );


			
			//$wp_admin_bar->remove_menu( 'theme_options' );

			//$wp_admin_bar->remove_menu( 'site-name' );

			//$wp_admin_bar->remove_menu( 'wpseo-menu' );
	}



/**
 * xse main admin page.
 */
function xse_page() {
	if (!current_user_can('manage_options')) {
		wp_die(__('You do not have sufficient privilages to check for broken links. You need manage_options rights, talk to your administrator.'));
	}
	
	global $submenu;
	global $menu;
	
	$options = xse_load();
	$checked = [];
	foreach($options as $option => $value) {
		if(filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
			$checked[$option] = ' checked';
		} else {
			$checked[$option] = '';
		}
	} 		
	?>	
	<div class="wrap">
		<h1>Xoo Sort</h1>
			<div class="xoo-options-expl">
				<p><?php echo _e('Xoo Sort will sort menus in the Wordpress Admin Interface alphabetically. Use the sliders below to activate and deactive sorting. Settings are changed instantly when you use a slider. You will however always have to reload this page or load another page before you can see the results of your changes.','xoo-xse'); ?></p>
			</div>
			<div class="xoo-select-wrapper">
		<div class="xoo-list-toggles">
		<h2><?php echo _e('Admin bar','xoo-xse'); ?></h2>
			<div class="xoo-toggle-wrap">
				<label class="xoo-switch">
				<input type="checkbox" id="sort_my_sites" class="xse-trigger"<?php echo esc_attr($checked['sort_my_sites']); ?>>
					<span class="xoo-toggle"></span>
				</label><span class="xoo-label"><?php echo _e("My sites",'xoo-xse'); ?></span>
			</div>	
		</div>
		<div class="xoo-list-toggles">
		<h2><?php echo _e('WordPress Menu','xoo-xse'); ?></h2>
			<?php foreach($menu as $menu_item) { 
				if(strpos($menu_item[4], 'wp-menu-separator') === false) { ?>
				<div class="xoo-toggle-wrap">
				<?php if(isset($menu_item[6])) {
					if(strpos($menu_item[6], 'dashicons-') === 0) {
						?><span class="xse-menu-icon dashicons <?php echo esc_attr($menu_item[6]); ?>"></span><?php
					} else {
					  ?><img src="<?php echo esc_url($menu_item[6]); ?>" class="xse-menu-icon" /><?php
					}
				}
					?>
					<label class="xoo-switch">
					<input type="checkbox" id="<?php echo esc_attr($menu_item[2]); ?>" class="xse-trigger"<?php if(isset($checked[$menu_item[2]])) echo esc_attr($checked[$menu_item[2]]); ?>>
						<span class="xoo-toggle"></span>
					</label><span class="xoo-label"><?php echo esc_html(preg_replace('/\s?<span.*$/i', '', $menu_item[0])); ?></span>
				</div>
				<?php
				}
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * xse save settings.
 */
function xse_save($options) {
	$saved_options = xse_load();
	if(!$saved_options)
		update_option('xse_' + get_current_blog_id(), json_encode(new stdClass()), '', 'no');
	foreach($options as $option => $value) {
		$option = sanitize_text_field($option);
		$value = sanitize_text_field($value);
		if($value !== 'true' && $value !== 'false')
			$value = 'false';
		$saved_options->$option = $value;
	}
	xoo_log($saved_options);
	update_option('xse_' + get_current_blog_id(), json_encode($saved_options), '', 'no');
}

/**
 * xse load settings.
 */
function xse_load() {
	return json_decode(get_option('xse_' + get_current_blog_id()));
}

/**
 * xse AJAX process.
 */
function xse_trigger() {
	if(!isset($_POST['xse_nonce']) || !wp_verify_nonce($_POST['xse_nonce'], 'xse-nonce')) {
		die('Unauthorised');
	}
	xoo_log($_POST['option']);
	xse_save($_POST['option']);
}
add_action('wp_ajax_xse_trigger', 'xse_trigger');

/**
 * xse sort my sites toggle.
 */
function xse_sort_my_sites($sites) {	
	$options = xse_load();
	$activate = $options->sort_my_sites;
	if(filter_var($activate, FILTER_VALIDATE_BOOLEAN)) {
		usort($sites, function ($a, $b) {
			return strcasecmp($a->blogname, $b->blogname);
		});
	}
	return $sites;
}
add_filter('get_blogs_of_user','xse_sort_my_sites');

/**
 * xse sort submenu
 */
function xse_sort_submenus($menu_ord) {
	global $submenu;
	$menus_to_filter = xse_load();
	foreach($menus_to_filter as $menu_to_filter => $value) {
		if(filter_var($value, FILTER_VALIDATE_BOOLEAN) && // setting slider = on
										!function_exists('xse_' . $menu_to_filter) && // predefined menu (must exist for non submenu
										isset($submenu[$menu_to_filter])) { //no submenu, only main menu button
			xoo_log('sort submenu ' . $menu_to_filter);
			usort($submenu[$menu_to_filter], function ($a, $b) {
				return strcasecmp($a[0], $b[0]);
			});
		} 
	}
	return $menu_ord;
}
add_filter( 'custom_menu_order', 'xse_sort_submenus');

/**
 * xse main menu register.
 */
function xse_menu_register() {
		// Adds the sub page for xse
		global $xse_page;
		$xse_page = add_submenu_page('xoo_menu', 'Xoo Sort', 'Xoo Sort', 'manage_options', 'xoo_sort', 'xse_page');
}
add_action('admin_menu', 'xse_menu_register');

/**
 * xse load scripts.
 */
function xse_load_scripts($hook) {
	global $xse_page;
	if($hook != $xse_page) {
		return;
	}
	wp_enqueue_style('xse-css', plugin_dir_url(__FILE__) . 'css/xse.css', '', xoo_version_id());
	wp_enqueue_script('xse-ajax', plugin_dir_url(__FILE__) . 'js/xse-ajax.js', array('jquery'), xoo_version_id());
	wp_localize_script('xse-ajax', 'xse_vars', array('xse_nonce' => wp_create_nonce('xse-nonce')));
}
add_action('admin_enqueue_scripts', 'xse_load_scripts');