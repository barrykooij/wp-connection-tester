<?php

/*
	Plugin Name: WP Connection Tester
	Plugin URI: http://www.barrykooij.com/
	Description: Check if a WordPress website is able to connect to one of the license servers.
	Version: 1.0.0
	Author: Barry Kooij
	Author URI: http://www.barrykooij.com/
	License: GPL v3

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class WP_Connection_Tester {

	private $urls = array(
		'rp4wp' => 'https://www.relatedpostsforwp.com/',
		'dlm'   => 'https://www.download-monitor.com/',
		'pc'    => 'https://www.post-connector.com/',
	);

	private $admin_message = array();

	/**
	 * Setup plugin
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
		add_action( 'admin_init', array( $this, 'catch_post' ) );
		add_action( 'admin_notices', array( $this, 'admin_messages' ) );
	}

	/**
	 * Add submenu page
	 */
	public function add_submenu_page() {
		add_submenu_page( 'options-general.php', 'WP Connection Tester', 'WP Connection Tester', 'manage_options', 'wp_connection_tester', array(
			$this,
			'screen'
		) );
	}

	/**
	 * Output the screen
	 */
	public function screen() {
		?>
		<div class="wrap" xmlns="http://www.w3.org/1999/html">

			<div class="rp4wp-content">
				<h2>Never5 Connection Tester</h2>

				<?php
				if ( function_exists( 'curl_version' ) ) {
					$curl_version = curl_version();
					$curl_status  = '<span style="font-weight:bold;color:#ff0000;">INCOMPATIBLE</span>';
					if ( version_compare( $curl_version['version'], '7.18.1', '>=' ) ) {
						$curl_status = '<span style="font-weight:bold;color:#00ff00;">COMPATIBLE</span>';
					}
					?>
					<p><strong>Curl:</strong> <?php echo $curl_version['version'] . ' ' . $curl_status; ?></p>
				<?php
				} else {
					echo 'curl_version() doesn\'t exist';
				}
				?>

				<form method="post"
				      action="<?php echo admin_url( 'options-general.php?page=wp_connection_tester' ); ?>">
					<p><label for="rp4wp"><input type="radio" name="bk_site" value="rp4wp" id="rp4wp" placeholder=""/>
							Related Post for WordPress</label></p>

					<p><label for="dlm"><input type="radio" name="bk_site" value="dlm" id="dlm" placeholder=""/>
							Download Monitor</label></p>

					<p><label for="pc"><input type="radio" name="bk_site" value="pc" id="pc" placeholder=""/> Post
							Connector</label></p>

					<p><input type="submit" name="bk_active" value="Test"
					          class="button button-primary"/></p>
				</form>
			</div>
		</div>
	<?php
	}

	/**
	 * Catch activation call
	 */
	public
	function catch_post() {

		if ( isset( $_POST['bk_site'] ) && isset( $this->urls[ $_POST['bk_site'] ] ) ) {

			$url = $this->urls[ $_POST['bk_site'] ];

			$result = wp_remote_get( $url );

			if ( ! is_wp_error( $result ) ) {
				$this->admin_message = array(
					'message' => sprintf( 'Connection to %s successful!', $url ),
					'type'    => 'updated'
				);
			} else {
				$this->admin_message = array(
					'message' => sprintf( 'Connection to %s failed! <br/> %s', $url, print_r( $result, 1 ) ),
					'type'    => 'updated'
				);
			}


		}

	}

	/**
	 * Display message
	 */
	public
	function admin_messages() {
		if ( count( $this->admin_message ) > 0 ) {
			?>
			<div class="<?php echo $this->admin_message['type']; ?>">
				<p><?php echo $this->admin_message['message']; ?></p>
			</div>
		<?php
		}
	}

}

function __wp_connection_tester() {
	new WP_Connection_Tester();
}

add_action( 'plugins_loaded', '__wp_connection_tester', 11 );