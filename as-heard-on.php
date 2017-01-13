<?php
/*
Plugin Name: As Heard On
Plugin URI: http://YourWebsiteEngineer.com
Description: Lets you display album artwork of podcasts you've been a guest on.  Widget included.  Optional link in sidebar block to "view all" podcast images on a page.
Version: 1.15
Author: Dustin Hartzler
Author URI: http://YourWebsiteEngineer.com
*/


if ( !class_exists('AsHeardOn') ) {
    class AsHeardOn {
    	//public $wpdb;
    	public $allowed_html = array(
			    'a' => array(
			        'href' => array(),
			        'title' => array()
			    ),
			    'br' => array(),
			    'em' => array(),
			    'strong' => array()
			);
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
			// register_setting( 'option-widget', 'admng' );
			// register_setting( 'option-widget', 'showlink' );
			// register_setting( 'option-widget', 'linktext' );
			// register_setting( 'option-widget', 'image_width');
			// register_setting( 'option-widget', 'image_height');
			// register_setting( 'option-widget', 'opacity');
			// register_setting( 'option-widget', 'setlimit' );
			// register_setting( 'option-widget', 'linkurl' );
			// register_setting( 'option-page', 'aho_imgalign' );
			// register_setting( 'option-page', 'imgdisplay' );
			// register_setting( 'option-page', 'imgmax' );
			// register_setting( 'option-page', 'sorder' );
			// register_setting( 'option-page', 'deldata' );
      		register_setting( 'aho_settings_widget', 'aho_widget' );
      		register_setting( 'aho_settings_page', 'aho_page' );
			register_setting( 'dustin_test_settings', 'dustin_widget');
		}

		function addscripts() { // include style sheet
      		wp_enqueue_style('style_css', plugins_url('/as-heard-on/css/style.css'), '0.1' );
			wp_enqueue_script( 'jquery' );
		  	wp_enqueue_script( 'grayscale', plugins_url('/as-heard-on/js/grayscale.js') ,array('jquery') );
		  	$params = array('opacity_js' => get_option('opacity') );
		  	wp_localize_script( 'grayscale', 'grayscale_vars', $params );
			if ( is_admin() )  {
        		wp_enqueue_script( 'display', plugins_url('/as-heard-on/js/display.js') ,array('jquery') );
			  	wp_enqueue_script( 'slider', plugins_url('/as-heard-on/js/simple-slider.js') ,array('jquery') );

			  	// Upload Button to Work
	 			wp_enqueue_script('thickbox');
	 			wp_enqueue_style ('thickbox');
	 			wp_enqueue_script('script', plugins_url('/js/upload-media.js', __FILE__), array('jquery'), '', true);
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
          			<div align="right" class="help" >Need help? <a href="http://wordpress.org/plugins/as-heard-on/" target="_blank">documentation</a> &nbsp;|&nbsp; <a href="http://wordpress.org/support/plugin/as-heard-on" target="_blank">support page</a></div>
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
				<?php $this->displayForm();?>
			</div>
		<?php }

/* Display Form */

		function displayForm(){
			?>
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
								<td><input name="imgurl" type="text" size="45" ><input class="upload_image_button button" type="button" value="Upload Image" /></td>
							</tr>
							<tr valign="top">
								<td><label for="imgurl">&nbsp;</label></td>
								<td><strong>For grayscale transition to work, album art must be uploaded to media library</strong></td>
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
			<?php
		}

/* insert podcast into DB */
		function insertnew() {
			global $wpdb;

			$table_name = $wpdb->prefix . "aho";
			$show_name 	= sanitize_text_field( $_POST['show_name'] );
			$host_name 	= sanitize_text_field( $_POST['host_name'] );
			$show_url 	= sanitize_text_field( $_POST['show_url'] );
			$imgurl 	= sanitize_text_field( $_POST['imgurl'] );
			$episode 	= sanitize_text_field( $_POST['episode'] );
			$excerpt 	= wp_kses( $_POST['excerpt'], $this->allowed_html );
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
			if(is_admin()){require_once('legacy.php');};

   			$table_name = $wpdb->prefix . "aho";
        	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
          		if ( $wpdb->supports_collation() ) {
  					if ( ! empty($wpdb->charset) )
  						$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
  					if ( ! empty($wpdb->collate) )
  						$charset_collate .= " COLLATE $wpdb->collate";
  				}

          		$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . "(
					aho_id int( 15 ) NOT NULL AUTO_INCREMENT ,
					show_name text,
					host_name text,
					show_url text,
					episode text,
					imgurl text,
					excerpt text,
					storder INT( 5 ) NOT NULL,
					PRIMARY KEY ( `aho_id` )
					) ".$charset_collate.";";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);

			   	$insert = "INSERT INTO " . $table_name .
		           	" (show_name,host_name,show_url,episode,imgurl) " .
		            "VALUES ('Your Website Engineer','Dustin Hartzler','http://YourWebsiteEngineer.com','001','http://YourWebsiteEngineer.com/wp-content/podcasts/YWE.png')";
		      	$results = $wpdb->query( $insert );


				// $defaults = array(
				//   'image_width'  => '50',
				//   'image_height' => '50',
				//   'opacity'		 => '1.5',
				//   'setlimit'	 => '3'
				// );
				// $options = wp_parse_args(get_option('aho_widget'), $defaults);
				//
				// $aho_widget = array(
				// 	'image_width'  => '50',
				// 	'image_height' => '50',
				// 	'opacity'	   => '1.5',
				// 	'setlimit'	   => '3'
		        // );
				//
				// 	  	add_option('dustin_widget',  $aho_widget );
			}
		}

		/* update item in DB */
		function aho_editdo($aho_id){
			global $wpdb;

			$table_name = $wpdb->prefix . "aho";

			$aho_id = $aho_id;
			$show_name 	= sanitize_text_field( $_POST['show_name'] );
			$host_name 	= sanitize_text_field( $_POST['host_name'] );
			$show_url 	= sanitize_text_field( $_POST['show_url'] );
			$imgurl 	= sanitize_text_field( $_POST['imgurl'] );
			$episode 	= sanitize_text_field( $_POST['episode'] );
			$excerpt 	= wp_kses( $_POST['excerpt'], $this->allowed_html );
			$storder 	= sanitize_text_field( $_POST['storder'] );

			$wpdb->query("UPDATE " . $table_name .
			" SET show_name = '$show_name', ".
			" host_name = '$host_name', ".
			" show_url = '$show_url', ".
			" imgurl = '$imgurl', ".
			" episode = '$episode', ".
			" excerpt = '$excerpt', ".
			" storder = '$storder' ".
			" WHERE aho_id = '$aho_id'");
		}

		/* delete testimonials from DB */
		function removetst($aho_id) {
			global $wpdb;
			$table_name = $wpdb->prefix . "aho";

			$insert = $wpdb->prepare( "DELETE FROM " . $table_name .
			" WHERE aho_id = '%d'", absint( $aho_id ) );

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
					$this->newform();
				}
				elseif ($_REQUEST['mode']=='ahorem') {
					$this->removetst($_REQUEST['aho_id']);
					?><div id="message" class="updated fade"><p><strong><?php _e('Podcast Deleted'); ?>.</strong></p></div><?php
				}
				elseif ($_REQUEST['mode']=='ahoedit') {
					$this->aho_edit($_REQUEST['aho_id']);
					//exit;
				}
				elseif (isset($_REQUEST['editdo'])) {
					$this->aho_editdo($_REQUEST['aho_id']);
					?><div id="message" class="updated fade"><p><strong><?php _e('Podcast Updated'); ?>.</strong></p></div><?php
					$this->showlist(); // show podcasts
					$this->newform(); // show form to add new podcast
				}
				else { ?>
					<div class="wrap">
				<?php $this->showlist(); // show podcasts
				 $this->newform(); // show form to add new podcast ?>
			</div> <?php
				}
				?>
			</div>


<?php $this->footerText();

}

  function footerText () { ?>
    <div class="wrap">
		<div class="aho-clear"></div>
      	<p>As Heard On Plugin is &copy; Copyright <?php echo("2013 - ".date('Y').""); ?>, <a href="http://www.yourwebsiteengineer.com/" target="_blank">Dustin Hartzler</a> and distributed under the <a href="http://www.fsf.org/licensing/licenses/quick-guide-gplv3.html" target="_blank">GNU General Public License</a>.
      	If you find this plugin useful, please consider a <a href="http://#" target="_blank">donation</a>.</p>
    </div><?php
  }

// +---------------------------------------------------------------------------+
// | Configuration options                                                     |
// +---------------------------------------------------------------------------+
		function widget_options() {
      		$options =   get_option('aho_widget');
			?>
			<div class="wrap">
				<?php if ($_REQUEST['settings-updated']=='true') { ?>
					<div id="message" class="updated fade"><p><strong>Widget Settings Updated</strong></p></div>
				<?php } ?>

				<form method="post" action="options.php">
				<?php settings_fields( 'aho_settings_widget' ); ?>

				<table cellpadding="5" cellspacing="5">
					<tr valign="top">
            			<td><label for="aho_widget[showlink]"><?php _e( 'Show link in sidebar to full page of previous interviews' ); ?></label></td>
						<td><input class="checkbox" type="checkbox" id="aho_widget[showlink]" name="aho_widget[showlink]" value="1" <?php checked( 1, isset( $options['showlink'] ) ); ?>/></td>
					</tr>

					<tr valign="top">
            			<td><label for="aho_widget[linktext]"><?php _e( 'Text for sidebar aho-button (Read More, View All, etc)' ); ?></label></td>
						<td><input type="text" id="aho_widget[linktext]" name="aho_widget[linktext]" value="<?php if( isset( $options['linktext'] ) ) { echo esc_attr( $options['linktext'] ); } ?>" /></td>
					</tr>

					<tr valign="top">
            			<td><label for="aho_widget[linkurl]"><?php _e( 'Page link for sidebar aho-button<br/> (use shortcode [aho])' ); ?></label></td>
						<td> <select name="aho_widget[linkurl]">
			 			<option value="">
						<?php echo esc_attr(__('Select page')); ?></option>
						<?php
						  $pages = get_pages();
						  foreach ($pages as $pagg) {
  						  $pagurl = get_page_link($pagg->ID);
  						  $sfturl = $options['linkurl'];
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
            			<td><label for="aho_widget[image_width]"><?php _e( 'Image Width' ); ?></label></td>
            			<td><input type="text" id="aho_widget[image_width]" name="aho_widget[image_width]" size="3" value="<?php if( isset( $options['image_width'] ) ) { echo esc_attr( $options['image_width'] ); } ?>" /><label> (pixels)</label></td>
					</tr>

					<tr valign="top">
            			<td><label for="aho_widget[image_height]"><?php _e( 'Image Height' ); ?></label></td>
            			<td><input type="text" id="aho_widget[image_height]" name="aho_widget[image_height]" size="3" value="<?php if( isset( $options['image_height'] ) ) { echo esc_attr( $options['image_height'] ); } ?>" /><label> (pixels)</label></td>
          			</tr>

					<tr valign="top">
            			<td><label for="aho_widget[opacity]"><?php _e( 'How fast to transition from B&W to Color' ); ?></label></td>
						<td><input type="text" data-slider="true" data-slider-range="0,5" data-slider-step=".25" data-slider-highlight="true" data-slider-theme="volume" name="aho_widget[opacity]" value="<?php if( isset( $options['opacity'] ) ) { echo esc_attr( $options['opacity'] ); } ?>" ></td>
					</tr>

					<tr valign="top">
            			<td><label for="aho_widget[setlimit]"><?php _e( 'Number of podcasts to show in sidebar' ); ?></label></td>
            			<td><input type="text" id="aho_widget[setlimit]" name="aho_widget[setlimit]" size="2" value="<?php if( isset( $options['setlimit'] ) ) { echo esc_attr( $options['setlimit'] ); } ?>" /></td>
					</tr>

			</table>
			<p class="submit">
			<input type="submit" class="button button-primary" value="<?php _e('Save Widget Options') ?>" />
			</p>

		<?php
      $this->footerText();
  }

		function page_options(){
      		$options =   get_option('aho_page'); ?>
			<div class="wrap">
				<?php if ($_REQUEST['settings-updated']=='true') { ?>
					<div id="message" class="updated fade"><p><strong>Page Settings Updated</strong></p></div>
				<?php  } ?>

				<form method="post" action="options.php">
					<?php settings_fields( 'aho_settings_page' ); ?>
					<table cellpadding="5" cellspacing="5">
						<tr valign="top">
							<td><label for="aho_page[sorder]"><?php _e( 'Sort podcasts on page by' ); ?></label></td>
							<td>
								<?php $aho_sorder = $options['sorder'];
								if ($aho_sorder == 'asc') { ?>
									<input type="radio" name="aho_page[sorder]" value="asc" checked /><label for="aho_page[sorder]"><?php _e( 'Order entered, oldest first' ); ?></label><br>
									<input type="radio" name="aho_page[sorder]" value="desc"  /><label for="aho_page[sorder]"><?php _e( 'Order entered, newest first' ); ?></label><br>
									<input type="radio" name="aho_page[sorder]" value="user" /><label for="aho_page[sorder]"><?php _e( 'User defined sort order' ); ?></label>
								<?php } elseif ($aho_sorder == 'desc') { ?>
									<input type="radio" name="aho_page[sorder]" value="asc" /><label for="aho_page[sorder]"><?php _e( 'Order entered, oldest first' ); ?></label><br>
									<input type="radio" name="aho_page[sorder]" value="desc" checked  /><label for="aho_page[sorder]"><?php _e( 'Order entered, newest first' ); ?></label><br>
									<input type="radio" name="aho_page[sorder]" value="user" /><label for="aho_page[sorder]"><?php _e( 'User defined sort order' ); ?></label>
								<?php } elseif ($aho_sorder == 'user') { ?>
									<input type="radio" name="aho_page[sorder]" value="asc" /><label for="aho_page[sorder]"><?php _e( 'Order entered, oldest first' ); ?></label><br>
									<input type="radio" name="aho_page[sorder]" value="desc"  /><label for="aho_page[sorder]"><?php _e( 'Order entered, newest first' ); ?></label><br>
									<input type="radio" name="aho_page[sorder]" value="user" checked /><label for="aho_page[sorder]"><?php _e( 'User defined sort order' ); ?></label>
								<?php } else { ?>
									<input type="radio" name="aho_page[sorder]" value="asc" /><label for="aho_page[sorder]"><?php _e( 'Order entered, oldest first' ); ?></label><br>
									<input type="radio" name="aho_page[sorder]" value="desc"  /><label for="aho_page[sorder]"><?php _e( 'Order entered, newest first' ); ?></label><br>
									<input type="radio" name="aho_page[sorder]" value="user" /><label for="aho_page[sorder]"><?php _e( 'User defined sort order' ); ?></label>
								<?php } ?>
							</td>
					</tr>

			<tr valign="top">
        		<td><label for="aho_page[imgalign]"><?php _e( 'Align Artwork on Left or Right of Description' ); ?></label></td>
			  	<td><?php $aho_imgalign = $options['imgalign'];
			    	if ($aho_imgalign == 'alignleft') { ?>
			        	<input type="radio" name="aho_page[imgalign]" value="alignleft" checked /> Left
			        	<input type="radio" name="aho_page[imgalign]" value="alignright" /> Right
			     	<?php } elseif ($aho_imgalign == 'alignright') { ?>
			        	<input type="radio" name="aho_page[imgalign]" value="alignleft" /> Left
			        	<input type="radio" name="aho_page[imgalign]" value="alignright" checked/> Right
					<?php } else { ?>
						<input type="radio" name="aho_page[imgalign]" value="alignleft" /> Left
						<input type="radio" name="aho_page[imgalign]" value="alignright" /> Right
					<?php } ?>
			    </td>
			</tr>

			<tr valign="top">
        		<td><label for="aho_page[imgdisplay]"><?php _e( 'Display Images or Images and Summary' ); ?></label></td>
			  	<td><?php $aho_display = $options['imgdisplay'];
			    	if ($aho_display == 'displayimg') { ?>
              			<input type="radio" name="aho_page[imgdisplay]" value="displayimg" checked /> Images
			        	<input type="radio" name="aho_page[imgdisplay]" value="displaysummary" /> Images & Summary
			     	<?php } elseif ($aho_display == 'displaysummary') { ?>
			        	<input type="radio" name="aho_page[imgdisplay]" value="displayimg" /> Images
			        	<input type="radio" name="aho_page[imgdisplay]" value="displaysummary" checked/> Images & Summary
			     	<?php } else { ?>
			        	<input type="radio" name="aho_page[imgdisplay]" value="displayimg" /> Images
			        	<input type="radio" name="aho_page[imgdisplay]" value="displaysummary" /> Images & Summary
			    	<?php } ?>
			  	</td>
			</tr>

			<tr valign="top">
        		<td><label for="aho_page[imgmax]"><?php _e( 'Maximum height (in pixels) for image' ); ?></label></td>
        		<td><input type="text" id="aho_page[imgmax]" name="aho_page[imgmax]" size="3" value="<?php if( isset( $options['imgmax'] ) ) { echo esc_attr( $options['imgmax'] ); } ?>" /><label><?php _e( ' (if left blank images will show full size)' ); ?></label></td>
			</tr>

			<tr valign="top">
        		<td><label for="aho_page[deldata]"><?php _e( 'Remove table when deactivating plugin' ); ?></label></td>
        		<td><input class="checkbox" type="checkbox" id="aho_page[deldata]" name="aho_page[deldata]" value="1" <?php checked( 1, isset( $options['deldata'] ) ); ?>/><label> (this will result in all data being deleted!)</label></td>
			<td>

			</table>
				<p class="submit">
					<input type="submit" class="button button-primary" value="<?php _e('Save Page Options') ?>" />
				</p>

			</form>

			</div>
		<?php $this->footerText();
		}

// +---------------------------------------------------------------------------+
// | Manage Page - list all and show edit/delete options                       |
// +---------------------------------------------------------------------------+
/* show podcast on settings page */
		function showlist() {
			global $wpdb;
			$table_name = $wpdb->prefix . "aho";
			$aholists = $wpdb->get_results("SELECT aho_id,show_name,host_name,show_url,imgurl,episode FROM $table_name");

			foreach ($aholists as $aholist) {
				echo '<div class="podcast-display">';
				echo '<img src="'.$aholist->imgurl.'" width="100px" class="alignleft">';
				echo '<a href="admin.php?page=setting_page&amp;mode=ahoedit&amp;aho_id='.$aholist->aho_id.'">Edit</a>';
				echo '&nbsp;|&nbsp;';
				echo '<a href="admin.php?page=setting_page&amp;mode=ahorem&amp;aho_id='.$aholist->aho_id.'" onClick="return confirm(\'Delete this podcast?\')">Delete</a>';
				echo '<br>';
				echo '<strong>Show Name: </strong>';
				echo stripslashes($aholist->show_name);
					if ($aholist->host_name != '') {
						echo '<br><strong>Host Name: </strong>'.stripslashes($aholist->host_name).'';
					}
					if ($aholist->show_url != '') {
						echo '<br><strong>Show URL: </strong> <a href="'.$aholist->show_url.'" rel="wordbreak">'.stripslashes($aholist->show_url).'</a> ';
					}
					if ($aholist->episode !=''){
						echo '<br><strong>Episode: </strong>'.stripslashes($aholist->episode).'';
					}
				echo '</div>';
			}
			echo '<div class="aho-clear"></div>';
		}

		/* edit podcast form */
		function aho_edit($aho_id){
			global $wpdb;
			$table_name = $wpdb->prefix . "aho";

			$getaho = $wpdb->get_row("SELECT aho_id, show_name, host_name, show_url, imgurl, episode, excerpt, storder FROM $table_name WHERE aho_id = $aho_id"); ?>

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
							<td><input name="imgurl" type="text" size="45" value="<?php echo $getaho->imgurl ?>"><input class="upload_image_button button" type="button" value="Upload Image" /></td>
						</tr>
						<tr valign="top">
								<td><label for="imgurl">&nbsp;</label></td>
								<td><strong>For grayscale transition to work, album art must be uploaded to media library</strong></td>
							</tr>

						<tr valign="top">
							<td><label for="episode">Episode:</label></td>
				 			<td><input name="episode" type="text" size="4" value="<?php echo $getaho->episode ?>"></td>
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
				  			<?php echo'<td><input type="hidden" name="aho_id" value="'.$getaho->aho_id.'"></td>'; ?>
				  			<td><input name="editdo" type="submit" class="button button-primary" value="Update Podcast"></td>
				  		</tr>
				  	</table>

			<?php echo '<h3>Preview</h3>';
			//$this->showlist();
			echo '<div class="podcast-display" >';
			echo '<img src="'.$getaho->imgurl.'" width="90px" class="alignleft">';
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
			if ($sorder != 'aho_id ASC' AND $sorder != 'aho_id DESC' AND $sorder != 'storder ASC')
			{ $sorder2 = 'aho_id ASC'; } else { $sorder2 = $sorder; }

			$table_name = $wpdb->prefix . "aho";
			$tstpage = $wpdb->get_results("SELECT aho_id, show_name, host_name, show_url, imgurl, episode, excerpt, storder FROM $table_name WHERE imgurl !='' ORDER BY $sorder2");
			$retvalo = '';
			$retvalo .= '';
			$retvalo .= '<div id="aho-page">';
			$retvalo .= '<div class="podcast-item">';
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
      		$options =   get_option('aho_page');

			$table_name = $wpdb->prefix . "aho";

			$aho_deldata =  $options['deldata'];
			if ($aho_deldata == '1') {
				$wpdb->query("DROP TABLE {$table_name}");
				delete_option("aho_widget");
				delete_option("aho_page");
				delete_option("aho_version");
		 	}
		}
	}
}

if(class_exists('AsHeardOn')) {
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('AsHeardOn', 'activate'));
	register_uninstall_hook(__FILE__, array('AsHeardOn', 'deactivate'));

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
      $options =   get_option('aho_widget');
      //settings_fields( 'aho_settings_group' );
			if (get_option('setlimit') == '') {
				$setlimit = 1;
			} else {
				$setlimit = get_option('setlimit');
			}
			$randone = $wpdb->get_results("SELECT show_name, show_url, episode, imgurl FROM $table_name WHERE show_url !='' order by RAND() LIMIT $setlimit");

			echo '<div id="sfstest-sidebar">';

			foreach ($randone as $randone2) {
				echo '<div class="item-gray">';
				echo '<a href="'.nl2br(stripslashes($randone2->show_url)).'" target="_blank"><img title="'.$randone2->show_name.'"src="'.$randone2->imgurl.'" width="'.$options['image_width'].'" height="'.$options['image_height'].'" style="margin-right:10px;"></a>';
				echo '</div>';
			} // end loop

      		$aho_showlink  = $options['showlink'];
			$aho_linktext  = $options['linktext'];
			$linkurl       = $options['linkurl'];

			if (($aho_showlink == '1') && ($linkurl !='')) {
				if ($aho_linktext == '') { $linkdisplay = 'Read More'; } else { $linkdisplay = $aho_linktext; }
        			echo '<div class="aho-clear"></div>';
        			echo '<a class="aho-button" href="'.$linkurl.'">'.$linkdisplay.'</a>';
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
