<?php
/*
Plugin Name: UserLook - real time analytics for your blog
Plugin URI: http://www.userlook.com
Description: This plugin provide an easy way to use Userlook service on Wordpress
Version: 1.0.3
Author: UserLook
Author URI: http://www.userlook.com
License: GPLv2
*/
/*
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
define( 'UL_URL', plugins_url() . '/userlook' );

class UserLook {
	public function __construct() {
		load_plugin_textdomain( 'UserLook', false, basename( dirname( __FILE__ ) ) . '/lang' );

		add_action( 'admin_menu', array( $this, 'add_menu' ), 20 );
		add_action( 'wp_footer', array( $this, 'embed_tracking_code' ) );
	}

	public function add_menu() {
		$options = $this->get_options();
		add_menu_page( 'UserLook' . __( 'Options' ), 'UserLook', 'manage_options', 'userlook', array( $this, 'set_options' ), UL_URL . '/favicon.ico' );
	}

	public function set_options() {
		if ( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$_REQUEST += array( 'ul_action' => '' );
		$action = $_REQUEST['ul_action'];

		if ( $action === 'delete' && check_admin_referer( 'ul-delete-options' ) ) {
			delete_option( 'UserLook_options' );
		}

		if ( $action === 'edit' && check_admin_referer( 'userlook' ) ) {
			$options = $this->get_options();
			$orig_options = $options;

			if ( !empty( $_POST['ul_domain'] ) ) { $options['ul_domain'] = $_POST['ul_domain']; }
			if ( !empty( $_POST['ul_site_id'] ) ) { $options['ul_site_id'] = $_POST['ul_site_id']; }
				
			if ( $orig_options !== $options ) {
				update_option( 'UserLook_options', $options );
			}
		}

		echo $this->meta_configuration_content();
	}


	function meta_configuration_content() {
		$options = $this->get_options();
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>UserLook settings</h2>
<h3>Main settings:</h3>

		<form method="post" action="">
			<?php wp_nonce_field( 'userlook' ); ?>
			<input type="hidden" name="ul_action" value="edit" />
			<div class="table">
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th><?php _e( 'Your blog domain', 'UserLook' ); ?></th>
							<td>
								<input id="ul_domain" name="ul_domain" type="text" class="regular-text" value="<?php echo esc_attr( $options['ul_domain'] ); ?>" />
								<span class="description"><?php _e( 'Your Blog url.', 'UserLook' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th><?php _e( 'Your site ID in UserLook', 'UserEcho' ); ?></th>
							<td>
								<input id="ul_site_id" name="ul_site_id" type="text" class="regular-text" value="<?php echo esc_attr( $options['ul_site_id'] ); ?>" />
								<span class="description">Goto <a href="http://userlook.com/sites/" target="blank">http://userlook.com/sites/</a> then click on <strong>Settings</strong> button, look for Wordpress integration section to find out ID</span>
							</td>
						</tr>
					</tbody>
				</table>

				<p class="submit"><input type="submit" class="button-primary" value="Save Options" /></p>
				<p class="submit"><input type="button" onclick="if(confirm('<?php _e( 'Are you sure you want to reset options to default values?' ); ?>')) location.href='<?php echo wp_nonce_url( admin_url( 'admin.php?page=userlook&ul_action=delete' ), 'ul-delete-options' ); ?>';" class="button-primary" value="Reset Configuration" /></p>
				<div class="clear"></div>
			</div>
		</form>

<h2>Link to your Live Dashboard</h2>
<a href="http://userlook.com/dashboard/<?php echo $options['ul_site_id'];?>/" target="blank">http://userlook.com/dashboard/<?php echo $options['ul_site_id'];?>/</a>
	</div>
<?php
}

	// This function returns the current options
	public function get_options() {
		static $default = null;
		if ( !isset( $default ) ) {
			$default = array(
				'ul_domain' => 'userlook.com',
				'ul_site_id' => '495',
				
			);
		}

		$saved = get_option( 'UserLook_options' );

		$options = array();
		if ( !empty( $saved ) ) {
			$options = $saved;
		}

		$options += $default;

		if ( $saved != $options ) {
			update_option( 'UserLook_options', $options );
		}

		return $options;
	}

	function embed_tracking_code()  {
		$options = $this->get_options();
	    $userlook_code = "<script type=\"text/javascript\">var _uls_ls=(new Date()).getTime()</script>
	    <script type=\"text/javascript\">
			var _uls={acc:".$options['ul_site_id'].",host:\"".$options['ul_domain']."\", server:\"io.userlook.com\"};
			(function(){
			  function loadUL() {
			    window._uls_le=(new Date()).getTime();
			    var _ul = document.createElement('script'); _ul.type = 'text/javascript'; _ul.async = true;
			    _ul.src = 'http://cdn.userlook.com/js/userlook.js';
			    document.body.appendChild(_ul);
			  }
			var ol_old = window.onload;
			window.onload = (typeof window.onload != 'function') ? loadUL : function() { ol_old(); loadUL(); };
			})();

			</script>
		";
	    print $userlook_code;
		}

}

$ul = new UserLook();
