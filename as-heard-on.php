<?php
/* 
Plugin Name: As Heard On
Plugin URI: http://YourWebsiteEngineer.com
Description: Lets you display album artwork of podcasts you've been a guest on.  Widget included.  Optional link in sidebar block to "view all" podcast images on a page.
Version: 2.0
Author: Dustin Hartzler
Author URI: http://YourWebsiteEngineer.com
*/


if ( !class_exists('AsHeardOn') ) {
    class AsHeardOn {
    	//public $wpdb;
// +---------------------------------------------------------------------------+
// | WP hooks                                                                  |
// +---------------------------------------------------------------------------+
		function __construct() {
		/* WP actions */
            add_action( 'init', array(&$this, 'addscripts'));
            add_action( 'admin_init', array(&$this, 'register_options'));
            add_action( 'admin_menu', array(&$this, 'addpages'));
			add_action( 'plugins_loaded', array(&$this, 'set'));
			add_shortcode( 'aho', array(&$this, 'showall'));
		}

		function register_options() { // whitelist options
			register_setting( 'option-widget', 'admng' );
			register_setting( 'option-widget', 'showlink' );
			register_setting( 'option-widget', 'linktext' );
			register_setting( 'option-widget', 'image_width');
			register_setting( 'option-widget', 'image_height');
			register_setting( 'option-widget', 'opacity');
			register_setting( 'option-widget', 'setlimit' );
			register_setting( 'option-widget', 'linkurl' );
			register_setting( 'option-page', 'aho_imgalign' );
			register_setting( 'option-page', 'imgdisplay' );
			register_setting( 'option-page', 'imgmax' );
			register_setting( 'option-page', 'sorder' );
			register_setting( 'option-page', 'deldata' );
		}

		function unregister_options() { // unset options
			unregister_setting( 'option-widget', 'admng' );
			unregister_setting( 'option-widget', 'showlink' );
			unregister_setting( 'option-widget', 'linktext' );
			unregister_setting( 'option-widget', 'image_width');
			unregister_setting( 'option-widget', 'image_height');
			unregister_setting( 'option-widget', 'opacity');
			unregister_setting( 'option-widget', 'setlimit' );
			unregister_setting( 'option-widget', 'linkurl' );
			unregister_setting( 'option-page', 'aho_imgalign' );
			unregister_setting( 'option-page', 'imgmax' );
			unregister_setting( 'option-page', 'sorder' );
			unregister_setting( 'option-page', 'deldata' );
		}


		function addscripts() { // include style sheet
			wp_enqueue_style('grayscale_css', plugins_url('/as-heard-on/css/style.css') );
			wp_enqueue_script( 'jquery' );			
			if ( ! is_admin() ) {
				
			  	wp_enqueue_script( 'grayscale', plugins_url('/as-heard-on/js/grayscale.js') ,array('jquery') );
			  	$params = array('opacity_js' => get_option('opacity') ); 
			  	wp_localize_script( 'grayscale', 'grayscale_vars', $params ); 
			} else {
				wp_enqueue_style('volume_css', plugins_url('/as-heard-on/css/simple-slider-volume.css') );
				wp_enqueue_script( 'display', plugins_url('/as-heard-on/js/display.js') ,array('jquery') );
			  	wp_enqueue_script( 'slider', plugins_url('/as-heard-on/js/simple-slider.js') ,array('jquery') ); 
			  	// Upload Button to Work
			  	wp_enqueue_script('media-upload');
	 			wp_enqueue_script('thickbox');  
	 			wp_enqueue_style ('thickbox'); 
	 			wp_enqueue_script('media-upload-box', plugins_url('/as-heard-on/js/media.js'), array('jquery','media-upload','thickbox'));
	 		}
		} 
	
// +---------------------------------------------------------------------------+
// | Create admin links                                                        |
// +---------------------------------------------------------------------------+

		function addpages() { 
			// Create top-level menu and appropriate sub-level menus:
			add_menu_page('As Heard On', 'As Heard On', 'manage_options', 'setting_page', array($this, 'settings_pages'), 'dashicons-microphone');
		}

// +---------------------------------------------------------------------------+
// | Add Settings Link to Plugins Page                                         |
// +---------------------------------------------------------------------------+

		function add_settings_link($links, $file) {
			static $plugin;
			if (!$plugin) $plugin = plugin_basename(__FILE__);
			
			if ($file == $plugin){
				$settings_link = '<a href="admin.php?page=setting_page">'.__("Configure").'</a>';
				$links[] = $settings_link;
			}
			return $links;
		}

		function set() {
			if (current_user_can('update_plugins')) 
			add_filter('plugin_action_links', array(&$this, 'add_settings_link'), 10, 2 );
		}

// +---------------------------------------------------------------------------+
// | Plugin Settings Pages 										               |
// +---------------------------------------------------------------------------+

		function settings_pages(){
			global $saf_networks; ?>

			<div class="wrap">
				<?php screen_icon('options-general'); ?>
				<h2>As Heard On Settings</h2>
				<style>
					#reset_color { cursor:pointer; }
				</style>

				<?php
				$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'add_new_podcast';
				?>

				<h2 class="nav-tab-wrapper">
					<a href="admin.php?page=setting_page&tab=add_new_podcast" class="nav-tab <?php echo $active_tab == 'add_new_podcast' ? 'nav-tab-active' : ''; ?>">Podcasts</a>
					<a href="admin.php?page=setting_page&tab=widget_options" class="nav-tab <?php echo $active_tab == 'widget_options' ? 'nav-tab-active' : ''; ?>">Widget Options</a>
					<a href="admin.php?page=setting_page&tab=full_page_options" class="nav-tab <?php echo $active_tab == 'full_page_options' ? 'nav-tab-active' : ''; ?>">Full Page Options</a>
				</h2>

				<?php
				if ( $active_tab == 'add_new_podcast' ) {  
					$this->adminpage();
				} elseif ( $active_tab == 'widget_options' ) { 
					$this->widget_options();
				} elseif ( $active_tab == 'full_page_options' ) {
					$this->page_options();
				}

				?> </div> <?php
		}
// +---------------------------------------------------------------------------+
// | Add New Podcast                                                           |
// +---------------------------------------------------------------------------+

/* add new podcast form */
		function newform() {
		?>
			<div class="wrap">
				<h2>Add New Podcast</h2>
				<ul>
				<li>If you want to include this podcast image in the sidebar, you must have content in the &quot;Show URL&quot; field.</li>
				<li>The text in the &quot;Podcast Excerpt&quot; field will only appear on the summary page.</li>
				</ul>
				<br />
				<div id="ppg-form">
					<form name="AddNew" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
						<table cellpadding="0" cellspacing="2">
							<tr valign="top">
								<td><label for="showname">Podcast Name:</label></td>
								<td><input name="show_name" type="text" size="45" ></td>
							</tr>
							<tr valign="top">
								<td><label for="hostname">Host Name:</label></td>
								<td><input name="host_name" type="text" size="45" ></td>
							</tr>

							<tr valign="top">
								<td><label for="showurl">Show URL:</label></td>
								<td><input name="show_url" type="text" size="45" value="http://" onFocus="this.value=''"></td>
							</tr>

							<tr valign="top">
								<td><label for="imgurl">Image URL:</label></td>
								<td><input name="imgurl" type="text" size="45" ><input class="media-upload-button button" type="button" value="Upload Image" /></td>
							</tr>
							<tr valign="top">
								<td><label for="imgurl">&nbsp;</label></td>
								<td><strong>For grayscale transition to work, album art must be on same server as website</strong></td>
							</tr>

							<tr valign="top">
								<td><label for="episode">Episode Number:</label></td>
								<td><input name="episode" type="text" size="5"></td>
							</tr>

							<tr valign="top">
								<td><label for="excerpt">Podcast Excerpt:</label></td>
								<td><textarea name="excerpt" cols="45" rows="7"></textarea></td>
							</tr>

							<tr valign="top">
								<td><label for="storder">Sort order:</label></td>
								<td><input name="storder" type="text" size="10" /> (optional) </td>
							</tr>
							<tr valign="top">
								<td></td>
								<td><input type="submit" name="addnew" class="button button-primary" value="<?php _e('Add Podcast', 'addnew' ) ?>" /></td>
							</tr>
						
					</table>
					</form>
				</div>
			</div>
		<?php } 

/* insert podcast into DB */
		function insertnew() {
			global $wpdb;

			$allowed_html = array(
			    'a' => array(
			        'href' => array(),
			        'title' => array()
			    ),
			    'br' => array(),
			    'em' => array(),
			    'strong' => array()
			);

			$table_name = $wpdb->prefix . "aho";
			$show_name 	= sanitize_text_field( $_POST['show_name'] );	
			$host_name 	= sanitize_text_field( $_POST['host_name'] );
			$show_url 	= sanitize_text_field( $_POST['show_url'] );
			$imgurl 	= sanitize_text_field( $_POST['imgurl'] );
			$episode 	= sanitize_text_field( $_POST['episode'] );
			$excerpt 	= wp_kses( $_POST['excerpt'], $allowed_html );
			$storder 	= sanitize_text_field( $_POST['storder'] );
			
			$insert = $wpdb->prepare( "INSERT INTO " . $table_name .
				" (show_name,host_name,show_url,imgurl,episode,excerpt,storder) " .
				"VALUES ('%s','%s','%s','%s','%d','%s','%s')",
				$show_name,
				$host_name,
				$show_url,
				$imgurl,
				$episode,
				$excerpt,
				$storder
			);
			
			$results = $wpdb->query( $insert );

		}
// +---------------------------------------------------------------------------+
// | Create table on activation                                                |
// +---------------------------------------------------------------------------+

		function activate () {
   			global $wpdb;

   			$table_name = $wpdb->prefix . "aho";
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
		            "VALUES ('Your Website Engineer','Dustin Hartzler','http://YourWebsiteEngineer.com','001','http://YourWebsiteEngineer.com/wp-content/podcasts/YWE.png')";
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
				// update version in options table
				  delete_option("ppg_version");
				  add_option("ppg_version", "0.5");
		}

		/* update item in DB */
		function aho_editdo($testid){
			global $wpdb;
			$allowed_html = array(
			    'a' => array(
			        'href' => array(),
			        'title' => array()
			    ),
			    'br' => array(),
			    'em' => array(),
			    'strong' => array()
			);

			$table_name = $wpdb->prefix . "aho";
			
			$testid = $testid;
			$show_name 	= sanitize_text_field( $_POST['show_name'] );	
			$host_name 	= sanitize_text_field( $_POST['host_name'] );
			$show_url 	= sanitize_text_field( $_POST['show_url'] );
			$imgurl 	= sanitize_text_field( $_POST['imgurl'] );
			$episode 	= sanitize_text_field( $_POST['episode'] );
			$excerpt 	= wp_kses( $_POST['excerpt'], $allowed_html );
			$storder 	= sanitize_text_field( $_POST['storder'] );
			
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
		function removetst($testid) {
			global $wpdb;
			$table_name = $wpdb->prefix . "aho";
			
			$insert = $wpdb->prepare( "DELETE FROM " . $table_name .
			" WHERE testid = '%d'", absint( $testid ) );
			
			$results = $wpdb->query( $insert );

		}

		/* admin page display */
		function adminpage() {
			global $wpdb; ?>
			<div class="wrap">
			<?php
				if (isset($_POST['addnew'])) {
					$this->insertnew();
					?><div id="message" class="updated fade"><p><strong><?php _e('Podcast Added'); ?>.</strong></p></div><?php
				}
				if ($_REQUEST['mode']=='ahorem') {
					$this->removetst($_REQUEST['testid']);
					?><div id="message" class="updated fade"><p><strong><?php _e('Podcast Deleted'); ?>.</strong></p></div><?php
				}
				if ($_REQUEST['mode']=='ahoedit') {
					$this->aho_edit($_REQUEST['testid']);
					exit;
				}
				if (isset($_REQUEST['editdo'])) {
					$this->aho_editdo($_REQUEST['testid']);
					?><div id="message" class="updated fade"><p><strong><?php _e('Podcast Updated'); ?>.</strong></p></div><?php
				}
					$this->showlist(); // show podcasts
				?>
			</div>
			<div class="wrap">
				<?php $this->newform(); // show form to add new podcast ?>
			</div>
			<div class="wrap">
			<?php $yearnow = date('Y');
			if($yearnow == "2013") {
			    $yearcright = "";
			} else { 
			    $yearcright = "2013-";
			}
			?>
			<p>As Heard On Plugin is &copy; Copyright <?php echo("".$yearcright."".date('Y').""); ?>, <a href="http://www.yourwebsiteengineer.com/" target="_blank">Dustin Hartzler</a> and distributed under the <a href="http://www.fsf.org/licensing/licenses/quick-guide-gplv3.html" target="_blank">GNU General Public License</a>. 
			If you find this plugin useful, please consider a <a href="http://#" target="_blank">donation</a>.</p>
			</div>
<?php }




// +---------------------------------------------------------------------------+
// | Configuration options                                                     |
// +---------------------------------------------------------------------------+
		function widget_options() {
		?>
			<div class="wrap">
				<?php if ($_REQUEST['settings-updated']=='true') { ?>
				<div id="message" class="updated fade"><p><strong>Settings Updated</strong></p></div>
				<?php  } ?>

				<?php echo '<p align="right">Need help? <a href="http://wordpress.org/plugins/as-heard-on/" target="_blank">documentation</a> &nbsp;|&nbsp; <a href="http://wordpress.org/support/plugin/as-heard-on" target="_blank">support page</a></p>'; ?>
				<form method="post" action="options.php">
				<?php wp_nonce_field('update-options'); ?>
				<?php settings_fields( 'option-widget' ); ?>
			
				<table cellpadding="5" cellspacing="5">
					<tr valign="top">
						<td>Show link in sidebar to full page of previous interviews</td>
						<td><?php $sfs_showlink = get_option('showlink'); 
						if ($sfs_showlink == 'yes') { ?>
							<input type="checkbox" name="showlink" value="yes" checked />
						<?php } else { ?>
							<input type="checkbox" name="showlink" value="yes" />
						<?php } ?>
						</td>
					</tr>
			
					<tr valign="top">
						<td>Text for sidebar aho-button (Read More, View All, etc)</td>
						<td><input type="text" name="linktext" value="<?php echo get_option('linktext'); ?>" /></td>
					</tr>

					<tr valign="top">
						<td>Page link for sidebar aho-button<br/> (use shortcode [aho])</td>
						<td> <select name="linkurl">
			 			<option value="">
						<?php echo esc_attr(__('Select page')); ?></option> 
						<?php 
						  $pages = get_pages(); 
						  foreach ($pages as $pagg) {
						  $pagurl = get_page_link($pagg->ID);
						  $sfturl = get_option('linkurl');
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

					<tr valign="top">
						<td>Image Width</td>
						<td><input type="text" name="image_width" size="2" value="<?php echo get_option('image_width'); ?>" /><label>(pixels)</label></td>
					</tr>

					<tr valign="top">
						<td>Image Height</td>
						<td><input type="text" name="image_height" size="2" value="<?php echo get_option('image_height'); ?>" /><label>(pixels)</label></td>
					</tr>

					<tr valign="top">
						<td>How fast to transition from B&W to Color</td>
						<td><input type="text" data-slider="true" data-slider-range="0,5" data-slider-step=".25" data-slider-highlight="true" data-slider-theme="volume" name="opacity" value="<?php echo get_option('opacity'); ?>" > seconds</td>
					</tr>
					<tr valign="top">
						<td>Number of podcasts to show in sidebar</td>
						<td><input type="text" name="setlimit" size="2" value="<?php echo get_option('setlimit'); ?>" /></td>
					</tr>

			</table>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="admng,showlink,linktext,image_width,image_height,opacity,setlimit, linkurl,sfs_sorder,sfs_imgalign,imgmax,deldata" />
			
			<p class="submit">
			<input type="submit" class="button button-primary" value="<?php _e('Save Widget Options') ?>" />
			</p>

			  
		<?php }

		function page_options(){ ?>
			<div class="wrap">
				<?php if ($_REQUEST['settings-updated']=='true') { ?>
				<div id="message" class="updated fade"><p><strong>Page Settings Updated</strong></p></div>
				<?php  } ?>

				<?php echo '<p align="right">Need help? <a href="http://wordpress.org/plugins/as-heard-on/" target="_blank">documentation</a> &nbsp;|&nbsp; <a href="http://wordpress.org/support/plugin/as-heard-on" target="_blank">support page</a></p>'; ?>
				<form method="post" action="options.php">
					<?php wp_nonce_field('update-options'); ?>
					<?php settings_fields( 'option-page' ); ?>
					<table cellpadding="5" cellspacing="5">
						<tr valign="top">
							<td>Sort podcasts on page by</td>
							<td>
								<?php if (get_option('sorder') == 'testid ASC') { ?>
								<input type="radio" name="sorder" value="testid ASC" checked /> Order entered, oldest first
								<?php } else { ?>
								<input type="radio" name="sorder" value="testid ASC" /> Order entered, oldest first
								<?php } ?><br/>	
								<?php if (get_option('sorder') == 'testid DESC') { ?>
								<input type="radio" name="sorder" value="testid DESC" checked /> Order entered, newest first
								<?php } else { ?>
								<input type="radio" name="sorder" value="testid DESC" /> Order entered, newest first
								<?php } ?><br/>
								<?php if (get_option('sorder') == 'storder ASC') { ?>
								<input type="radio" name="sorder" value="storder ASC" checked /> User defined sort order
								<?php } else { ?>
								<input type="radio" name="sorder" value="storder ASC" /> User defined sort order
								<?php } ?>
							</td>
					</tr>

			<tr valign="top">
			<td>Align Artwork on Left or Right of Description</td>
			<td>
			<?php $sfs_imgalign = get_option('aho_imgalign'); 
			if ($sfs_imgalign == 'alignleft') { ?>
			<input type="radio" name="aho_imgalign" value="alignleft" checked /> Left 
			<input type="radio" name="aho_imgalign" value="alignright" /> Right
			<?php } elseif ($sfs_imgalign == 'alignright') { ?>
			<input type="radio" name="aho_imgalign" value="alignleft" /> Left
			<input type="radio" name="aho_imgalign" value="alignright" checked/> Right
			<?php } else { ?>
			<input type="radio" name="aho_imgalign" value="alignleft" /> Left
			<input type="radio" name="aho_imgalign" value="alignright" /> Right
			<?php } ?>
			</td>
			</tr>

			<tr valign="top">
			<td>Display Images or Images and Summary</td>
			<td>
			<?php $aho_display = get_option('imgdisplay'); 
			if ($aho_display == 'displayimg') { ?>
			<input type="radio" name="imgdisplay" value="displayimg" checked /> Images
			<input type="radio" name="imgdisplay" value="displaysummary" /> Images & Summary
			<?php } elseif ($aho_display == 'displaysummary') { ?>
			<input type="radio" name="imgdisplay" value="displayimg" /> Images
			<input type="radio" name="imgdisplay" value="displaysummary" checked/> Images & Summary
			<?php } else { ?>
			<input type="radio" name="imgdisplay" value="displayimg" /> Images
			<input type="radio" name="imgdisplay" value="displaysummary" /> Images & Summary
			<?php } ?>
			</td>
			</tr>

			<tr valign="top">
			<td>Maximum height (in pixels) for image</td>
			<td><input type="text" name="imgmax" value="<?php echo get_option('imgmax'); ?>" /> (if left blank images will show full size)</td>
			</tr>
			
			<tr valign="top">
			<td>Remove table when deactivating plugin</td>
			<td>
			<?php $deldata = get_option('deldata'); 
			if ($deldata == 'yes') { ?>
			<input type="checkbox" name="deldata" value="yes" checked /> (this will result in all data being deleted!)
			<?php } else { ?>
			<input type="checkbox" name="deldata" value="yes" /> (this will result in all data being deleted!)
			<?php } ?>
			</td>
			</tr>
			
			</table>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="sorder,aho_imgalign,imgmax,deldata" />
			
			<p class="submit">
			<input type="submit" class="button button-primary" value="<?php _e('Save Page Changes') ?>" />
			</p>
			
			</form>
			
			</div>
		<?php 
		}

// +---------------------------------------------------------------------------+
// | Manage Page - list all and show edit/delete options                       |
// +---------------------------------------------------------------------------+
/* show podcast on settings page */
		function showlist() { 
			global $wpdb;
			$table_name = $wpdb->prefix . "aho";
			$aholists = $wpdb->get_results("SELECT testid,show_name,host_name,show_url,imgurl,episode FROM $table_name");

			foreach ($aholists as $aholist) {
				echo '<div class="podcast-display">';
				echo '<img src="'.$aholist->imgurl.'" width="100px" class="alignleft" style="margin:0 10px 10px 0;">';
				echo '<a href="admin.php?page=setting_page&amp;mode=ahoedit&amp;testid='.$aholist->testid.'">Edit</a>';
				echo '&nbsp;|&nbsp;';
				echo '<a href="admin.php?page=setting_page&amp;mode=ahorem&amp;testid='.$aholist->testid.'" onClick="return confirm(\'Delete this podcast?\')">Delete</a>';
				echo '<br>';
				echo '<strong>Show Name: </strong>';
				echo stripslashes($aholist->show_name);
					if ($aholist->host_name != '') {
						echo '<br><strong>Host Name: </strong>'.stripslashes($aholist->host_name).'';
						if ($aholist->show_url != '') {
							echo '<br><strong>Show URL: </strong> <a href="'.$aholist->show_url.'" rel="wordbreak">'.stripslashes($aholist->show_url).'</a> ';
							if ($aholist->episode !=''){
							echo '<br><strong>Episode: </strong>'.stripslashes($aholist->episode).'';	
							}	
						}
					}
				echo '</div>'; 
			}
			echo '<div class="aho-clear"></div>';
		}

		/* edit podcast form */
		function aho_edit($testid){
			global $wpdb;
			$table_name = $wpdb->prefix . "aho";
			
			$getaho = $wpdb->get_row("SELECT testid, show_name, host_name, show_url, imgurl, episode, excerpt, storder FROM $table_name WHERE testid = $testid"); ?>
			
			<h3>Edit Podcast</h3
			<div id="ppg-form">
				<?php echo '<form name="edittst" method="post" action="admin.php?page=setting_page">';?>
					<table cellpadding="2" cellspacing="2">
						<tr valign="top">	
							<td><label for="show_name">Show Name:</label></td>
				  			<?php echo '<td><input name="show_name" type="text" size="45" value="'. stripslashes($getaho->show_name).'"></td>';
				  		?></tr>
				  		<tr valign="top">
							<td><label for="host_name">Host Name:</label></td>
				  			<td><input name="host_name" type="text" size="45" value="<?php echo stripslashes($getaho->host_name)?>"></td>
						</tr>

						<tr valign="top">
							<td><label for="show_url">Show URL:</label></td>
				 			<td><input name="show_url" type="text" size="45" value="<?php echo $getaho->show_url ?>"></td>
				 		</tr>
				
						<tr valign="top">
							<td><label for="imgurl">Image URL:</label></td>
							<td><input name="imgurl" type="text" size="45" value="<?php echo $getaho->imgurl ?>"><input class="media-upload-button button" type="button" value="Upload Image" /></td>
						</tr>
						<tr valign="top">
								<td><label for="imgurl">&nbsp;</label></td>
								<td><strong>For grayscale transition to work, album art must be on same server as website</strong></td>
							</tr>
						
						<tr valign="top">
							<td><label for="episode">Episode:</label></td>
				 			<td><input name="episode" type="text" size="2" value="<?php echo $getaho->episode ?>"></td>
				 		</tr>

				 		<tr valign="top">
				 			<td><label for="excerpt">Show Recap:</label></td>
				  			<td><textarea name="excerpt" cols="45" rows="7"><?php echo stripslashes($getaho->excerpt) ?></textarea></td>
				  		</tr>

				  		<tr valign="top">
							<td><label for="storder">Sort order:</label></td>
				 			<td><input name="storder" type="text" size="2" value="<?php echo $getaho->storder ?>">(optional)</td>
				 		</tr>

				 		<tr valign="top">
				  			<?php echo'<td><input type="hidden" name="testid" value="'.$getaho->testid.'"></td>'; ?>
				  			<td><input name="editdo" type="submit" class="button button-primary" value="Update"></td>
				  		</tr>
				  	</table>

			<?php echo '<h3>Preview</h3>';
			echo '<div class="podcast-display" >';
			echo '<img src="'.$getaho->imgurl.'" width="90px" class="alignleft" style="margin:0 10px 10px 0;">';
				echo '<strong>Show Name: </strong>';
				echo stripslashes($getaho->show_name);
					if ($getaho->host_name != '') {
						echo '<br><strong>Host Name: </strong>'.stripslashes($getaho->host_name).'';
						if ($getaho->show_url != '') {
							echo '<br><strong>Show URL: </strong> <a href="'.$getaho->show_url.'">'.stripslashes($getaho->show_url).'</a> ';
							if ($getaho->episode !=''){
							echo '<br><strong>Episode: </strong>'.stripslashes($getaho->episode).'';	
							}	
							if ($getaho->excerpt !=''){
							echo '<br><strong>Show Recap: </strong>'.stripslashes($getaho->excerpt).'';	
							}
						}
					}
				echo '</div>'; 
			echo '</form>';
			echo '</div>';
		}

// +---------------------------------------------------------------------------+
// | Show podcasts on page with shortcode [aho]					               |
// +---------------------------------------------------------------------------+
		/* show page of all podcast artwork */
		function showall() {
		global $wpdb;
			$imgalign = get_option('aho_imgalign');
			if ($imgalign == '') { $imgalign = 'alignright'; } else { $imgalign = get_option('aho_imgalign'); }

			$sorder = (get_option('sorder'));
			if ($sorder != 'testid ASC' AND $sorder != 'testid DESC' AND $sorder != 'storder ASC')
			{ $sorder2 = 'testid ASC'; } else { $sorder2 = $sorder; }
			
			$table_name = $wpdb->prefix . "aho";
			$tstpage = $wpdb->get_results("SELECT testid, show_name, host_name, show_url, imgurl, episode, excerpt, storder FROM $table_name WHERE imgurl !='' ORDER BY $sorder2");
			$retvalo = '';
			$retvalo .= '';
			$retvalo .= '<div id="aho-page">';
			$retvalo .= '<div class="item">';
			foreach ($tstpage as $tstpage2) {
				$imgdisplay = get_option('imgdisplay');
				if ($imgdisplay == 'displaysummary'){
					if ($tstpage2->imgurl != '') { // don't show podcasts without album art.

						
						if ($tstpage2->imgurl != '') { // check for image
							$imgmax = get_option('imgmax');
							if ($imgmax == '') { $sfiheight = ''; } else { $sfiheight = ' width="'.get_option('imgmax').'"'; }
							$retvalo .= '<a href="'.$tstpage2->show_url.'" target="_blank"><img src="'.$tstpage2->imgurl.'"'.$sfiheight.' class="'.$imgalign.'" alt="'.stripslashes($tstpage2->show_name).'"></a>';
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
							$retvalo .= '<div class="aho-clear"><hr></div>';
					}
				}
				else {
					if ($tstpage2->imgurl != '') { // don't show podcasts without album art.

						
						if ($tstpage2->imgurl != '') { // check for image
							$imgmax = get_option('imgmax');
							if ($imgmax == '') { $sfiheight = ''; } else { $sfiheight = ' width="'.get_option('imgmax').'"'; }
							
							$retvalo .= '<a href="'.$tstpage2->show_url.'" target="_blank"><img src="'.$tstpage2->imgurl.'"'.$sfiheight.' class="grid" alt="'.stripslashes($tstpage2->show_name).'"></a>';
							// $retvalo .= '</div>';
						}
						
							//$retvalo .= '<div class="clear"></div>';
					}

				}
			}
			$retvalo .= '</div></div>';
		return $retvalo;
		}


// +---------------------------------------------------------------------------+
// | Uninstall plugin                                                          |
// +---------------------------------------------------------------------------+

		function deactivate () {
			global $wpdb;

			$table_name = $wpdb->prefix . "aho";

			$aho_deldata = get_option('aho_deldata');
			if ($aho_deldata == 'yes') {
				$wpdb->query("DROP TABLE {$table_name}");
				delete_option("aho_showlink");
				delete_option("aho_linktext");
				delete_option("aho_linkurl");
				delete_option("aho_deldata");
				delete_option("aho_setlimit");
				delete_option("aho_admng");
				delete_option("aho_sorder");
				delete_option("aho_imgalign");
				delete_option("aho_imgmax");
		 	}
		    delete_option("aho_version");
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
			$this->WP_Widget('podcasts', __('As Heard On'), $widget_ops);
		}
	 
		// Display Widget
		function widget($args, $instance) {
			extract($args);
			$title = esc_attr($instance['title']);
	
			echo $before_widget.$before_title.$title.$after_title;
	
				$this->onerandom();
	
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
	 
		// Display Widget Control Form
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

// +---------------------------------------------------------------------------+
// | Sidebar - show random podcast(s) in sidebar                               |
// +---------------------------------------------------------------------------+

/* show random testimonial(s) in sidebar */
		function onerandom() {
			global $wpdb;
			$table_name = $wpdb->prefix . "aho";
			if (get_option('setlimit') == '') {
				$setlimit = 1;
			} else {
				$setlimit = get_option('setlimit');
			}
			$randone = $wpdb->get_results("SELECT show_name, show_url, episode, imgurl FROM $table_name WHERE show_url !='' order by RAND() LIMIT $setlimit");

			echo '<div id="sfstest-sidebar">';
			
			foreach ($randone as $randone2) {
				echo '<div class="item-gray">';
				echo '<a href="'.nl2br(stripslashes($randone2->show_url)).'" target="_blank"><img title="'.$randone2->show_name.'"src="'.$randone2->imgurl.'" width="'.get_option('image_width').'" height="'.get_option('image_height').'" style="margin-right:10px;"></a>';
				echo '</div>';
			} // end loop
			$showlink = get_option('showlink');
			$linktext = get_option('linktext');
			$linkurl = get_option('linkurl');
			
			if (($showlink == 'yes') && ($linkurl !='')) {
				if ($linktext == '') { $linkdisplay = 'Read More'; } else { $linkdisplay = $linktext; }
				echo '<div class="ppgreadmore" ><a class="aho-button" href="'.$linkurl.'">'.$linkdisplay.'</a></div>';
			}
			echo '<div class="aho-clear"></div>';
			echo '</div>';
		}
}

// +---------------------------------------------------------------------------+
// | Function: Init WP-Testimonials  Widget                                    |
// +---------------------------------------------------------------------------+
	add_action('widgets_init', 'widget_aho_init');
	function widget_aho_init() {
		register_widget('aho_widget');
	}