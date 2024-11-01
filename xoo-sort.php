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
if (!defined('WPINC')) {
	die;
}

include 'xoo-common.php';

define('XSE_VERSION', '1.0.0');
define('XSE_FILE_PATH', dirname(__FILE__));
define('XSE_DIR_NAME', basename(XSE_FILE_PATH));
define('XSE_FOLDER', dirname(plugin_basename(__FILE__)));
define('XSE_NAME', plugin_basename(__FILE__));
define('XSE_DIR', WP_CONTENT_DIR . '/plugins/' . XSE_FOLDER);
define('XSE_OPTIONS', 'xse_' . get_current_blog_id() . '_options');

/**
 * xse main page.
 */
function xse_page() {
	if (!current_user_can('manage_options')) {
		wp_die(__('You do not have sufficient privilages to check for broken links. You need manage_options rights, talk to your administrator.'));
	}
	
	global $submenu;
	global $menu;
	
	$options = xse_load();
	$checked = [];
	foreach($options as $option) {
		foreach($option as $name => $value) {
			if(filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
				$checked[$name] = ' checked';
			} else {
				$checked[$name] = '';
			}
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
			<div id="xse_sort_my_sites" class="xoo-toggle-wrap">
				<label class="xoo-switch">
				<input type="checkbox" id="sort_my_sites" class="xse-trigger"<?php if(isset($checked['sort_my_sites'])) echo esc_attr($checked['sort_my_sites']); ?>>
					<span class="xoo-toggle"></span>
				</label><span class="xoo-label"><?php echo _e("My sites",'xoo-xse'); ?></span>
			</div>
				
	<?php 
	global $wp_admin_bar;
	xoo_log('wp_admin_br =');
	xoo_log($wp_admin_bar->get_nodes());
	foreach($wp_admin_bar->get_nodes() as $node => $submenu) {
		if(!$submenu->parent) {
			$admin_bar_menus[] = $submenu->id;
		}
	}
	xoo_log($admin_bar_menus);
	$xse_menus = [];
	foreach($admin_bar_menus as $admin_bar_menu) {
		$count = 0;
		foreach($wp_admin_bar->get_nodes() as $node => $submenu) {	
			if($submenu->parent == $admin_bar_menu) {
				$count++;
			}
		}
		if($count>1) {
			$xse_menus[] = $admin_bar_menu;
		}
	}
	foreach($xse_menus as $admin_bar_menu) { 
	?>		
				<div id="sort_admin_bar_menus" class="xoo-toggle-wrap">
					<label class="xoo-switch">
					<input type="checkbox" id="<?php echo esc_attr($admin_bar_menu); ?>" class="xse-trigger"<?php if(isset($checked[$admin_bar_menu])) echo esc_attr($checked[$admin_bar_menu]); ?>>
						<span class="xoo-toggle"></span>
					</label><span class="xoo-label"><?php echo esc_html(get_node($admin_bar_menu)->title); ?></span>
				</div>
				<?php } ?>
			<div id="sort_admin_bar_menus" class="xoo-toggle-wrap">
				<label class="xoo-switch">
				<input type="checkbox" id="new-content" class="xse-trigger"<?php if(isset($checked['new-content'])) echo esc_attr($checked['new-content']); ?>>
					<span class="xoo-toggle"></span>
				</label><span class="xoo-label"><?php echo _e("New content",'xoo-xse'); ?></span>
			</div>
		</div>
		<div class="xoo-list-toggles">
		<h2><?php echo _e('WordPress Menu','xoo-xse'); ?></h2>
			<?php 
			foreach($menu as $menu_item) { 
				if(strpos($menu_item[4], 'wp-menu-separator') === false) { 
				?>
				<div id="sort_submenus" class="xoo-toggle-wrap">
				<?php 
				if(isset($menu_item[6])) {
					if(strpos($menu_item[6], 'dashicons-') === 0) {
						?>
						<span class="xse-menu-icon dashicons <?php echo esc_attr($menu_item[6]); ?>"></span>
						<?php
					} else {
					  ?>
						<img src="<?php echo esc_url($menu_item[6]); ?>" class="xse-menu-icon" />
						<?php
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
    
	//update_option(XSE_OPTIONS, array(), '', 'no');
	//return
	
	$saved_options = xse_load();
	xoo_log('Just loaded options: ');
	xoo_log($saved_options);
	if(!$saved_options) {
		update_option(XSE_OPTIONS, array(), '', 'no');
	}
	
	foreach($options as $type_name => $type_values) {
		foreach($type_values as $name => $value) {
			if(!isset($saved_options[$type_name])) {
				$saved_options[$type_name] = [];
			}
			
			$saved_type = &$saved_options[$type_name];
			$saved_type[$name] = $value;
		}
	}
	xoo_log('Processed options: ');
	xoo_log($saved_options);
	update_option(XSE_OPTIONS, $saved_options, '', 'no');
}

/**
 * xse load settings.
 */
function xse_load() {
	return get_option(XSE_OPTIONS);
}

/**
 * xse AJAX process.
 */
function xse_trigger() {
	if(!isset($_POST['xse_nonce']) || !wp_verify_nonce($_POST['xse_nonce'], 'xse-nonce')) {
		die('Unauthorised');
	}
	if(!isset($_POST['option']) || !is_array($_POST['option'])) {
		die();
	}
	
	// debug
	$sanitized_options = $_POST['option'];
	
	/**
	$sanitized_options = [];
	foreach($_POST['option'] as $option => $value) {
		xoo_log($option);
		xoo_log($value);
		if(!is_string($option) || !is_array($value)) die();
		if(!is_string($value['type']) || !is_string($value['value']) || !($value['value'] === 'true' || $value['value'] === 'false')) die();
				
		$sanitized_options[sanitize_text_field($option)] = array(
			'value' => sanitize_text_field($value['value']),
			'type' => sanitize_text_field($value['type'])
		);
	}
	*/
	
	//xoo_log($sanitized_options);
	xse_save($sanitized_options);

}
add_action('wp_ajax_xse_trigger', 'xse_trigger');

/**
 * xse sort my sites toggle.
 */
function xse_sort_my_sites($sites) {	
	$options = xse_load();
	if(!isset($options['xse_sort_my_sites'])) {
		return($sites);
	}
	
	$sort_my_sites = $options['xse_sort_my_sites'];
	
	if(!isset($sort_my_sites['sort_my_sites'])) {
		return($sites);
	}
	
	$activate = $sort_my_sites['sort_my_sites'];
	
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
	$options = xse_load();
	
	if(!isset($options['sort_submenus'])) {
		return $menu_ord;
	}
	
	$menus_to_filter = $options['sort_submenus'];
	foreach($menus_to_filter as $menu_to_filter => $value) {
		if(filter_var($value, FILTER_VALIDATE_BOOLEAN) && // setting slider = on
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

function xse_sort_admin_bar_menus() {
	global $wp_admin_bar;
	$options = xse_load();
	
	if(!isset($options['sort_admin_bar_menus'])) {
		return;
	}
	
	$menus_to_filter = $options['sort_admin_bar_menus'];

	foreach($menus_to_filter as $admin_bar_menu) {
		$xse_menus = [];
		foreach($wp_admin_bar->get_nodes() as $node => $submenu) {	
			if($submenu->parent == $admin_bar_menu) {
				$xse_menus[] = $submenu;
				$wp_admin_bar->remove_menu($submenu->id);
			}
		}
		usort($xse_menus, function ($a, $b) {
			return strcasecmp($a->title, $b->title);
		});
		foreach($xse_menus as $submenu) {
			$wp_admin_bar->add_node($submenu);
		}
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
add_action('wp_before_admin_bar_render', 'xse_sort_admin_bar_menus' ) ;

/**
 * Register xse main menu under the xoo main menu. 
 * Also register xoo main menu if this is the first xoo plugin installed.
 */
function xse_menu_register() {
	// Add the main menu page for Xoo if it doesnt exist
	if (empty($GLOBALS['admin_page_hooks']['xoo_menu'])) {
		// Will add a duplicate submenu
		add_menu_page('Xoo', 'Xoo', 'manage_options', 'xoo_menu', 'xoo_main_menu_page', plugins_url(XSE_FOLDER . '/static/img/xoocode.favicon.menu.png'), '98.999999999901');
		$main_menu_was_set = true;
	}

	// Adds the sub page for xse
	global $xse_page;
	$xse_page = add_submenu_page('xoo_menu', 'Xoo Sort', 'Xoo Sort', 'manage_options', 'xoo_sort', 'xse_page');
	
	// Remove the duplicate submenu if it was set above
	if(isset($main_menu_was_set)) {
		remove_submenu_page('xoo_menu','xoo_menu');
	}
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