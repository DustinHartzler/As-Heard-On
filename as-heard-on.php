<?php
/* 
Plugin Name: As Heard On
Plugin URI: http://YourWebsiteEngineer.com
Description: Lets you display album artwork of podcasts you've been a guest on.  Widget included.  Optional link in sidebar block to "view all" podcast images on a page.  Requires WordPress 2.7 or higher.
Version: 0.5
Author: Dustin Hartzler
Author URI: http://YourWebsiteEngineer.com
*/

// +---------------------------------------------------------------------------+
// | WP hooks                                                                  |
// +---------------------------------------------------------------------------+

/* WP actions */

register_activation_hook( __FILE__, 'ppg_install' );
register_deactivation_hook( __FILE__, 'ppg_deactivate' );
add_action('admin_menu', 'ppg_addpages');
add_action( 'admin_init', 'register_ppg_options' );
add_action('init', 'ppg_addcss');
add_action('plugins_loaded', 'ppg_Set');
add_shortcode('ppg', 'ppg_showall');

function register_ppg_options() { // whitelist options
  register_setting( 'ppg-option-widget', 'ppg_admng' );
  register_setting( 'ppg-option-widget', 'ppg_showlink' );
  register_setting( 'ppg-option-widget', 'ppg_linktext' );
  register_setting( 'ppg-option-widget', 'ppg_image_width');
  register_setting( 'ppg-option-widget', 'ppg_opacity');
  register_setting( 'ppg-option-widget', 'ppg_setlimit' );
  register_setting( 'ppg-option-widget', 'ppg_linkurl' );

  register_setting( 'ppg-option-page', 'ppg_imgalign' );
  register_setting( 'ppg-option-page', 'ppg_imgmax' );
  register_setting( 'ppg-option-page', 'ppg_sorder' );
  register_setting( 'ppg-option-page', 'ppg_deldata' );
}

function unregister_ppg_options() { // unset options
  unregister_setting( 'ppg-option-widget', 'ppg_admng' );
  unregister_setting( 'ppg-option-widget', 'ppg_showlink' );
  unregister_setting( 'ppg-option-widget', 'ppg_linktext' );
  unregister_setting( 'ppg-option-widget', 'ppg_image_width');
  unregister_setting( 'ppg-option-widget', 'ppg_opacity');
  unregister_setting( 'ppg-option-widget', 'ppg_setlimit' );
  unregister_setting( 'ppg-option-widget', 'ppg_linkurl' );

  unregister_setting( 'ppg-option-page', 'ppg_imgalign' );
  unregister_setting( 'ppg-option-page', 'ppg_imgmax' );
  unregister_setting( 'ppg-option-page', 'ppg_sorder' );
  unregister_setting( 'ppg-option-page', 'ppg_deldata' );
}


function ppg_addcss() { // include style sheet
  	  wp_enqueue_style('grayscale_css', plugins_url('/past-podcast-guest/css/past-podcast-guest-style.css') );
  	  wp_enqueue_style('slider_css', plugins_url('/past-podcast-guest/css/simple-slider.css') );
  	  wp_enqueue_style('volume_css', plugins_url('/past-podcast-guest/css/simple-slider-volume.css') );
  	  wp_enqueue_script( 'jquery' );
  	  wp_enqueue_script( 'grayscale', plugins_url('/past-podcast-guest/js/grayscale.js') ,array('jquery') );
  	  wp_enqueue_script( 'slider', plugins_url('/past-podcast-guest/js/simple-slider.js') ,array('jquery') ); 
  	  $params = array('ppg_opacity_js' => get_option('ppg_opacity') ); 
  	  wp_localize_script( 'grayscale', 'grayscale_vars', $params );  
  	  wp_enqueue_script( 'display', plugins_url('/past-podcast-guest/js/display.js') ,array('jquery') );    
}  

// +---------------------------------------------------------------------------+
// | Create admin links                                                        |
// +---------------------------------------------------------------------------+

function ppg_addpages() { 

	if (get_option('sfs_admng') == '') { $sfs_admng = 'update_plugins'; } else {$sfs_admng = get_option('sfs_admng'); }

// Create top-level menu and appropriate sub-level menus:
//	add_menu_page('Other Shows', 'Other Shows', $sfs_admng, 'ppg_manage', 'ppg_adminpage', plugins_url('/past-podcast-guest/podcast_icon.png'));
	add_menu_page('Other Shows', 'Other Shows', $sfs_admng, 'ppg_manage', 'ppg_settings_pages', plugins_url('/past-podcast-guest/podcast_icon.png'));

	// add_submenu_page('ppg_manage', 'Settings', 'Settings', $sfs_admng, 'tppg_config', 'ppg_options_page');
	// add_settings_section( 'add_new_podcast', 'Add New Podcast', 'add_new_podcast_cb', 'add_new_podcast' );
	// add_settings_section(  
	// 	'ppg_widget',				// ID used to identify this section and with which to register options  
	// 	'Widget Settings',							// Title to be displayed on the administration page  
	// 	'$sfs_admng',		// Callback used to render the description of the section  
	// 	'widget_options'					// Page on which to add this section of options  
	// );
}

// +---------------------------------------------------------------------------+
// | Create table on activation                                                |
// +---------------------------------------------------------------------------+

function ppg_install () {
   global $wpdb;

   $table_name = $wpdb->prefix . "ppg";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
 
		if ( $wpdb->supports_collation() ) {
				if ( ! empty($wpdb->charset) )
					$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
				if ( ! empty($wpdb->collate) )
					$charset_collate .= " COLLATE $wpdb->collate";
		}
      
	   $sql = "CREATE TABLE IF NOT EXISTS " . $table_name . "(
		testid int( 15 ) NOT NULL AUTO_INCREMENT ,
		show_name text,
		host_name text,
		show_url text,
		episode text,
		imgurl text,
		excerpt text,
		storder INT( 5 ) NOT NULL,
		PRIMARY KEY ( `testid` )
		) ".$charset_collate.";";
	  
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
	  
	   $insert = "INSERT INTO " . $table_name .
            " (show_name,host_name,show_url,episode,imgurl) " .
            "VALUES ('Your Website Engineer','Dustin Hartzler','http://YourWebsiteEngineer.com','001','http://YourWebsiteEngineer.com/AlbumArt.png')";
      $results = $wpdb->query( $insert );

	// insert default settings into wp_options 
	$toptions = $wpdb->prefix ."options";
	$defset = "INSERT INTO ".$toptions.
		"(option_name, option_value) " .
		"VALUES ('sfs_admng', 'update_plugins'),('sfs_deldata', ''),".
		"('sfs_linktext', 'Read More'),('sfs_linkurl', ''),('sfs_setlimit', '1'),".
		"('sfs_showlink', ''),('sfs_imgalign','right'),('sfs_sorder', 'testid DESC')";
	$dodef = $wpdb->query( $defset );

	} 

	// add default values for core settings if current version is older than 3.0
	if (get_option('ppg_version') < '3.0') { 
		$toptions = $wpdb->prefix ."options";
		$defset = "INSERT INTO ".$toptions.
			"(option_name, option_value) " .
			"VALUES ('sfs_admng', 'update_plugins'),('sfs_setlimit', '1'),('sfs_sorder', 'testid DESC')";
		$dodef = $wpdb->query( $defset );
	}
	
	// update version in options table
	  delete_option("ppg_version");
	  add_option("ppg_version", "0.5");
}

// +---------------------------------------------------------------------------+
// | Add Settings Link to Plugins Page                                         |
// +---------------------------------------------------------------------------+

function ppg_add_settings_link($links, $file) {
	static $ppg_plugin;
	if (!$ppg_plugin) $ppg_plugin = plugin_basename(__FILE__);
	
	if ($file == $ppg_plugin){
		$settings_link = '<a href="admin.php?page=tppg_config">'.__("Configure").'</a>';
		 // array_unshift($links, $settings_link);
		 $links[] = $settings_link;
	}
	return $links;
}

function ppg_Set() {
	if (current_user_can('update_plugins')) 
	add_filter('plugin_action_links', 'ppg_add_settings_link', 10, 2 );
}

// +---------------------------------------------------------------------------+
// | Add New Podcast                                                           |
// +---------------------------------------------------------------------------+

/* add new podcast form */
function ppg_newform() {
?>
	<div class="wrap">
		<h2>Add New Podcast</h2>
		<ul>
		<li>If you want to include this podcast image in the sidebar, you must have content in the &quot;Show URL&quot; field.</li>
		<li>The text in the &quot;Podcast Excerpt&quot; field will only appear on the summary page.</li>
		</ul>
		<br />
		<div id="ppg-form">
			<form name="addnew" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<label for="showname">Podcast Name:</label><input name="show_name" type="text" size="45" ><br/>
				<label for="hostname">Host Name:</label><input name="host_name" type="text" size="45" ><br/>
				<label for="showurl">Show URL:</label><input name="show_url" type="text" size="45" value="http://" onFocus="this.value=''"><br/>
				<label for="imgurl">Image URL:</label><input name="imgurl" type="text" size="45" > (copy File URL from <a href="<?php echo admin_url('/upload.php'); ?>" target="_blank">Media</a>) <br/>
				<label for="episode">Episode Number:</label><input name="episode" type="text" size="10"><br/>
				<label for="excerpt">Podcast Excerpt:</label><textarea name="excerpt" cols="45" rows="7"></textarea><br/>
				<label for="storder">Sort order:</label><input name="storder" type="text" size="10" /> (optional) <br/>
				<input type="submit" name="ppg_addnew" class="button button-primary" value="<?php _e('Add Podcast', 'ppg_addnew' ) ?>" /><br/>
			</form>
		</div>
	</div>
<?php } 

/* insert podcast into DB */
function ppg_insertnew() {
	global $wpdb;
	$table_name = $wpdb->prefix . "ppg";
	$show_name 	= $wpdb->escape($_POST['show_name']);	
	$host_name 	= $wpdb->escape($_POST['host_name']);
	$show_url 	= $_POST['show_url'];
	$imgurl 	= $_POST['imgurl'];
	$episode 	= $_POST['episode'];
	$excerpt 	= $_POST['excerpt'];
	$storder 	= $_POST['storder'];
	
	$insert = "INSERT INTO " . $table_name .
	" (show_name,host_name,show_url,imgurl,episode,excerpt,storder) " .
	"VALUES ('$show_name','$host_name','$show_url','$imgurl','$episode','$excerpt','$storder')";
	
	$results = $wpdb->query( $insert );

}

// +---------------------------------------------------------------------------+
// | Manage Page - list all and show edit/delete options                       |
// +---------------------------------------------------------------------------+

/* show podcast on settings page */
function ppg_showlist() { 
	global $wpdb;
	$table_name = $wpdb->prefix . "ppg";
	$ppglists = $wpdb->get_results("SELECT testid,show_name,host_name,show_url,imgurl,episode FROM $table_name");

	foreach ($ppglists as $ppglist) {
		echo '<div class="podcast-display">';
		echo '<img src="'.$ppglist->imgurl.'" width="100px" class="alignleft" style="margin:0 10px 10px 0;">';
		echo '<a href="admin.php?page=ppg_manage&amp;mode=ppgedit&amp;testid='.$ppglist->testid.'">Edit</a>';
		echo '&nbsp;|&nbsp;';
		echo '<a href="admin.php?page=ppg_manage&amp;mode=ppgrem&amp;testid='.$ppglist->testid.'" onClick="return confirm(\'Delete this testimonial?\')">Delete</a>';
		echo '<br>';
		echo '<strong>Show Name: </strong>';
		echo stripslashes($ppglist->show_name);
			if ($ppglist->host_name != '') {
				echo '<br><strong>Host Name: </strong>'.stripslashes($ppglist->host_name).'';
				if ($ppglist->show_url != '') {
					echo '<br><strong>Show URL: </strong> <a href="'.$ppglist->show_url.'">'.stripslashes($ppglist->show_url).'</a> ';
					if ($ppglist->episode !=''){
					echo '<br><strong>Episode: </strong>'.stripslashes($ppglist->episode).'';	
					}	
				}
			}
		echo '</div>'; 
	}
	echo '<div class="clear"></div>';
}

/* edit podcast form */

function ppg_edit($testid){
	global $wpdb;
	$table_name = $wpdb->prefix . "ppg";
	
	$getppg = $wpdb->get_row("SELECT testid, show_name, host_name, show_url, imgurl, episode, excerpt, storder FROM $table_name WHERE testid = $testid");
	
	echo '<h3>Edit Podcast</h3>';

	echo '<div id="ppg-form">';
	echo '<form name="edittst" method="post" action="admin.php?page=ppg_manage">';
	echo '<label for="show_name">Show Name:</label>
		  <input name="show_name" type="text" size="45" value="'.stripslashes($getppg->show_name).'"><br/>
			<label for="host_name">Host Name:</label>
		  	<input name="host_name" type="text" size="45" value="'.stripslashes($getppg->host_name).'"><br/>
		
			<label for="show_url">Show URL:</label>
		 	<input name="show_url" type="text" size="45" value="'.$getppg->show_url.'"><br/>
		
			<label for="imgurl">Image URL:</label>
			<input name="imgurl" type="text" size="45" value="'.$getppg->imgurl.'"> (copy File URL from <a href="'.admin_url('/upload.php').'" target="_blank">Media</a>) <br/>
			
			<label for="episode">Episode:</label>
		 	<input name="episode" type="text" size="2" value="'.$getppg->episode.'"><br/>

		 	<label for="excerpt">Show Recap:</label>
		  	<textarea name="excerpt" cols="45" rows="7">'.stripslashes($getppg->excerpt).'</textarea><br/>

			<label for="storder">Sort order:</label>
		 	<input name="storder" type="text" size=2" value="'.$getppg->storder.'">(optional)<br/>

		  	<input type="hidden" name="testid" value="'.$getppg->testid.'">
		  	<input name="ppgeditdo" type="submit" class="button button-primary" value="Update">';
	echo '<h3>Preview</h3>';
	echo '<div class="podcast-display" >';
	echo '<img src="'.$getppg->imgurl.'" width="90px" class="alignleft" style="margin:0 10px 10px 0;">';
		echo '<strong>Show Name: </strong>';
		echo stripslashes($getppg->show_name);
			if ($getppg->host_name != '') {
				echo '<br><strong>Host Name: </strong>'.stripslashes($getppg->host_name).'';
				if ($getppg->show_url != '') {
					echo '<br><strong>Show URL: </strong> <a href="'.$getppg->show_url.'">'.stripslashes($getppg->show_url).'</a> ';
					if ($getppg->episode !=''){
					echo '<br><strong>Episode: </strong>'.stripslashes($getppg->episode).'';	
					}	
					if ($getppg->excerpt !=''){
					echo '<br><strong>Show Recap: </strong>'.stripslashes($getppg->excerpt).'';	
					}
				}
			}
		echo '</div>'; 
	echo '</form>';
	echo '</div>';
}

/* update testimonial in DB */
function ppg_editdo($testid){
	global $wpdb;
	$table_name = $wpdb->prefix . "ppg";
	
	$testid = $testid;
	$show_name = $_POST['show_name'];
	$host_name = $_POST['host_name'];
	$show_url  = $_POST['show_url'];
	$imgurl    = $_POST['imgurl'];
	$episode   = $_POST['episode'];
	$excerpt   = $_POST['excerpt'];
	$storder   = $_POST['storder'];
	
	$wpdb->query("UPDATE " . $table_name .
	" SET show_name = '$show_name', ".
	" host_name = '$host_name', ".
	" show_url = '$show_url', ".
	" imgurl = '$imgurl', ".
	" episode = '$episode', ".
	" excerpt = '$excerpt', ".
	" storder = '$storder' ".
	" WHERE testid = '$testid'");
}

/* delete testimonials from DB */
function ppg_removetst($testid) {
	global $wpdb;
	$table_name = $wpdb->prefix . "ppg";
	
	$insert = "DELETE FROM " . $table_name .
	" WHERE testid = ".$testid ."";
	
	$results = $wpdb->query( $insert );

}


/* admin page display */
function ppg_adminpage() {
	global $wpdb;
?>
	<div class="wrap">
	<?php

		if (isset($_POST['ppg_addnew'])) {
			ppg_insertnew();
			?>
	<div id="message" class="updated fade"><p><strong><?php _e('Podcast Added'); ?>.</strong></p></div><?php
		}
		if ($_REQUEST['mode']=='ppgrem') {
			ppg_removetst($_REQUEST['testid']);
			?><div id="message" class="updated fade"><p><strong><?php _e('Podcast Deleted'); ?>.</strong></p></div><?php
		}
		if ($_REQUEST['mode']=='ppgedit') {
			ppg_edit($_REQUEST['testid']);
			exit;
		}
		if (isset($_REQUEST['ppgeditdo'])) {
			ppg_editdo($_REQUEST['testid']);
			?><div id="message" class="updated fade"><p><strong><?php _e('Podcast Updated'); ?>.</strong></p></div><?php
		}
			ppg_showlist(); // show podcasts
		?>
	</div>
	<div class="wrap"><?php ppg_newform(); // show form to add new podcast ?>
	</div>
	<div class="wrap">
	<?php 
$yearnow = date('Y');
if($yearnow == "2013") {
    $yearcright = "";
} else { 
    $yearcright = "2013-";
}
?>
	  <p>Past Podcast Plugin is &copy; Copyright <?php echo("".$yearcright."".date('Y').""); ?>, <a href="http://www.yourwebsiteengineer.com/" target="_blank">Dustin Hartzler</a> and distributed under the <a href="http://www.fsf.org/licensing/licenses/quick-guide-gplv3.html" target="_blank">GNU General Public License</a>. 
	  If you find this plugin useful, please consider a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7279865" target="_blank">donation</a>.</p>
	<p align="right" style="float:right">Need help? <a href="/' . PLUGINDIR . '/wp-testimonials/docs/documentation.php" target="_blank">documentation</a> &nbsp;|&nbsp; <a href="http://YourWebsiteEngineer.com/">support page</a></p>
	</div>
<?php } 

// +---------------------------------------------------------------------------+
// | Sidebar - show random podcast(s) in sidebar                               |
// +---------------------------------------------------------------------------+

/* show random testimonial(s) in sidebar */
function ppg_onerandom() {
	global $wpdb;
	$table_name = $wpdb->prefix . "ppg";
	if (get_option('ppg_setlimit') == '') {
		$ppg_setlimit = 1;
	} else {
		$ppg_setlimit = get_option('ppg_setlimit');
	}
	$randone = $wpdb->get_results("SELECT show_url, episode, imgurl FROM $table_name WHERE show_url !='' order by RAND() LIMIT $ppg_setlimit");

	echo '<div id="sfstest-sidebar">';
	
	foreach ($randone as $randone2) {
			echo '<div class="item">';
			echo '<a href="'.nl2br(stripslashes($randone2->show_url)).'" target="_blank"><img src="'.$randone2->imgurl.'" width="'.get_option('ppg_image_width').'" style="margin-right:10px;"></a>';
			echo '</div>';

		} // end loop
			$sfs_showlink = get_option('sfs_showlink');
			$sfs_linktext = get_option('sfs_linktext');
			$sfs_linkurl = get_option('sfs_linkurl');
			
				if (($sfs_showlink == 'yes') && ($sfs_linkurl !='')) {
					if ($sfs_linktext == '') { $sfs_linkdisplay = 'Read More'; } else { $sfs_linkdisplay = $sfs_linktext; }
					echo '<div class="ppgreadmore" ><a class="button" href="'.$sfs_linkurl.'">'.$sfs_linkdisplay.'</a></div>';
				}
		echo '<div class="clear"></div>';
	echo '</div>';
}

// +---------------------------------------------------------------------------+
// | Widget for podcast(s) in sidebar                                          |
// +---------------------------------------------------------------------------+
	### Class: WP-Testimonials Widget
	 class ppg_widget extends WP_Widget {
		// Constructor
		function ppg_widget() {
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
	 
	### Function: Init WP-Testimonials  Widget
	add_action('widgets_init', 'widget_ppg_init');
	function widget_ppg_init() {
		register_widget('ppg_widget');
	}



// +---------------------------------------------------------------------------+
// | Configuration options                                                     |
// +---------------------------------------------------------------------------+

function ppg_options_page() {
?>
	<div class="wrap">
	<?php if ($_REQUEST['updated']=='true') { ?>
	<div id="message" class="updated fade"><p><strong>Settings Updated</strong></p></div>
	<?php  } ?>

	<?php echo '<p align="right">Need help? <a href="/' . PLUGINDIR . '/wp-testimonials/docs/documentation.php" target="_blank">documentation</a> &nbsp;|&nbsp; <a href="http://www.sunfrogservices.com/web-programming/wp-testimonials/">support page</a></p>'; ?>
	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>
	<?php settings_fields( 'ppg-option-widget' ); ?>
	
	<table cellpadding="5" cellspacing="5">

	<tr valign="top">
	<td>Minimum user level to manage</td>
	<td>
	<?php if (get_option('sfs_admng') == 'update_plugins') { ?>
	<input type="radio" name="ppg_admng" value="update_plugins" checked /> Administrator
	<?php } else { ?>
	<input type="radio" name="ppg_admng" value="update_plugins" /> Administrator
	<?php } ?>	
	<?php if (get_option('sfs_admng') == 'edit_pages') { ?>
	<input type="radio" name="ppg_admng" value="edit_pages" checked /> Editor
	<?php } else { ?>
	<input type="radio" name="ppg_admng" value="edit_pages" /> Editor
	<?php } ?>
	<?php if (get_option('ppg_admng') == 'publish_posts') { ?>
	<input type="radio" name="ppg_admng" value="publish_posts" checked /> Author
	<?php } else { ?>
	<input type="radio" name="ppg_admng" value="publish_posts" /> Author
	<?php } ?>
	</td>
	</tr>

	<tr valign="top">
	<td>Show link in sidebar to full page of previous interviews</td>
	<td>
	<?php $sfs_showlink = get_option('ppg_showlink'); 
	if ($sfs_showlink == 'yes') { ?>
	<input type="checkbox" name="ppg_showlink" value="yes" checked />
	<?php } else { ?>
	<input type="checkbox" name="ppg_showlink" value="yes" />
	<?php } ?>
	</td>
	</tr>
	
	<tr valign="top">
	<td>Text for sidebar link (Read More, View All, etc)</td>
	<td><input type="text" name="ppg_linktext" value="<?php echo get_option('ppg_linktext'); ?>" /></td>
	</tr>

	<tr valign="top">
	<td>Image Width (for sidebar)</td>
	<td><input type="text" name="ppg_image_width" size="2" value="<?php echo get_option('ppg_image_width'); ?>" /><label>(pixels)</label></td>
	</tr>

	<tr valign="top">
	<td>How fast to transition from B&W to Color</td>
	<td><input type="text" data-slider="true" data-slider-range="0,5" data-slider-step=".1" data-slider-highlight="true" data-slider-theme="volume" name="ppg_opacity" value="<?php echo get_option('ppg_opacity'); ?>"><span class="output">seconds</span></td>
	</tr>

	<tr valign="top">
	<td>Number of podcasts to show in sidebar</td>
	<td><input type="text" name="ppg_setlimit" size="2" value="<?php echo get_option('ppg_setlimit'); ?>" /></td>
	</tr>

	<tr valign="top">
	<td>Previous podcast page for sidebar link<br/> (use shortcode [ppg])</td>
	<td> <select name="ppg_linkurl">
	 <option value="">
<?php echo attribute_escape(__('Select page')); ?></option> 
 <?php 
  $pages = get_pages(); 
  foreach ($pages as $pagg) {
  $pagurl = get_page_link($pagg->ID);
  $sfturl = get_option('ppg_linkurl');
  	if ($pagurl == $sfturl) {
		$option = '<option value="'.get_page_link($pagg->ID).'" selected>';
		$option .= $pagg->post_title;
		$option .= '</option>';
		echo $option;
	} else {
		$option = '<option value="'.get_page_link($pagg->ID).'">';
		$option .= $pagg->post_title;
		$option .= '</option>';
		echo $option;	
	}
  }
 ?>	</select></td>
	</tr>
		</table>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="ppg_admng,ppg_showlink,ppg_linktext,ppg_image_width,ppg_opacity,ppg_setlimit, ppg_linkurl,sfs_sorder,sfs_imgalign,sfs_imgmax,ppg_deldata" />
	
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save Widget Options') ?>" />
	</p>
<?php }

function ppg_page_options(){ ?>
		<div class="wrap">
	<?php if ($_REQUEST['updated']=='true') { ?>
	<div id="message" class="updated fade"><p><strong>Settings Updated</strong></p></div>
	<?php  } ?>

	<?php echo '<p align="right">Need help? <a href="/' . PLUGINDIR . '/wp-testimonials/docs/documentation.php" target="_blank">documentation</a> &nbsp;|&nbsp; <a href="http://www.sunfrogservices.com/web-programming/wp-testimonials/">support page</a></p>'; ?>
	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>
	<?php settings_fields( 'ppg-option-page' ); ?>
	<table cellpadding="5" cellspacing="5">
	<tr valign="top">
	<td>Sort podcasts on page by</td>
	<td>
	<?php if (get_option('ppg_sorder') == 'testid ASC') { ?>
	<input type="radio" name="ppg_sorder" value="testid ASC" checked /> Order entered, oldest first
	<?php } else { ?>
	<input type="radio" name="ppg_sorder" value="testid ASC" /> Order entered, oldest first
	<?php } ?><br/>	
	<?php if (get_option('ppg_sorder') == 'testid DESC') { ?>
	<input type="radio" name="ppg_sorder" value="testid DESC" checked /> Order entered, newest first
	<?php } else { ?>
	<input type="radio" name="ppg_sorder" value="testid DESC" /> Order entered, newest first
	<?php } ?><br/>
	<?php if (get_option('ppg_sorder') == 'storder ASC') { ?>
	<input type="radio" name="ppg_sorder" value="storder ASC" checked /> User defined sort order
	<?php } else { ?>
	<input type="radio" name="ppg_sorder" value="storder ASC" /> User defined sort order
	<?php } ?>
	</td>
	</tr>

	<tr valign="top">
	<td>Use class alignleft or alignright for testimonial image</td>
	<td>
	<?php $sfs_imgalign = get_option('ppg_imgalign'); 
	if ($sfs_imgalign == 'alignleft') { ?>
	<input type="radio" name="ppg_imgalign" value="alignleft" checked /> Left 
	<input type="radio" name="ppg_imgalign" value="alignright" /> Right
	<?php } elseif ($sfs_imgalign == 'alignright') { ?>
	<input type="radio" name="ppg_imgalign" value="alignleft" /> Left
	<input type="radio" name="ppg_imgalign" value="alignright" checked/> Right
	<?php } else { ?>
	<input type="radio" name="ppg_imgalign" value="alignleft" /> Left
	<input type="radio" name="ppg_imgalign" value="alignright" /> Right
	<?php } ?>
	</td>
	</tr>

	<tr valign="top">
	<td>Maximum height (in pixels) for image</td>
	<td><input type="text" name="ppg_imgmax" value="<?php echo get_option('ppg_imgmax'); ?>" /> (if left blank images will show full size)</td>
	</tr>
	
	<tr valign="top">
	<td>Remove table when deactivating plugin</td>
	<td>
	<?php $ppg_deldata = get_option('ppg_deldata'); 
	if ($ppg_deldata == 'yes') { ?>
	<input type="checkbox" name="ppg_deldata" value="yes" checked /> (this will result in all data being deleted!)
	<?php } else { ?>
	<input type="checkbox" name="ppg_deldata" value="yes" /> (this will result in all data being deleted!)
	<?php } ?>
	</td>
	</tr>
	
	</table>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="ppg_sorder,ppg_imgalign,ppg_imgmax,ppg_deldata" />
	
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save Page Changes') ?>" />
	</p>
	
	</form>
	
	</div>
<?php 
}


// +---------------------------------------------------------------------------+
// | Uninstall plugin                                                          |
// +---------------------------------------------------------------------------+

function ppg_deactivate () {
	global $wpdb;

	$table_name = $wpdb->prefix . "ppg";

	$ppg_deldata = get_option('ppg_deldata');
	if ($ppg_deldata == 'yes') {
		$wpdb->query("DROP TABLE {$table_name}");
		delete_option("ppg_showlink");
		delete_option("ppg_linktext");
		delete_option("ppg_linkurl");
		delete_option("ppg_deldata");
		delete_option("ppg_setlimit");
		delete_option("ppg_admng");
		delete_option("ppg_sorder");
		delete_option("ppg_imgalign");
		delete_option("ppg_imgmax");
 	}
    delete_option("ppg_version");
	unregister_ppg_options();

}

// +---------------------------------------------------------------------------+
// | Show podcasts on page with shortcode [ppg]					               |
// +---------------------------------------------------------------------------+


/* show page of all testimonials */
function ppg_showall() {
global $wpdb;

	$sfimgalign = get_option('sfs_imgalign');
	if ($sfimgalign == '') { $sfs_imgalign = 'alignright'; } else { $sfs_imgalign = get_option('sfs_imgalign'); }

	$sfs_sorder = (get_option('sfs_sorder'));
	if ($sfs_sorder != 'testid ASC' AND $sfs_sorder != 'testid DESC' AND $sfs_sorder != 'storder ASC')
	{ $sfs_sorder2 = 'testid ASC'; } else { $sfs_sorder2 = $sfs_sorder; }
	
	$table_name = $wpdb->prefix . "ppg";
	$tstpage = $wpdb->get_results("SELECT testid, show_name, host_name, show_url, imgurl, episode, excerpt, storder FROM $table_name WHERE imgurl !='' ORDER BY $sfs_sorder2");
	$retvalo = '';
	$retvalo .= '';
	$retvalo .= '<div id="sfstest-page">';
	foreach ($tstpage as $tstpage2) {
		if ($tstpage2->imgurl != '') { // don't show podcasts without album art.

			
			if ($tstpage2->imgurl != '') { // check for image
				$sfs_imgmax = get_option('sfs_imgmax');
				if ($sfs_imgmax == '') { $sfiheight = ''; } else { $sfiheight = ' height="'.get_option('sfs_imgmax').'"'; }
				$retvalo .= '<img src="'.$tstpage2->imgurl.'"'.$sfiheight.' class="'.$sfs_imgalign.'" alt="'.stripslashes($tstpage2->show_name).'">';
			}
			
				if ($tstpage2->show_name != '') {
					if ($tstpage2->show_url != '') {
							$retvalo .= '<strong>Show Name: </strong><a href="'.$tstpage2->show_url.'" class="cite-link">'.stripslashes($tstpage2->show_name).'</a><br>';
					} else {
						$retvalo .= stripslashes($tstpage2->show_name).'';
					}
					if ($tstpage2->host_name != ''){
						$retvalo .= '<strong>Host Name: </strong>'.$tstpage2->host_name.'<br>';
					} else {
					}
					if ($tstpage2->episode != ''){
						$retvalo .= '<strong>Episode: </strong>' .$tstpage2->episode. '<br>';
					}
					else {
					}
					if ($tstpage2->excerpt != ''){
						$retvalo .= '<strong>Show Recap: </strong>' .$tstpage2->excerpt. '<br>';
					}
					else {
					}
				} else {
					$retvalo .= stripslashes($tstpage2->clientname).'';
				}
				$retvalo .= '<div class="clear"></div>';

		}
	}
	$retvalo .= '</div>';
return $retvalo;
}

// +---------------------------------------------------------------------------+
// | Plugin Settings Pages 										               |
// +---------------------------------------------------------------------------+

function ppg_settings_pages(){
	global $saf_networks; ?>

	<div class="wrap">
		<?php screen_icon('options-general'); ?>
		<h2>Past Podcast Guest Settings</h2>
		<style>
			#reset_color { cursor:pointer; }
		</style>

		<?php
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'add_new_podcast';
		?>

	<h2 class="nav-tab-wrapper">
		<a href="admin.php?page=ppg_manage&tab=add_new_podcast" class="nav-tab <?php echo $active_tab == 'add_new_podcast' ? 'nav-tab-active' : ''; ?>">Add New Podcast</a>
		<a href="admin.php?page=ppg_manage&tab=widget_options" class="nav-tab <?php echo $active_tab == 'widget_options' ? 'nav-tab-active' : ''; ?>">Widget Options</a>
		<a href="admin.php?page=ppg_manage&tab=full_page_options" class="nav-tab <?php echo $active_tab == 'full_page_options' ? 'nav-tab-active' : ''; ?>">Full Page Options</a>
	</h2>


			<?php
			if ( $active_tab == 'add_new_podcast' ) {  
				ppg_adminpage();
			
			} elseif ( $active_tab == 'widget_options' ) { 
				ppg_options_page();
			} elseif ( $active_tab == 'full_page_options' ) {
				ppg_page_options();
			}

	?> </div> <?php
}

?>