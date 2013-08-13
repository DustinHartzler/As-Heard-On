<?php
/* 
Plugin Name: As Heard On
Plugin URI: http://YourWebsiteEngineer.com
Description: Lets you display album artwork of podcasts you've been a guest on.  Widget included.  Optional link in sidebar block to "view all" podcast images on a page.  Requires WordPress 2.7 or higher.
Version: 0.5
Author: Dustin Hartzler
Author URI: http://YourWebsiteEngineer.com
*/


if ( !class_exists('AsHeardOn') ) {
    class AsHeardOn {
// +---------------------------------------------------------------------------+
// | WP hooks                                                                  |
// +---------------------------------------------------------------------------+
		function __construct() {
		/* WP actions */
			$this->widget = new AHO_Widget();
            $this->widget->aho_widget();

            add_action( 'init', array(&$this, 'addscripts'));
            add_action( 'admin_init', array(&$this, 'register_options'));
            add_action( 'admin_menu', array(&$this, 'addpages'));
			add_action( 'plugins_loaded', array(&$this, 'set'));
			add_shortcode( 'aho', 'showall');
		}

		function register_options() { // whitelist options
			register_setting( 'option-widget', 'admng' );
			register_setting( 'option-widget', 'showlink' );
			register_setting( 'option-widget', 'linktext' );
			register_setting( 'option-widget', 'image_width');
			register_setting( 'option-widget', 'opacity');
			register_setting( 'option-widget', 'setlimit' );
			register_setting( 'option-widget', 'linkurl' );
			register_setting( 'option-page', 'imgalign' );
			register_setting( 'option-page', 'imgmax' );
			register_setting( 'option-page', 'sorder' );
			register_setting( 'option-page', 'deldata' );
		}

		function unregister_ppg_options() { // unset options
			unregister_setting( 'option-widget', 'admng' );
			unregister_setting( 'option-widget', 'showlink' );
			unregister_setting( 'option-widget', 'linktext' );
			unregister_setting( 'option-widget', 'image_width');
			unregister_setting( 'option-widget', 'opacity');
			unregister_setting( 'option-widget', 'setlimit' );
			unregister_setting( 'option-widget', 'linkurl' );
			unregister_setting( 'option-page', 'imgalign' );
			unregister_setting( 'option-page', 'imgmax' );
			unregister_setting( 'option-page', 'sorder' );
			unregister_setting( 'option-page', 'deldata' );
		}


		function addscripts() { // include style sheet
		  	wp_enqueue_style('grayscale_css', plugins_url('/as-heard-on/css/past-podcast-guest-style.css') );
		  	wp_enqueue_style('slider_css', plugins_url('/as-heard-on/css/simple-slider.css') );
		  	wp_enqueue_style('volume_css', plugins_url('/as-heard-on/css/simple-slider-volume.css') );
		  	wp_enqueue_script( 'jquery' );
		  	wp_enqueue_script( 'grayscale', plugins_url('/as-heard-on/js/grayscale.js') ,array('jquery') );
		  	wp_enqueue_script( 'slider', plugins_url('/as-heard-on/js/simple-slider.js') ,array('jquery') ); 
		  	$params = array('ppg_opacity_js' => get_option('ppg_opacity') ); 
		  	wp_localize_script( 'grayscale', 'grayscale_vars', $params );  
		  	wp_enqueue_script( 'display', plugins_url('/as-heard-on/js/display.js') ,array('jquery') );    
		} 
	
// +---------------------------------------------------------------------------+
// | Create admin links                                                        |
// +---------------------------------------------------------------------------+

		function addpages() { 
			// Create top-level menu and appropriate sub-level menus:
			add_menu_page('Other Shows', 'Other Shows', 'update_plugins', 'ppg_manage', 'ppg_settings_pages', plugins_url('/as-heard-on/podcast_icon.png'));
		}
		
// +---------------------------------------------------------------------------+
// | Add Settings Link to Plugins Page                                         |
// +---------------------------------------------------------------------------+

		function add_settings_link($links, $file) {
			static $plugin;
			if (!$plugin) $plugin = plugin_basename(__FILE__);
			
			if ($file == $plugin){
				$settings_link = '<a href="admin.php?page=ppg_manage">'.__("Configure").'</a>';
				 // array_unshift($links, $settings_link);
				 $links[] = $settings_link;
			}
			return $links;
		}

		function set() {
			if (current_user_can('update_plugins')) 
			add_filter('plugin_action_links', array(&$this, 'add_settings_link'), 10, 2 );
		}











	}
}

if(class_exists('AsHeardOn')) { 
// Installation and uninstallation hooks 
register_activation_hook(__FILE__, array('AsHeardOn', 'activate')); 
register_deactivation_hook(__FILE__, array('AsHeardOn', 'deactivate')); 

// instantiate the plugin class 
$wp_plugin_template = new AsHeardOn(); 
}

// +---------------------------------------------------------------------------+
// | Widget for podcast(s) in sidebar                                          |
// +---------------------------------------------------------------------------+
	### Class: WP-Testimonials Widget
	 class AHO_Widget extends WP_Widget {
		// Constructor
		function aho_widget() {
			$widget_ops = array('description' => __('Displays random podcast in your sidebar', 'wp-podcast'));
			$this->WP_Widget('podcasts', __('Past Podcast Guest'), $widget_ops);
		}
	 
		// Display Widget
		function widget($args, $instance) {
			extract($args);
			$title = esc_attr($instance['title']);
	
			echo $before_widget.$before_title.$title.$after_title;
	
				ppg_onerandom();
	
			echo $after_widget;
		}
	 
		// When Widget Control Form Is Posted
		function update($new_instance, $old_instance) {
			if (!isset($new_instance['submit'])) {
				return false;
			}
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			return $instance;
		}
	 
		// DIsplay Widget Control Form
		function form($instance) {
			global $wpdb;
			$instance = wp_parse_args((array) $instance, array('title' => __('Hear Me On Other Shows', 'wp-podcast')));
			$title = esc_attr($instance['title']);
	?>
	 
	 
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wp-podcast'); ?>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
	 
	<input type="hidden" id="<?php echo $this->get_field_id('submit'); ?>" name="<?php echo $this->get_field_name('submit'); ?>" value="1" />
	<?php
		}
	}