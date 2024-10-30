<?php
/*
Plugin Name: CigiCigi Post Guest
Plugin URI:  http://www.cigicigi.co/cigicigi-post-guest.html
Description: Plugin for guest post.
Author: CigiCigi Online iLk3r
Author URI: http://ilkerozcan.com.tr
Version: 1.0.5
License: GPLv2 or later
*/
/*  Copyright 2011  CigiCigi Online  ( info@cigicigi.co )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$SID = session_id(); 
if(empty($SID))
{
	session_start();
}

global $cigicig_post_guest_jal_db_version;
$cigicig_post_guest_jal_db_version = '1.0.4';

if( ! function_exists( 'cigicigi_post_guest_install' ) ) {
	function cigicigi_post_guest_install () {
		global $wpdb;
		global $cigicig_post_guest_jal_db_version;

		$table_name = $wpdb->prefix . "cigicigi_post_guest";
   
		$sql = "CREATE TABLE " . $table_name . " (
			ID bigint(20) NOT NULL AUTO_INCREMENT,
			yazar_ad varchar(60) NOT NULL,
			mail varchar(120) NOT NULL,
			post_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			post_title text NOT NULL,
			post_slugs text NOT NULL,
			post_content longtext NOT NULL,
			cat_id bigint(20) NOT NULL,
			media_id text NOT NULL,
			PRIMARY KEY ( ID )
		);";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		$cigicigi_post_guest_media_dir	= __DIR__ . '/../../uploads';
		if (!is_dir($cigicigi_post_guest_media_dir.'/cigicigi_post_guest_media')) {
			mkdir($cigicigi_post_guest_media_dir.'/cigicigi_post_guest_media', 0770);
			mkdir($cigicigi_post_guest_media_dir.'/cigicigi_post_guest_media/temp', 0770);
		}
		
		$cigicigi_post_guest_user_id = username_exists( 'cigicigi_post_guest_user' );
		if ( !$cigicigi_post_guest_user_id ) {
			$random_password = wp_generate_password( 12, false );
			$cigicigi_post_guest_user_id = wp_create_user( 'cigicigi_post_guest_user', $random_password, get_bloginfo('admin_email') );
			$cigicigi_post_guest_user_display_name	= __('Guest', 'cigicigi-post-guest');
			wp_update_user(array('ID' => $cigicigi_post_guest_user_id, 'display_name' => $cigicigi_post_guest_user_display_name)) ;
		}

		if( ! get_option( 'cigicigi_post_guest_db_version' ) ){
			add_option('cigicigi_post_guest_db_version', $cigicig_post_guest_jal_db_version);
		}elseif(get_option( 'cigicigi_post_guest_db_version' ) != $cigicig_post_guest_jal_db_version){
			update_option('cigicigi_post_guest_db_version', $cigicig_post_guest_jal_db_version );
		}
	}
	register_activation_hook(__FILE__,'cigicigi_post_guest_install');
}

if( ! function_exists( 'cigicigi_post_guest_update_db_check' ) ) {
	function cigicigi_post_guest_update_db_check() {
		global $cigicig_post_guest_jal_db_version;
		if (get_option('cigicigi_post_guest_db_version') != $cigicig_post_guest_jal_db_version) {
			cigicigi_post_guest_install();
		}
	}
	add_action('plugins_loaded', 'cigicigi_post_guest_update_db_check');
}

if( ! function_exists( 'cigicigi_post_guest_init' ) ) {
	function cigicigi_post_guest_init() {
		global $cigicigi_post_guest_options;
		global $wpdb;

		$cigicigi_post_guest_default_options = array(
												'cigicigi_post_guest_captcha'			=> 'yes',
												'cigicigi_post_guest_captcha_type'		=> 'cigicigi',
												'cigicigi_post_guest_recaptcha_pubk'	=> '',
												'cigicigi_post_guest_recaptcha_prik'	=> '',
												'cigicigi_post_guest_cigicigi_capt_bg'	=> '/images/arkaplan.png',
												'cigicigi_post_guest_editor'			=> 'bbcode',
												'cigicigi_post_guest_guest_upload_img'	=> 'yes',
												'cigicigi_post_guest_guest_inf_mail'	=> 'yes',
										);
										
				if( ! get_option( 'cigicigi_post_guest_options' ) ){
					add_option( 'cigicigi_post_guest_options', $cigicigi_post_guest_default_options, '', 'yes' );
				}
				if( ! get_option( 'cigicigi_post_guest_count' ) ){
					$table_name = $wpdb->prefix . "cigicigi_post_guest";
					$post_query	= $wpdb->get_results( "SELECT ID FROM ".$table_name );
					$post_count	= count($post_query);
					add_option( 'cigicigi_post_guest_count', $post_count, '', 'yes' );
				}else{
					$table_name = $wpdb->prefix . "cigicigi_post_guest";
					$post_query	= $wpdb->get_results( "SELECT ID FROM ".$table_name );
					$post_count	= count($post_query);
					update_option( 'cigicigi_post_guest_count', $post_count, '', 'yes' );
				}
				$cigicigi_post_guest_options	= get_option( 'cigicigi_post_guest_options' );
	}
	add_action( 'admin_init', 'cigicigi_post_guest_init' );
}

if( ! function_exists( 'dashboard_cigicigi_post_guest' ) ) {
	function dashboard_cigicigi_post_guest() {
		$count = get_option('cigicigi_post_guest_count');
?>
		<p><a href="http://www.cigicigi.co/wordpress-eklentileri" target="_blank"><?php _e('CigiCigi Post Guest Plugins', 'cigicigi-post-guest'); ?></a>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<b><a href="./<?php echo clean_url("admin.php?page=cigicigi_post_guest_posts"); ?>"><?php echo $count; ?></b></a>&nbsp;&nbsp;&nbsp;
		<a class="waiting" href="./<?php echo clean_url("admin.php?page=cigicigi_post_guest_posts"); ?>"><?php _e('Waiting Posts', 'cigicigi-post-guest'); ?></a></p>
<?php
	}
	add_action('activity_box_end', 'dashboard_cigicigi_post_guest');
}

if( ! function_exists( 'cigicigi_post_guest_admin_menu' ) ) {
	function cigicigi_post_guest_admin_menu() {
		$cigicigi_post_guest_count = get_option( 'cigicigi_post_guest_count' );
		add_menu_page(__('CigiCigi Plugins', 'cigicigi-post-guest'), __('CigiCigi Plugins', 'cigicigi-post-guest'), 'edit_pages', 'cigicigi_plugins', 'cigicigi_plugins_menu', WP_CONTENT_URL."/plugins/cigicigi-post-guest/images/cigix16.png", 701); 
		add_submenu_page('cigicigi_plugins', __('CigiCigi Post Guest Options', 'cigicigi-post-guest'), __('CigiCigi Post Guest Options', 'cigicigi-post-guest'), 'activate_plugins', "cigicigi_post_guest_settings", 'cigicigi_post_guest_settings');

		add_submenu_page('cigicigi_plugins', __('Guest Posts', 'cigicigi-post-guest').' ('.$cigicigi_post_guest_count.')', __('Guest Posts', 'cigicigi-post-guest').' ('.$cigicigi_post_guest_count.')', 'edit_pages', "cigicigi_post_guest_posts", 'cigicigi_post_guest_waiting');
	}
	add_action( 'admin_menu', 'cigicigi_post_guest_admin_menu' );
}

if( ! function_exists( 'cigicigi_plugin_header' ) ) {
	function cigicigi_plugin_header() {
		global $post_type;
		?>
		<style>
		#adminmenu #toplevel_page_cg_plugins div.wp-menu-image
		{
			background: url("<?php echo plugins_url( 'images/cigix16.png' , __FILE__ ); ?>") no-repeat scroll center center transparent;
		}
		#adminmenu #toplevel_page_cg_plugins:hover div.wp-menu-image,#adminmenu #toplevel_page_cg_plugins.wp-has-current-submenu div.wp-menu-image
		{
			background: url("<?php echo plugins_url( 'images/cigix16.png' , __FILE__ ); ?>") no-repeat scroll center center transparent;
		}	
		.wrap #icon-options-general.icon32-cg
		{
			background: url("<?php echo plugins_url( 'images/cigix16.png' , __FILE__ ); ?>") no-repeat scroll left top transparent;
		}
		#toplevel_page_cg_plugins .wp-submenu .wp-first-item
		{
			display:none;
		}
		#cg-post-guest-setting td
		{
			margin-left: 20px;
			padding-left: 20px;
			margin-top: 20px;
			padding-top: 20px;
		}
		input.cigicigi_delete_button
		{
			background: #990000;
			color: #fff;
		}
		input.cigicigi_publish_button
		{
			background: #209296;
			color: #fff;		
		}
		#cigicigi_guest_post_read
		{
			border: 1px solid #ccc;
			width: 80%;
			margin-left: 40px;
			padding: 20px;
		}
		</style>
		<script type="text/javascript">
			function Sample_sh(shid){
				var shid_deger = document.getElementById(shid).value;
				if(shid_deger=='yes'){
					document.getElementById('captcha_var').style.display = 'block';
					document.getElementById('captcha_null').style.display = 'none';
				}else{
					document.getElementById('captcha_var').style.display = 'none';
					document.getElementById('captcha_null').style.display = 'block';
				}
			}
			function Sample_tp(tpid){
				var tpid_deger = document.getElementById(tpid).value;
				if(tpid_deger=='cigicigi'){
					document.getElementById('cigicigi_cpth').style.display = 'block';
					document.getElementById('re_cpth').style.display = 'none';
				}else{
					document.getElementById('cigicigi_cpth').style.display = 'none';
					document.getElementById('re_cpth').style.display = 'block';				
				}
			}
			function delete_confirm(URL) {
				if(confirm('<?php _e('Are you sure delete this post ?', 'cigicigi-post-guest'); ?>')) location.href = URL;
			}
		</script>
		<?php
	}
	add_action('admin_head', 'cigicigi_plugin_header');
}

if( ! function_exists( 'cigicigi_plugins_menu' ) ) {
	function cigicigi_plugins_menu() {
		global $title;
		?>
		<div class="wrap">
			<div class="icon32 icon32-cg" id="icon-options-general"></div>
			<h2><?php echo $title;?></h2>
			<div align="center">
<script type="text/javascript"><!--
google_ad_client = "ca-pub-4689181628554493";
/* wp_plugin */
google_ad_slot = "3664682233";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>			
			</div>
			<div>&nbsp;</div>
			<div style="margin-left: 70px;">
				
				<?php _e('For more CigiCigi Wordpress plugin visit', 'cigicigi-post-guest'); ?>&nbsp;<a href="http://www.cigicigi.co/wordpress-eklentileri" target="_blank">CigiCigi Online</a>
				
			</div>
			<div>&nbsp;</div>
			<div style="margin-left: 50px;">
				<h3><?php _e('Other CigiCigi Wordpress Plugins', 'cigicigi-post-guest'); ?></h3>
				<ul style="list-style-type: square;">
					<li><a href="http://www.cigicigi.co/cigicigi-post-guest.html" target="_blank"><?php _e('CigiCigi Post Guest Plugins', 'cigicigi-post-guest'); ?></a></li>
				</ul>
			</div>
			<div>&nbsp;</div>
			<div align="center">
<script type="text/javascript"><!--
google_ad_client = "ca-pub-4689181628554493";
/* wp_plugin */
google_ad_slot = "3664682233";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>			
			</div>
		</div>
		<?php
	}
}

if( ! function_exists( 'cigicigi_post_guest_settings' ) ) {
	function cigicigi_post_guest_settings() {
		global $cigicigi_post_guest_options;
		global $title;
		global $wpdb;
		$wpdb->flush();

		$cigicigi_post_guest_user_id			= username_exists( 'cigicigi_post_guest_user' );
		$cigicigi_post_guest_user_display_name	= get_userdata( $cigicigi_post_guest_user_id );

		if( isset( $_POST['cigicigi_post_guest_sbmt'] ) ) {
			$cigicigi_options_submit = array(
												'cigicigi_post_guest_captcha'			=> $_POST['captcha'],
												'cigicigi_post_guest_captcha_type'		=> $_POST['captcha_type'],
												'cigicigi_post_guest_recaptcha_pubk'	=> $_POST['recaptcha_pubk'],
												'cigicigi_post_guest_recaptcha_prik'	=> $_POST['recaptcha_prik'],
												'cigicigi_post_guest_cigicigi_capt_bg'	=> $_POST['catptcha_background'],
												'cigicigi_post_guest_editor'			=> $_POST['editor'],
												'cigicigi_post_guest_guest_upload_img'	=> $_POST['upload_image'],
												'cigicigi_post_guest_guest_inf_mail'	=> $_POST['inf_mail'],
										);
			update_option( 'cigicigi_post_guest_options', $cigicigi_options_submit, '', 'yes' );
			$cigicigi_post_guest_options = array_merge( $cigicigi_post_guest_options, $cigicigi_options_submit  );
			
			wp_update_user(array('ID' => $cigicigi_post_guest_user_id, 'display_name' => $_POST['post_author_name'])) ;
			$message = __('Options Saved', 'cigicigi-post-guest');
		}
		
		if($cigicigi_post_guest_options['cigicigi_post_guest_captcha'] == 'yes')
		{
			$captcha_sl_y	= 'selected="selected"';
			$captcha_sl_n	= '';
			$captcha_smp_eh	= '';
			$captcha_smp_no	= 'style="display: none;"';
		}else{
			$captcha_sl_n	= 'selected="selected"';
			$captcha_sl_y	= '';
			$captcha_smp_no	= '';
			$captcha_smp_eh	= 'style="display: none;"';
		}
		if($cigicigi_post_guest_options['cigicigi_post_guest_captcha_type'] == 'cigicigi')
		{
			$captcha_type_cg	= 'selected="selected"';
			$captcha_type_re	= '';
			$sample_cpt_cg		= '';
			$sample_cpt_re		= 'style="display: none;"';
		}else{
			$captcha_type_re	= 'selected="selected"';
			$captcha_type_cg	= '';
			$sample_cpt_re		= '';
			$sample_cpt_cg		= 'style="display: none;"';
		}
		if($cigicigi_post_guest_options['cigicigi_post_guest_editor'] == 'bbcode')
		{
			$editor_type_bb	= 'selected="selected"';
			$editor_type_sd	= '';
		}else{
			$editor_type_sd	= 'selected="selected"';
			$editor_type_bb	= '';
		}
		if($cigicigi_post_guest_options['cigicigi_post_guest_guest_upload_img'] == 'yes')
		{
			$image_upload_y	= 'selected="selected"';
			$image_upload_n	= '';
		}else{
			$image_upload_n	= 'selected="selected"';
			$image_upload_y	= '';
		}
		if($cigicigi_post_guest_options['cigicigi_post_guest_guest_inf_mail'] == 'yes')
		{
			$auth_inf_y		= 'selected="selected"';
			$auth_inf_n	= '';
		}else{
			$auth_inf_n		= 'selected="selected"';
			$auth_inf_y	= '';
		}
?>
		<div class="wrap">
			<div class="icon32 icon32-cg" id="icon-options-general"></div>
			<h2><?php echo $title;?></h2>
			<div align="center">
<script type="text/javascript"><!--
google_ad_client = "ca-pub-4689181628554493";
/* wp_plugin */
google_ad_slot = "3664682233";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>			
			</div>
			<div>&nbsp;</div>
<?php
	$son_post_date_sorgu	= $wpdb->get_results( "SELECT post_date,media_id FROM ".$wpdb->prefix."cigicigi_post_guest where media_id!='' order by post_date asc limit 0,1" );
	$son_media				= explode('-', $son_post_date_sorgu[0]->media_id);
	$cigicigi_post_guest_media_dir	= __DIR__ . '/../../uploads/cigicigi_post_guest_media/temp';
	if ($dit_temp = opendir($cigicigi_post_guest_media_dir)) {
		while (($dosya = readdir($dit_temp)) !== false) {
			$dosya_trh	= explode('-', $dosya);
			if($dosya_trh[0] < $son_media[0])
			{
				$temporary_file_list[] = $dosya;
			}
		}
		closedir($dit_temp);
	}
	$temporary_count = count($temporary_file_list) - 2;
	if($temporary_count > 0)
	{
		$message_temp = sprintf(__('Temporary upload image directory contain %1$s unnecessary files', 'cigicigi-post-guest'), '<b>'.$temporary_count.'</b>');
?>
			<div id="cigicigi_post_guest_form_warn"><?php echo $message_temp; ?>&nbsp;<input type="button" class="cigicigi_delete_button" value="<?php _e('Delete These Files', 'cigicigi-post-guest'); ?>" onclick="delete_confirm('admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $sayfa; ?>&do=delete_temporary_files')"></div>
			<div>&nbsp;</div>
<?php
	}
?>
			<?php if(isset($message)){ ?><div class="updated fade"><strong><?php echo $message; ?></strong></div><?php } ?>
			<form method="POST" action="admin.php?page=cigicigi_post_guest_settings">
			<div id="cg-post-guest-setting" style="margin-left: 70px;">
				<table border="0" cellspacing="3" cellpadding="3">
					<tr>
						<td><b><?php _e('Use CAPTCHA ?', 'cigicigi-post-guest'); ?></b></td>
						<td>
							<select id="captcha" name="captcha" onChange="Sample_sh(this.id);">
								<option value="yes" <?php echo $captcha_sl_y;?>><?php _e('Yes'); ?></option>
								<option value="no" <?php echo $captcha_sl_n;?>><?php echo _e('No'); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><b><?php _e('Captcha Type ?', 'cigicigi-post-guest'); ?></b></td>
						<td>
							<select id=="captcha_type" name="captcha_type" onChange="Sample_tp(this.id);">
								<option value="cigicigi" <?php echo $captcha_type_cg;?>>CigiCigi</option>
								<option value="recaptcha" <?php echo $captcha_type_re;?>>reCAPTCHA</option>
							</select><br />
							<?php _e('If you use reCAPTCHA, you register <a href="https://www.google.com/recaptcha" target="_blank">reCAPTCHA</a> and you save this page for public/private key', 'cigicigi-post-guest'); ?>
						</td>
					</tr>
					<tr>
						<td><b><?php _e('Sample Captcha', 'cigicigi-post-guest'); ?></b></td>
						<td>
							<div id="captcha_var" <?php echo $captcha_smp_eh; ?>>
								<div id="cigicigi_cpth" <?php echo $sample_cpt_cg; ?>><img src="<?php echo get_bloginfo('url');?>/wp-content/plugins/cigicigi-post-guest/images/cigicigi_captcha_s.png"></div>
								<div id="re_cpth" <?php echo $sample_cpt_re; ?>><img src="<?php echo get_bloginfo('url');?>/wp-content/plugins/cigicigi-post-guest/images/recaptcha_s.png"></div>
							</div>
							<div id="captcha_null" <?php echo $captcha_smp_no; ?>><u><?php _e('You are not using CAPTCHA', 'cigicigi-post-guest'); ?></u></div>
						</td>
					</tr>
					<tr>
						<td><b>reCAPTCHA Public Key</b></td>
						<td>
							<input type="text" style="width: 360px;" name="recaptcha_pubk" value="<?php echo $cigicigi_post_guest_options['cigicigi_post_guest_recaptcha_pubk']; ?>">
						</td>
					</tr>
					<tr>
						<td><b>reCAPTCHA Private Key</b></td>
						<td>
							<input type="text" style="width: 360px;" name="recaptcha_prik" value="<?php echo $cigicigi_post_guest_options['cigicigi_post_guest_recaptcha_prik']; ?>">
						</td>
					</tr>
					<tr>
						<td><b><?php _e('CigiCigi Captcha Background', 'cigicigi-post-guest'); ?></b></td>
						<td>
							<input type="text" style="width: 360px;" name="catptcha_background" value="<?php echo $cigicigi_post_guest_options['cigicigi_post_guest_cigicigi_capt_bg']; ?>">
							<br /><?php _e('Background image is in plugin directory. Image size must be 200x60. Sample: /images/arkaplan.png', 'cigicigi-post-guest'); ?>
						</td>
					</tr>
					<tr>
						<td><b><?php _e('Editor Type ?', 'cigicigi-post-guest'); ?></b></td>
						<td>
							<select name="editor">
								<option value="standart" <?php echo $editor_type_sd;?>><?php _e('Standart Editor', 'cigicigi-post-guest'); ?></option>
								<option value="bbcode" <?php echo $editor_type_bb;?>><?php _e('BBCode Editor', 'cigicigi-post-guest'); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><b><?php _e('Upload Image'); ?></b></td>
						<td>
							<select name="upload_image">
								<option value="yes" <?php echo $image_upload_y;?>><?php _e('Yes'); ?></option>
								<option value="no" <?php echo $image_upload_n;?>><?php _e('No'); ?></option>
							</select><br />
							<?php _e('Image upload option only run bbcode editor and your theme must be load jQuery library.', 'cigicigi-post-guest'); ?>
						</td>
					</tr>
					<tr>
						<td><b><?php _e('Post Author Name', 'cigicigi-post-guest'); ?></b></td>
						<td>
							<input type="text" style="width: 360px;" name="post_author_name" value="<?php echo $cigicigi_post_guest_user_display_name->display_name; ?>">
						</td>
					</tr>
					<tr>
						<td><b><?php _e('Author Information with E-Mail', 'cigicigi-post-guest'); ?></b></td>
						<td>
							<select name="inf_mail">
								<option value="yes" <?php echo $auth_inf_y;?>><?php _e('Yes'); ?></option>
								<option value="no" <?php echo $auth_inf_n;?>><?php _e('No'); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<input type="hidden" name="cigicigi_post_guest_sbmt" value="submit" />
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
				</p>
			</div>
			</form>
			<div>&nbsp;</div>
			<div align="center">
<script type="text/javascript"><!--
google_ad_client = "ca-pub-4689181628554493";
/* wp_plugin */
google_ad_slot = "3664682233";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>			
			</div>
		</div>
<?php
	}
}


if( ! function_exists( 'cigicigi_post_guest_waiting' ) ) {
	function cigicigi_post_guest_waiting(){
		global $title;
		global $wpdb;
		$wpdb->flush();
?>
		<div class="wrap">
			<div class="icon32 icon32-cg" id="icon-options-general"></div>
			<h2><?php echo $title;?></h2>
			<div align="center">
<script type="text/javascript"><!--
google_ad_client = "ca-pub-4689181628554493";
/* wp_plugin */
google_ad_slot = "3664682233";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>			
			</div>
			<div>&nbsp;</div>
<?php
	$son_post_date_sorgu	= $wpdb->get_results( "SELECT post_date,media_id FROM ".$wpdb->prefix."cigicigi_post_guest where media_id!='' order by post_date asc limit 0,1" );
	$son_media				= explode('-', $son_post_date_sorgu[0]->media_id);
	$cigicigi_post_guest_media_dir	= __DIR__ . '/../../uploads/cigicigi_post_guest_media/temp';
	if ($dit_temp = opendir($cigicigi_post_guest_media_dir)) {
		while (($dosya = readdir($dit_temp)) !== false) {
			$dosya_trh	= explode('-', $dosya);
			if($dosya_trh[0] < $son_media[0])
			{
				$temporary_file_list[] = $dosya;
			}
		}
		closedir($dit_temp);
	}
	$temporary_count = count($temporary_file_list) - 2;
	if($temporary_count > 0)
	{
		$message_temp = sprintf(__('Temporary upload image directory contain %1$s unnecessary files', 'cigicigi-post-guest'), '<b>'.$temporary_count.'</b>');
?>
			<div id="cigicigi_post_guest_form_warn"><?php echo $message_temp; ?>&nbsp;<input type="button" class="cigicigi_delete_button" value="<?php _e('Delete These Files', 'cigicigi-post-guest'); ?>" onclick="delete_confirm('admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $sayfa; ?>&do=delete_temporary_files')"></div>
			<div>&nbsp;</div>
<?php
	}
		switch($_GET['do'])
		{
		default:
	if(get_option( 'cigicigi_post_guest_count' ) > 0)
	{
		$post_sayi_sorgu	= $wpdb->get_results( "SELECT ID FROM ".$wpdb->prefix."cigicigi_post_guest" );
		$post_sayi			= count($post_sayi_sorgu);
		$listele			= 10;
		$kac_sayfa_var	= $post_sayi / $listele;
		$kac_sayfa_var	= ceil($kac_sayfa_var);
		if(($_GET['sayfa'] < 2) | ($_GET['sayfa'] > $kac_sayfa_var))
		{
			$sayfa	= 1;
		}else{
			$sayfa	= $_GET['sayfa'];
		}
		$basla			= $listele * ($sayfa - 1);
?>
		<div class="tablenav top">
			<div class="tablenav-pages"><span class="displaying-num"><?php _e('Page'); ?>: </span><span class="pagination-links">
<?php
		
		for($s=1;$s<=$kac_sayfa_var;$s++)
		{
?>
			<a class="next-page" href="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $s; ?>&do=<?php echo $_GET['do']; ?>"><?php echo $s; ?></a>
<?php
		}
?>
			</span></div>
		</div>
			<div>&nbsp;</div>
			<table class="wp-list-table widefat fixed pages" cellspacing="0">
			<thead>
			<tr>
				<th scope='col' id='title' class='manage-column column-title sortable desc'  style=""><span><?php _e('Title'); ?></span></th>
				<th scope='col' id='author' class='manage-column column-author sortable desc'  style=""><span><?php _e('Author'); ?></span></th>
				<th scope='col' id='date' class='manage-column column-author sortable desc'  style=""><span><?php _e('Date'); ?></span></th>
				<th scope='col' id='mail' class='manage-column column-author sortable desc'  style=""><span><?php _e('Author E-Mail', 'cigicigi-post-guest'); ?></span></th>
				<th scope='col' id='category' class='manage-column column-author sortable desc'  style=""><span><?php _e('Category'); ?></span></th>
				<th scope='col' id='slugs' class='manage-column column-author sortable desc'  style=""><span><?php _e('Post Tags'); ?></span></th>
				<th scope='col' id='edit' class='manage-column column-author sortable desc'  style=""><span><?php _e('Edit'); echo ' / '; _e('Delete'); ?></span></th>
			</tr>
			</thead>
			<tbody id="the-list">
<?php
		$table_name = $wpdb->prefix . "cigicigi_post_guest";
		$post_query	= $wpdb->get_results( "SELECT * FROM ".$table_name." order by post_date asc limit ".$basla.",".$listele );
		$kac_post	= count($post_query);
		for($i=0;$i<$kac_post;$i++){
			$imod	= $i / 2;
			if($imod == ceil($imod))
			{
				$tr_class	= 'alternate';
			}else{
				$tr_class	= '';
			}
			$term_name	= $wpdb->get_results( "SELECT term_id,name FROM ".$wpdb->prefix."terms where term_id='".$post_query[$i]->cat_id."'" );
?>
			<tr id='post-<?php echo $post_query[$i]->ID; ?>' class='<?php echo $tr_class; ?> author-other status-draft format-default iedit' valign="top">
				<td class="post-title page-title column-title"><strong>
					<a class="row-title" href="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $sayfa; ?>&do=read&id=<?php echo $post_query[$i]->ID; ?>" title="<?php _e('Read this post', 'cigicigi-post-guest'); ?>"><?php echo $post_query[$i]->post_title; ?></a>
				</strong></td>
				<td class="author column-author"><?php echo $post_query[$i]->yazar_ad; ?></td>
				<td class="date column-date"><abbr><?php echo $post_query[$i]->post_date; ?></abbr></td>
				<td class="email column-email"><?php echo $post_query[$i]->mail; ?></td>
				<td class="category column-category"><?php echo $term_name[0]->name; ?></td>
				<td class="tags column-tags"><?php echo $post_query[$i]->post_slugs; ?></td>
				<td class="edit column-edit">
					<input type="button" value="<?php _e('Edit'); ?>" onclick=parent.location="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $sayfa; ?>&do=edit&id=<?php echo $post_query[$i]->ID; ?>">
					<input type="button" class="cigicigi_delete_button" value="<?php _e('Delete'); ?>" onclick="delete_confirm('admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $sayfa; ?>&do=delete&id=<?php echo $post_query[$i]->ID; ?>')">
					<input type="button" class="cigicigi_publish_button" value="<?php _e('Publish'); ?>" onclick=parent.location="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $sayfa; ?>&do=publish&id=<?php echo $post_query[$i]->ID; ?>">
				</td>
			</tr>
<?php
		}
?>
			</tbody>
		</table>
		<div>&nbsp;</div>
		<div class="tablenav top">
			<div class="tablenav-pages"><span class="displaying-num"><?php _e('Page'); ?>: </span><span class="pagination-links">
<?php
		
		for($s=1;$s<=$kac_sayfa_var;$s++)
		{
?>
			<a class="next-page" href="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $s; ?>&do=<?php echo $_GET['do']; ?>"><?php echo $s; ?></a>
<?php
		}
?>
			</span></div>
		</div>
<?php
	}else{
?>
		<div class="updated fade"><strong><?php _e('There are not any waiting posts.', 'cigicigi-post-guest'); ?></strong></div>
<?php
	}
		break;
		case "read":
			$single_cigi_post_sorgu	= $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."cigicigi_post_guest where ID='".$_GET['id']."'" );
?>
		<div id="cigicigi_guest_post_read">
			<h3><?php echo $single_cigi_post_sorgu[0]->post_title; ?></h3><br />
			<span><b><?php _e('Author'); ?>:</b> <?php echo $single_cigi_post_sorgu[0]->yazar_ad; ?>, <b><?php _e('Date'); ?>:</b> <?php echo $single_cigi_post_sorgu[0]->post_date; ?>, <b><?php _e('Author E-Mail', 'cigicigi-post-guest'); ?>:</b> <?php echo $single_cigi_post_sorgu[0]->mail; ?></span><br />
			<p><?php echo cigicigi_bbcodetohtml($single_cigi_post_sorgu[0]->post_content, 'read'); ?></p><br />
			<span><b><?php _e('Tags'); ?>:</b> <?php echo $single_cigi_post_sorgu[0]->post_slugs; ?></span><br />
			<input type="button" value="<< <?php _e('Back', 'cigicigi-post-guest'); ?>" onclick=parent.location="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $_GET['sayfa']; ?>">
			<input type="button" value="<?php _e('Edit'); ?>" onclick=parent.location="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $_GET['sayfa']; ?>&do=edit&id=<?php echo $single_cigi_post_sorgu[0]->ID; ?>">
			<input type="button" class="cigicigi_delete_button" value="<?php _e('Delete'); ?>" onclick="delete_confirm('admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $_GET['sayfa']; ?>&do=delete&id=<?php echo $single_cigi_post_sorgu[0]->ID; ?>')">
			<input type="button" class="cigicigi_publish_button" value="<?php _e('Publish'); ?>" onclick=parent.location="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $_GET['sayfa']; ?>&do=publish&id=<?php echo $single_cigi_post_sorgu[0]->ID; ?>">
		</div>
<?php
		break;
		case "edit":
		if( isset( $_POST['cigicigi_post_guest_form_admin'] ) ) {
			if(empty($_POST['cigicigi_post_guest_author_name']) | empty($_POST['cigicigi_post_guest_email']) | empty($_POST['cigicigi_post_guest_title']) | empty($_POST['cigicigi_post_guest_message']) | empty($_POST['cigicigi_post_guest_posttags']))
			{
				$message	= __('You must fill in all fields', 'cigicigi-post-guest'); 
				$err		= "evet";
			}elseif(!preg_match( "/^(?:[a-z0-9]+(?:[a-z0-9\-_\.]+)?@[a-z0-9]+(?:[a-z0-9\-\.]+)?\.[a-z]{2,5})$/i", trim( $_POST['cigicigi_post_guest_email'] ) )){
					$message	= __('Incorrect E-Mail address.', 'cigicigi-post-guest'); 
					$err		= "evet";
			}else{
				$err		= "nop";
			}
			if($err	== "nop"){
				$table_name = $wpdb->prefix . "cigicigi_post_guest";
				
				$rows_affected = $wpdb->update( $table_name, array( 'yazar_ad' => $_POST['cigicigi_post_guest_author_name'], 'mail' => $_POST['cigicigi_post_guest_email'], 'post_content' => cigicigi_post_guest_post_security($_POST['cigicigi_post_guest_message']),
	   'post_date' => current_time('mysql'), 'post_title' => $_POST['cigicigi_post_guest_title'], 'post_slugs' => $_POST['cigicigi_post_guest_posttags'], 'cat_id' => $_POST['cigicigi_post_guest_post_cat'] ), array('ID' => $_GET['id']) );
			
				$message	= __('Your post have been saving database and will be published after approval.', 'cigicigi-post-guest');
			}
		}
		$single_cigi_post_sorgu	= $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."cigicigi_post_guest where ID='".$_GET['id']."'" );
		if(isset($message))
		{
?>
			<div id="cigicigi_post_guest_form_warn"><?php echo $message; ?></div>
<?php
		}
?>
		<div id="cigicigi_guest_post_read">
			<form method="POST" action="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $_GET['sayfa']; ?>&do=edit&id=<?php echo $_GET['id']; ?>" id="cigicigi_post_guest_form" enctype="multipart/form-data">
			<div id="cigicigi_post_guest_form" align="center">
			<table border="0" cellspacing="3" cellpadding="3">
				<tr>
					<td><?php _e('Name'); ?>: </td>
					<td><input type="text" name="cigicigi_post_guest_author_name" maxlength="60" value="<?php echo $single_cigi_post_sorgu[0]->yazar_ad; ?>"></td>
				</tr>
				<tr>
					<td><?php _e('E-Mail'); ?>: </td>
					<td><input type="text" name="cigicigi_post_guest_email" maxlength="120" value="<?php echo $single_cigi_post_sorgu[0]->mail; ?>"></td>
				</tr>
				<tr>
					<td><?php _e('Post'); ?>: </td>
					<td><input type="text" name="cigicigi_post_guest_title" maxlength="120" value="<?php echo $single_cigi_post_sorgu[0]->post_title; ?>"></td>
				</tr>
				<tr>
					<td><?php _e('Category'); ?>: </td>
					<td>
						<select name="cigicigi_post_guest_post_cat">
<?php
							$cigicigi_guest_post_tax	= $wpdb->get_results( "SELECT term_id, taxonomy FROM ".$wpdb->prefix."term_taxonomy where taxonomy='category'" );
							$cigicigi_guest_post_tax_co	= count($cigicigi_guest_post_tax);
							$cigicigi_guest_post_tax_tt	= $wpdb->prefix . "terms";
							for($cat=0;$cat<$cigicigi_guest_post_tax_co;$cat++){
								if($single_cigi_post_sorgu[0]->cat_id == $cigicigi_guest_post_tax[$cat]->term_id)
								{
									$sel	= 'selected="selected"';
								}else{
									$sel	= '';
								}
								$term_query	= $wpdb->get_results( "SELECT term_id, name FROM ".$cigicigi_guest_post_tax_tt." where term_id='".$cigicigi_guest_post_tax[$cat]->term_id."'" );
								echo '<option '.$sel.' value="'.$cigicigi_guest_post_tax[$cat]->term_id.'">'.$term_query[0]->name.'</option>';
							}
?>
						</select>
					</td>
				</tr>
				<tr>
					<td><?php _e('Post Tags'); ?>: </td>
					<td><input type="text" name="cigicigi_post_guest_posttags" value="<?php echo $single_cigi_post_sorgu[0]->post_slugs; ?>"><br /><?php  _e( 'Separate items with commas', 'cigicigi-post-guest' ); ?></td>
				</tr>
				<tr>
					<td><?php _e('Content'); ?>: </td>
					<td>
						<script>cigicigi_post_guest_edToolbar('cigicigi_post_guest_message', '<?php echo plugins_url( 'bbcode/' , __FILE__ ); ?>');</script>
						<textarea name="cigicigi_post_guest_message" id="cigicigi_post_guest_message" rows="16" cols="75"><?php echo $single_cigi_post_sorgu[0]->post_content; ?></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="2" rowspan="1">
						<input type="hidden" name="cigicigi_post_guest_form_admin" value="submit">
						<input type="button" class="cigicigi_post_guest_submit" value="<< <?php _e('Back', 'cigicigi-post-guest'); ?>" onclick=parent.location="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $_GET['sayfa']; ?>">
						<input type="submit" class="cigicigi_post_guest_submit" name="cigicigi_post_guest_submit" value="<?php _e('Save'); ?>">
					</td>
				</tr>
			</table>
			</div>
			</form>
		</div>
<?php
		break;
		case "delete":
			$post_media_sorgu	= $wpdb->get_results("SELECT ID,media_id FROM ".$wpdb->prefix."cigicigi_post_guest where ID='".$_GET['id']."'");
			if(!empty($post_media_sorgu[0]->media_id))
			{
				$media_unlink = __DIR__ . '/../../uploads/cigicigi_post_guest_media/temp/'.$post_media_sorgu[0]->media_id;
				@unlink($media_unlink);
			}
			$wpdb->query( "DELETE FROM ".$wpdb->prefix."cigicigi_post_guest where ID='".$_GET['id']."'" );
			$cigicigi_post_guest_count	= get_option( 'cigicigi_post_guest_count' ) - 1;
			update_option( 'cigicigi_post_guest_count', $cigicigi_post_guest_count, '', 'yes' );
?>
			<div id="cigicigi_post_guest_form_warn"><?php _e('Guest post has deleted !.', 'cigicigi-post-guest'); ?></div>
			<input type="button" value="<< <?php _e('Back', 'cigicigi-post-guest'); ?>" onclick=parent.location="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $_GET['sayfa']; ?>">
<?php
		break;
		case "publish":
				$single_cigi_post_sorgu	= $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."cigicigi_post_guest where ID='".$_GET['id']."'" );
				if(!empty($single_cigi_post_sorgu[0]->media_id))
				{
					$media_kaynak = __DIR__ . '/../../uploads/cigicigi_post_guest_media/temp/'.$single_cigi_post_sorgu[0]->media_id;
					$media_hedef  = __DIR__ . '/../../uploads/cigicigi_post_guest_media/'.$single_cigi_post_sorgu[0]->media_id;
					@copy($media_kaynak, $media_hedef);
					@unlink($media_kaynak);
				}
				$post_content			= '<h2>';
				$post_content			.= sprintf(__('This post send by %1$s', 'cigicigi-post-guest'), '<b>'.$single_cigi_post_sorgu[0]->yazar_ad.'</b>');
				$post_content			.= '</h2><br /><br /><br />';
				$post_content			.= cigicigi_bbcodetohtml($single_cigi_post_sorgu[0]->post_content, 'publish');
				$post_name				= cigicigi_post_guest_title_slug($single_cigi_post_sorgu[0]->post_title);
				$table_name	= $wpdb->prefix."posts";

				$post = array(
							  'menu_order'		=> 0,
							  'comment_status'	=> 'open',
							  'ping_status'		=> 'open',
							  'post_author'		=> username_exists( 'cigicigi_post_guest_user' ),
							  'post_category'	=> array($single_cigi_post_sorgu[0]->cat_id),
							  'post_content'	=> $post_content,
							  'post_date'		=> $single_cigi_post_sorgu[0]->post_date,
							  'post_date_gmt'	=> $single_cigi_post_sorgu[0]->post_date,
							  'post_name'		=> $post_name,
							  'post_parent'		=> 0,
							  'post_status'		=> 'publish',
							  'post_title'		=> $single_cigi_post_sorgu[0]->post_title,
							  'post_type'		=> 'post',
							  'tags_input'		=> $single_cigi_post_sorgu[0]->post_slugs,
						);  
				wp_insert_post( $post );
				$option	= get_option( 'cigicigi_post_guest_options' );
				if($option['cigicigi_post_guest_guest_inf_mail'] == 'yes'){
					$result	= cigicigi_post_guest_send_mail($single_cigi_post_sorgu[0]->mail);
					if($result === True)
					{
?>
						<div id="cigicigi_post_guest_form_warn"><?php _e('Post published', 'cigicigi-post-guest'); ?> .!</div>
						<input type="button" value="<< <?php _e('Back', 'cigicigi-post-guest'); ?>" onclick=parent.location="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $_GET['sayfa']; ?>">
<?php
					}else{
?>
						<div id="cigicigi_post_guest_form_warn"><?php _e('Sorry user e-mail could not be delivered but post published', 'cigicigi-post-guest'); ?> .!</div>
						<input type="button" value="<< <?php _e('Back', 'cigicigi-post-guest'); ?>" onclick=parent.location="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $_GET['sayfa']; ?>">
<?php				
					}
				}else{
?>
						<div id="cigicigi_post_guest_form_warn"><?php _e('Post published', 'cigicigi-post-guest'); ?> .!</div>
						<input type="button" value="<< <?php _e('Back', 'cigicigi-post-guest'); ?>" onclick=parent.location="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $_GET['sayfa']; ?>">
<?php				
				}
				$wpdb->query( "DELETE FROM ".$wpdb->prefix."cigicigi_post_guest where ID='".$_GET['id']."'" );
				$cigicigi_post_guest_count	= get_option( 'cigicigi_post_guest_count' ) - 1;
				update_option( 'cigicigi_post_guest_count', $cigicigi_post_guest_count, '', 'yes' );
		break;
		case "delete_temporary_files":
			$son_post_date_sorgu	= $wpdb->get_results( "SELECT post_date,media_id FROM ".$wpdb->prefix."cigicigi_post_guest where media_id!='' order by post_date asc limit 0,1" );
			$son_media				= explode('-', $son_post_date_sorgu[0]->media_id);
			$cigicigi_post_guest_media_dir	= __DIR__ . '/../../uploads/cigicigi_post_guest_media/temp';
			if ($dit_temp = opendir($cigicigi_post_guest_media_dir)) {
				while (($dosya = readdir($dit_temp)) !== false) {
					$dosya_trh	= explode('-', $dosya);
					if($dosya_trh[0] < $son_media[0])
					{
						$temporary_file_list[] = $dosya;
					}
				}
				closedir($dit_temp);
			}
			$temporary_count = count($temporary_file_list) - 2;
			if($temporary_count > 0)
			{
				for($t=2;$t<count($temporary_file_list);$t++)
				{
					$dosya_yol = $cigicigi_post_guest_media_dir.'/'.$temporary_file_list[$t];
					@unlink($dosya_yol);
				}
			$message_temp = __('Temporary images deleted', 'cigicigi-post-guest');
?>
			<div id="cigicigi_post_guest_form_warn"><?php echo $message_temp; ?></div>
			<input type="button" value="<< <?php _e('Back', 'cigicigi-post-guest'); ?>" onclick=parent.location="admin.php?page=cigicigi_post_guest_posts&sayfa=<?php echo $_GET['sayfa']; ?>">
			<div>&nbsp;</div>
<?php
			}
		break;
		}
?>
			<div>&nbsp;</div>
			<div align="center">
<script type="text/javascript"><!--
google_ad_client = "ca-pub-4689181628554493";
/* wp_plugin */
google_ad_slot = "3664682233";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>			
			</div>
		</div>
<?php	
	}
}

// Send mail function
if( ! function_exists( 'cigicigi_post_guest_send_mail' ) ) {
	function cigicigi_post_guest_send_mail($mail) {
		$to = $mail;
		$user_info_string = '';
		$userdomain = '';
		$form_action_url = '';
		$form_action_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$message = '
			<html>
			<head>
				<title>Contact from'.get_bloginfo('name').'</title>
			</head>
			<body>
				<b>'.__('Your post to be approved', 'cigicigi-post-guest').'</b>
				<br />
				'.__('Thank you posting', 'cigicigi-post-guest').'
				<br />
				<a href="'.get_bloginfo('home').'">'.get_bloginfo('home').'</a>
			</body>
			</html>
			';


			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

			$headers .= 'From: '.get_bloginfo('admin_email'). "\r\n";

			return @mail($to, get_bloginfo('name'), stripslashes($message), $headers);
	}
}

wp_enqueue_style( 'cigicigiStylesheet', WP_PLUGIN_URL .'/cigicigi-post-guest/cigicigi_style.css' );
wp_enqueue_style( 'cigicigiBBCodeStylesheet', WP_PLUGIN_URL .'/cigicigi-post-guest/bbcode/cigicigi-editor.css' );
wp_enqueue_script( 'cigicigiBBCodeJS', WP_PLUGIN_URL .'/cigicigi-post-guest/bbcode/cigicigi-ed.js' );

if( ! function_exists( 'cigicigi_post_guest_form' ) ) {
	function cigicigi_post_guest_form() {
		global $wpdb;
		if(empty($_SESSION['cigicigi_flood']))
		{
			$_SESSION['cigicigi_flood'] = 'no';
		}
		$cigicigi_post_guest_options	= get_option( 'cigicigi_post_guest_options' );

		if($cigicigi_post_guest_options['cigicigi_post_guest_captcha'] == 'yes')
		{
			if($cigicigi_post_guest_options['cigicigi_post_guest_captcha_type'] == 'recaptcha')
			{
				$recaptcha_lib_url	= dirname(__FILE__).'/recaptchalib.php';
				require_once $recaptcha_lib_url;
			}
		}

		if( isset( $_POST['cigicigi_post_guest_form'] ) ) {
			if(empty($_POST['cigicigi_post_guest_author_name']) | empty($_POST['cigicigi_post_guest_email']) | empty($_POST['cigicigi_post_guest_title']) | empty($_POST['cigicigi_post_guest_message']) | empty($_POST['cigicigi_post_guest_posttags']))
			{
				$message	= __('You must fill in all fields', 'cigicigi-post-guest');
				$_SESSION['cigicigi_flood'] = 'no';
				$err		= "evet";
			}elseif(!preg_match( "/^(?:[a-z0-9]+(?:[a-z0-9\-_\.]+)?@[a-z0-9]+(?:[a-z0-9\-\.]+)?\.[a-z]{2,5})$/i", trim( $_POST['cigicigi_post_guest_email'] ) )){
					$message	= __('Incorrect E-Mail address.', 'cigicigi-post-guest');
					$_SESSION['cigicigi_flood'] = 'no';
					$err		= "evet";
			}else{
				if($cigicigi_post_guest_options['cigicigi_post_guest_captcha'] == 'yes')
				{
					if($cigicigi_post_guest_options['cigicigi_post_guest_captcha_type'] == 'cigicigi')
					{
						if($_POST['cigicigi_post_guest_cigicigi_captcha'] != $_SESSION['cigicigi_captcha'])
						{
							$message	= __('Incorrect Captcha, Please try again...!', 'cigicigi-post-guest');
							$_SESSION['cigicigi_flood'] = 'no';
							$err		= "evet";
						}else{
							$_SESSION['cigicigi_captcha']	= substr(md5(rand()), 10, 6);
							$err		= "nop";
						}
					}else{
						$resp = recaptcha_check_answer ($cigicigi_post_guest_options['cigicigi_post_guest_recaptcha_prik'], $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

						if (!$resp->is_valid) {
							$message	= __('Incorrect Captcha, Please try again...!', 'cigicigi-post-guest');
							$_SESSION['cigicigi_flood'] = 'no';
							$err		= "evet";
						}else{
							$err		= "nop";
						}
					}
				}else{
					$_SESSION['cigicigi_captcha']	= substr(md5(rand()), 10, 6);
					$err		= "nop";
				}
			}
			if(($err == "nop") & ($_SESSION['cigicigi_flood']	== 'no')){
				if(isset($_POST['cigicigi_post_guest_file_var']))
				{
					$media_id	= $_POST['cigicigi_post_guest_file_var'];
				}
					$table_name = $wpdb->prefix . "cigicigi_post_guest";
					
					$wpdb->insert( $table_name, array( 'yazar_ad' => cigicigi_post_guest_title_sec($_POST['cigicigi_post_guest_author_name']), 'mail' => $_POST['cigicigi_post_guest_email'], 'post_content' => cigicigi_post_guest_post_security($_POST['cigicigi_post_guest_message']),
		   'post_date' => current_time('mysql'), 'post_title' => cigicigi_post_guest_title_sec($_POST['cigicigi_post_guest_title']), 'post_slugs' => cigicigi_post_guest_title_sec($_POST['cigicigi_post_guest_posttags']), 'cat_id' => $_POST['cigicigi_post_guest_post_cat'], 'media_id' => $media_id ) );
					
					$cigicigi_post_guest_count	= get_option( 'cigicigi_post_guest_count' ) + 1;
					update_option( 'cigicigi_post_guest_count', $cigicigi_post_guest_count, '', 'yes' );
					$message	= __('Your post have been saving database and will be published after approval.', 'cigicigi-post-guest');
					$_SESSION['cigicigi_flood']	= 'yes';
			}elseif($_SESSION['cigicigi_flood']	== 'yes'){
					$message	= __('Flood Protection ! Your post had already saved database.', 'cigicigi-post-guest');
					$_SESSION['cigicigi_flood']	= 'no';
					$err		= "evet";
			}
		}
		if(isset($message))
		{
			$content .= '<div id="cigicigi_post_guest_form_warn">'.$message.'</div>';
		}

			$pageURL = 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			$content .= '<form method="POST" action="'.$pageURL.'" enctype="multipart/form-data">
			<div id="cigicigi_post_guest_form" align="center">
			<table border="0" cellspacing="3" cellpadding="3">
				<tr>
					<td>'.__('Name').': </td>
					<td><input type="text" name="cigicigi_post_guest_author_name" maxlength="60" value="'.$_POST['cigicigi_post_guest_author_name'].'"></td>
				</tr>
				<tr>
					<td>'.__('E-Mail').': </td>
					<td><input type="text" name="cigicigi_post_guest_email" maxlength="120" value="'.$_POST['cigicigi_post_guest_email'].'"></td>
				</tr>
				<tr>
					<td>'.__('Post').': </td>
					<td><input type="text" name="cigicigi_post_guest_title" maxlength="120" value="'.$_POST['cigicigi_post_guest_title'].'"></td>
				</tr>
				<tr>
					<td>'.__('Category').': </td>
					<td>
						<select name="cigicigi_post_guest_post_cat">';

							$table_name		= $wpdb->prefix . "term_taxonomy";
							$post_query		= $wpdb->get_results( "SELECT term_id, taxonomy FROM ".$table_name." where taxonomy='category'" );
							$taxonomy_count	= count($post_query);
							$table_term		= $wpdb->prefix . "terms";
							for($i=0;$i<$taxonomy_count;$i++){
								if(isset($_POST['cigicigi_post_guest_post_cat']))
								{
									if($_POST['cigicigi_post_guest_post_cat'] == $post_query[$i]->term_id)
									{
										$cigicigi_post_guest_post_cat_selected	= 'selected="selected"';
									}else{
										$cigicigi_post_guest_post_cat_selected	= '';
									}
								}
									$term_query	= $wpdb->get_results( "SELECT term_id, name FROM ".$table_term." where term_id='".$post_query[$i]->term_id."'" );
									$content .= '<option value="'.$post_query[$i]->term_id.'" '.$cigicigi_post_guest_post_cat_selected.'>'.$term_query[0]->name.'</option>';
							}
			$content .= '</select>
					</td>
				</tr>
				<tr>
					<td>'.__('Post Tags').': </td>
					<td><input type="text" name="cigicigi_post_guest_posttags" value="'.$_POST['cigicigi_post_guest_posttags'].'"><br />'. __( 'Separate items with commas', 'cigicigi-post-guest' ).'</td>
				</tr>
				<tr>
					<td>'.__('Content').': </td>
					<td>';
		if($cigicigi_post_guest_options['cigicigi_post_guest_editor'] == 'bbcode'){
			$content	.= '
						<script>cigicigi_post_guest_edToolbar(\'cigicigi_post_guest_message\', \''.plugins_url( 'bbcode/' , __FILE__ ).'\', \''.$cigicigi_post_guest_options['cigicigi_post_guest_guest_upload_img'].'\'); </script>
						<textarea name="cigicigi_post_guest_message" id="cigicigi_post_guest_message" rows="16" cols="55">'.$_POST['cigicigi_post_guest_message'].'</textarea>';
		}else{
			$content	.= '<textarea name="cigicigi_post_guest_message" rows="14" cols="55">'.$_POST['cigicigi_post_guest_message'].'</textarea>';
		}
			if(empty($_POST['cigicigi_post_guest_file_var']))
			{
				$style	= 'style="display: none;"';
				$content	.= '<input type="hidden" id="cigicigi_post_guest_file_var" name="cigicigi_post_guest_file_var" value="">';
			}else{
				$content	.= '<input type="hidden" id="cigicigi_post_guest_file_var" name="cigicigi_post_guest_file_var" value="'.$_POST['cigicigi_post_guest_file_var'].'">';
			}
			$content .= '<div id="cigicigi_post_guest_upload_information" '.$style.'><a href="#cigicigi_post_guest_dialog" name="cigicigi_post_guest_modal">'.__('You are uploaded', 'cigicigi-post-guest').': '.$_POST['cigicigi_post_guest_file_var'].'</a></div>
					</td>
				</tr>';

		if($cigicigi_post_guest_options['cigicigi_post_guest_captcha'] == 'yes'){
			if($cigicigi_post_guest_options['cigicigi_post_guest_captcha_type'] == 'cigicigi'){
				$_SESSION['cigicigi_captcha']	= substr(md5(rand()), 10, 6);
				$content .= '<tr>
						<td colspan="2" rowspan="1" style="text-align: center;"><img src="'.get_bloginfo('url').'/wp-content/plugins/cigicigi-post-guest/cigicigi-captcha.php?bg='.base64_encode($cigicigi_post_guest_options['cigicigi_post_guest_cigicigi_capt_bg']).'">
						<input type="text" name="cigicigi_post_guest_cigicigi_captcha" maxlength="6"></td>
					</tr>';
			}else{
				$content .= '<tr>
						<td colspan="2" rowspan="1" style="text-align: center;">'.recaptcha_get_html($cigicigi_post_guest_options['cigicigi_post_guest_recaptcha_pubk']).'</td>
					</tr>';			
			}
		}
			$content .= '
				<tr>
					<td colspan="2" rowspan="1">
						<input type="hidden" name="cigicigi_post_guest_form" value="submit">
						<input type="submit" class="cigicigi_post_guest_submit" name="cigicigi_post_guest_submit" value="'.__('Send', 'cigicigi-post-guest').'">
					</td>
				</tr>
			</table>
			</div>
			</form>
			';
		if(($cigicigi_post_guest_options['cigicigi_post_guest_editor'] == 'bbcode') and ($cigicigi_post_guest_options['cigicigi_post_guest_guest_upload_img'] == 'yes')){
			$_SESSION['cigicigi_upload']	= 'yes';
			$_SESSION['cigicigi_upload_count']	= 0;
			$content	.= '
							<div id="cigicigi_post_guest_boxes">
								<div id="cigicigi_post_guest_dialog" class="window">
									<script>
										var timeout_upload;
										function timedCount()
										{
											upload_check(\''.rand().'\', \''.plugins_url( '/' , __FILE__ ).'cigicigi_upload.php\');
											timeout_upload=setTimeout("timedCount()",1000);
										}
									</script>
									<p align="right"><a href="#" class="close"/>X</a></p><br />
									<p>'.__('You can upload one image and add text', 'cigicigi-post-guest').'</p><br />
										<form method="POST" id="cigicigi_post_guest_file_upload_form" action="'.plugins_url( '/' , __FILE__ ).'cigicigi_upload.php" enctype="multipart/form-data">
								';
			if(empty($_POST['cigicigi_post_guest_file_var']))
			{
			$content	.= '
										<fieldset>
											<legend>'.__('Upload Image').'</legend>
											<ol>
												<li id="cigicigi_post_guest_media_upload">
													<label for="cigicigi_post_guest_media_upload_field">'.__('Choose a file to upload', 'cigicigi-post-guest').': </label>
													<input name="MAX_FILE_SIZE" value="5242880" type="hidden" />
													<div id="cigicigi_post_guest_upload_message" style="display: none;" align="center"><img src="'.plugins_url( '/bbcode' , __FILE__ ).'/working.gif"> '.__('Uploading', 'cigicigi-post-guest').'...</div>
													<input type="file" name="cigicigi_post_guest_file" id="cigicigi_post_guest_file" accept="image/gif,image/jpeg,image/jpg,image/png"/>
													<input type="submit" style="width: 110px;" value="'.__('Add File', 'cigicigi-post-guest').'" onClick="timedCount();">
													<div id="cigicigi_post_guest_upload_bar"><img id="cigicigi_post_guest_upload_bar_image" src="'.plugins_url( '/bbcode' , __FILE__ ).'/upload_bar.png" width="30" height="10"></div>
												</li>
											</ol>
											<iframe id="cigicigi_post_guest_upload_target" name="cigicigi_post_guest_upload_target" src="" style="width:350px;height:50px;border:0px solid #fff;"></iframe>
										</fieldset>
										<fieldset>
											<legend id="cigicigi_post_guest_image_properties_title" style="display: none;">'.__('Image Properties', 'cigicigi-post-guest').'</legend>
											<div id="cigicigi_post_guest_image_properties" style="display: none;">
												<p><b>'.__('File Name', 'cigicigi-post-guest').':</b> <span id="cigicigi_post_guest_image_properties_filename"></span></p>
												<p><b>'.__('Align').':</b> <select id="cigicigi_post_guest_image_align"><option value="none" selected="selected">'.__('None').'</option><option value="left">'.__('Left').'</option></select></p>
												<p><b>'.__('Width').':</b> <input id="cigicigi_post_guest_image_width" size="5" onkeypress="return onlyNumbers_width(this.value)" maxlength="3" onChange="return onlyNumbers_width(this.value)"> px.<br />'.__('Maximum image width 600 px.', 'cigicigi-post-guest').'</p>
												<p><input type="button" style="width: 110px;" value="'.__('Add to text', 'cigicigi-post-guest').'" class="close" onClick="cigicigi_post_guest_doAddUpload(\'cigicigi_post_guest_message\')">&nbsp;&nbsp;&nbsp;<input type="button" value="'.__('Get BBCode', 'cigicigi-post-guest').'" style="width: 110px;" onClick="cigicigi_post_guest_doGetBBCode()"></p>
											</div>
										</fieldset>
			';
			}else{
			$content	.= '
										<fieldset>
											<legend id="cigicigi_post_guest_image_properties_title">'.__('Image Properties', 'cigicigi-post-guest').'</legend>
											<div id="cigicigi_post_guest_image_properties">
												<p><b>'.__('File Name', 'cigicigi-post-guest').':</b> <span id="cigicigi_post_guest_image_properties_filename">'.$_POST['cigicigi_post_guest_file_var'].'</span></p>
												<p><b>'.__('Align').':</b> <select id="cigicigi_post_guest_image_align"><option value="alignnone" selected="selected">'.__('None').'</option><option value="alignleft">'.__('Left').'</option></select></p>
												<p><b>'.__('Width').':</b> <input id="cigicigi_post_guest_image_width" size="5" onkeypress="return onlyNumbers_width(this.value)" onChange="return onlyNumbers_width(this.value)"> px.<br />'.__('Maximum image width 600 px.', 'cigicigi-post-guest').'</p>
												<p><input type="button" style="width: 110px;" value="'.__('Add to text', 'cigicigi-post-guest').'" class="close" onClick="cigicigi_post_guest_doAddUpload(\'cigicigi_post_guest_message\')">&nbsp;&nbsp;&nbsp;<input type="button" value="'.__('Get BBCode', 'cigicigi-post-guest').'" style="width: 110px;" onClick="cigicigi_post_guest_doGetBBCode()"></p>
											</div>
										</fieldset>
			';
			}
			$content	.= '
										</form>
								</div>
								<div id="cigicigi_post_guest_media_mask"></div>
							</div>';
		}

		return $content ;
	}
}

if( ! function_exists( 'cigicigi_post_guest_title_slug' ) ) {
	function cigicigi_post_guest_title_slug($string)
	{
		$string	= iconv("UTF-8", "ISO-8859-1//TRANSLIT", $string);
		$string = preg_replace("`\[.*\]`U","",$string);
		$string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$string);
		$string = htmlentities($string, ENT_QUOTES, 'ISO-8859-1');
		$trans	= array(
					"&thorn;"	=> 's',
					"&eth;"		=> 'g',
					"&THORN;"	=> 'S',
					"&ETH;"		=> 'G',
					"&YACUTE;"	=> 'I',
					"&yacute;"	=> 'i'
			);
		$string	= strtr($string, $trans);
		$string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $string );
		$string = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $string);
		return strtolower(trim($string, '-'));
	}
}

if( ! function_exists( 'cigicigi_post_guest_post_security' ) ) {
	function cigicigi_post_guest_post_security($mesaj)
	{
		$mesaj = str_replace(chr(13),"[br]",$mesaj);
		$trans	= array(
						'>'			=> '&gt;',
						'<'			=> '&lt;',
						'"'			=> '&quot;',
						'\''		=> '&rsquo;',
						'\/'		=> '&frasl;',
						'\\'		=> '&#92;',
						'\‘'		=> '&lsquo;',
						'\’'		=> '&rsquo;',
						'\‚'		=> '&sbquo;',
						'\`'		=> '&lsquo;',
						'\“'		=> '&ldquo;',
						'\”'		=> '&rdquo;',
						'\„'		=> '&bdquo;',
						'<3'		=> '&hearts;',
						'\$'		=> '&#36;',
						':++'		=> '&dagger;',
						':)'		=> '&#9786;',
						'=)'		=> '&#9787;',
						':*'		=> '&#9788;',
					);
					
		$mesaj	= strtr($mesaj, $trans);
		return $mesaj;
	}
}

if( ! function_exists( 'cigicigi_post_guest_title_sec' ) ) {
	function cigicigi_post_guest_title_sec($mesaj)
	{
		$trans	= array(
						'>'			=> '&gt;',
						'<'			=> '&lt;',
						'<3'		=> '&hearts;',
						':++'		=> '&dagger;',
						':)'		=> '&#9786;',
						'=)'		=> '&#9787;',
						':*'		=> '&#9788;',
					);
					
		$mesaj	= strtr($mesaj, $trans);
		return $mesaj;
	}
}

if( ! function_exists( 'cigicigi_bbcodetohtml' ) ) {
	function cigicigi_bbcodetohtml($post, $type)
	{

		$simple_search	= array(
							'/\[br\]/is',
							'/\[b\](.*?)\[\/b\]/is',
							'/\[i\](.*?)\[\/i\]/is',
							'/\[u\](.*?)\[\/u\]/is',
							'/\[url\=(.*?)\](.*?)\[\/url\]/is',
							'/\[url\](.*?)\[\/url\]/is',
							'/\[img\](.*?)\[\/img\]/is',
							'/\[more\]/is',
							'/\[align\=(center|right)\](.*?)\[\/align\]/is',
							'/\[size\=(.*?)\](.*?)\[\/size\]/is',
							'/\[\*\](.*?)\[\/\*\]/is',
							'/\[list=ordered\](.*?)\[\/list\]/is',
							'/\[list=unordered\](.*?)\[\/list\]/is',
						);
		$simple_replace	= array(
							'<br />',
							'<strong>$1</strong>',
							'<em>$1</em>',
							'<u>$1</u>',
							'<a href="$1" rel="nofollow" title="$2 - $1" target="_blank">$2</a>',
							'<a href="$1" rel="nofollow" title="$1" target="_blank"">$1</a>',
							'<img src="$1" />',
							'<!--more-->',
							'<p style="text-align: $1;">$2</p>',
							'<h$1>$2</h$1>',
							'<li>$1</li>',
							'<ol>$1</ol>',
							'<ul>$1</ul>',
						);

		$post	= preg_replace($simple_search, $simple_replace, $post);

		$search_upload	= array(
							'/\[upload\=(.*?),(.*?)\](.*?)\[\/upload\]/is',
						);
		$cigicigi_post_guest_media_dir	= get_bloginfo('url').'/wp-content/uploads/cigicigi_post_guest_media';
		if($type == 'read')
		{
			$replace_upload	= array(
							'<img src="'.$cigicigi_post_guest_media_dir.'/temp/$3" class="$1" width="$2"/>',
						);
		}elseif($type == 'publish'){
			$replace_upload	= array(
							'<img src="'.$cigicigi_post_guest_media_dir.'/$3" class="$1" width="$2"/>',
						);	
		}
		$post	= preg_replace($search_upload, $replace_upload, $post);
		
		$match	= preg_match_all("/\{\%\?(.*?)\?\%\}.html/is", $post, $out, PREG_SET_ORDER);
		
		if($match > 0)
		{
			for($i=0;$i<$match;$i++)
			{
				$link	= _cf('temiz_text', $out[$i][1]).".html";
				$post	= strtr($post, array($out[$i][0]=>$link));
			}
		}
		
		return $post;
	}
}

if( ! function_exists( 'cigicigi_post_guest_plugin_action_links' ) ) {
	function cigicigi_post_guest_plugin_action_links( $links, $file )
	{
		static $this_plugin;
		if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin ){
				 $settings_link = '<a href="admin.php?page=cigicigi_post_guest_settings">' . __('Settings') . '</a>';
				 array_unshift( $links, $settings_link );
			}
		return $links;
	}
	add_filter( 'plugin_action_links', 'cigicigi_post_guest_plugin_action_links',10,2);
}

if( ! function_exists( 'cigicigi_post_guest_register_plugin_links' ) ) {
	function cigicigi_post_guest_register_plugin_links($links, $file)
	{
		$base = plugin_basename(__FILE__);
		if ($file == $base) {
			$links[] = '<a href="admin.php?page=cigicigi_post_guest_settings">' . __('Settings') . '</a>';
			$links[] = '<a href="Mailto:info@cigicigi.co">' . __('Support', 'cigicigi-post-guest') . '</a>';
		}
		return $links;
	}
	add_filter( 'plugin_row_meta', 'cigicigi_post_guest_register_plugin_links',10,2);
}

if( ! function_exists( 'cigicigi_post_guest_language_init' ) ) {
	function cigicigi_post_guest_language_init()
	{
		load_plugin_textdomain( 'cigicigi-post-guest', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	add_action('init', 'cigicigi_post_guest_language_init');
}

if( ! function_exists( 'cigicigi_post_guest_uninstall' ) ) {
	function cigicigi_post_guest_uninstall()
	{
		global $wpdb;

		$table_name = $wpdb->prefix . "cigicigi_post_guest";

		$sql = "DROP TABLE ".$table_name;
		
		$wpdb->query($sql);
		
		$cigicigi_post_guest_media_dir	= __DIR__ . '/../../uploads/cigicigi_post_guest_media/temp';
		rmdir($cigicigi_post_guest_media_dir);

		delete_option('cigicigi_post_guest_db_version');
		delete_option( 'cigicigi_post_guest_options');
		delete_option( 'cigicigi_post_guest_count');
	}
	register_uninstall_hook(__FILE__,'cigicigi_post_guest_uninstall');
}

add_shortcode( 'post_guest_cigicigi', 'cigicigi_post_guest_form' );

?>