<?php
/**
 * Notify Old Blog
 *
 * @package    Notify Old Blog
 * @subpackage NotifyOldBlogAdmin Management screen
/*
	Copyright (c) 2019- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$notifyoldblogadmin = new NotifyOldBlogAdmin();

/** ==================================================
 * Management screen
 */
class NotifyOldBlogAdmin {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_action( 'notify_old_blog_settings', array( $this, 'register_settings' ) );

		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );
	}

	/** ==================================================
	 * Add a "Settings" link to the plugins page
	 *
	 * @param  array  $links  links array.
	 * @param  string $file   file.
	 * @return array  $links  links array.
	 * @since 1.00
	 */
	public function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = 'notify-old-blog/notifyoldblog.php';
		}
		if ( $file == $this_plugin ) {
			$links[] = '<a href="' . admin_url( 'options-general.php?page=NotifyOldBlog' ) . '">' . __( 'Settings' ) . '</a>';
		}
		return $links;
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_menu() {
		add_options_page( 'NotifyOldBlog Options', 'Notify Old Blog', 'manage_options', 'NotifyOldBlog', array( $this, 'plugin_options' ) );
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_options() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}

		$this->options_updated();

		$scriptname   = admin_url( 'options-general.php?page=NotifyOldBlog' );
		$nob_settings = get_option( 'notify_old_blog' );

		?>
		<div class="wrap">
		<h2>NotifyOldBlog <?php esc_html_e( 'Settings' ); ?></h2>

			<details>
			<summary><strong><?php esc_html_e( 'Various links of this plugin', 'notify-old-blog' ); ?></strong></summary>
			<?php $this->credit(); ?>
			</details>

			<div style="padding:10px;">
				<form style="padding:10px;" method="post" action="<?php echo esc_url( $scriptname ); ?>" />
					<?php wp_nonce_field( 'nob_settings', 'notifyoldblog_settings' ); ?>
					<h3><?php esc_html_e( 'Current and last updated interval limit', 'notify-old-blog' ); ?></h3>
					<div style="margin: 5px; padding: 5px;">
						<input type="number" name="check_interval" min="1" step="1" value="<?php echo esc_attr( $nob_settings['check_interval'] ); ?>"> <?php esc_html_e( 'Days', 'notify-old-blog' ); ?>
					</div>
					<h3><?php esc_html_e( 'Notify mail', 'notify-old-blog' ); ?></h3>
					<div style="margin: 5px; padding: 5px;">
						<?php esc_html_e( 'Subject' ); ?>
						<input type="text" name="mail_subject" style="width: 100%;" value="<?php echo esc_attr( $nob_settings['mail_subject'] ); ?>">
					</div>
					<div style="margin: 5px; padding: 5px;">
						<?php esc_html_e( 'Header' ); ?>
						<textarea name="mail_head" style="width: 100%;"><?php echo esc_textarea( $nob_settings['mail_head'] ); ?></textarea>
					</div>
					<div style="margin: 5px; padding: 5px;">
						<?php esc_html_e( 'Content' ); ?> : 
						<?php
						/* translators: %d is the number of days exceeded. */
						esc_html_e( '%d is the number of days exceeded.', 'notify-old-blog' );
						?>
						<textarea name="mail_caution" style="width: 100%;"><?php echo esc_textarea( $nob_settings['mail_caution'] ); ?></textarea>
					</div>
					<div style="margin: 5px; padding: 5px;">
						<?php esc_html_e( 'Hour & minute for notify', 'notify-old-blog' ); ?> : 
						<?php esc_html_e( 'After this time, it will be the time when the site was first accessed.', 'notify-old-blog' ); ?>
						<div>
							<input type="number" name="hour" min="0" max="24" step="1" style="width: 80px;" value="<?php echo esc_attr( $nob_settings['hour'] ); ?>"> <?php esc_html_e( 'Hour' ); ?>
							<input type="number" name="minute" min="0" max="60" step="1" style="width: 80px;" value="<?php echo esc_attr( $nob_settings['minute'] ); ?>"> <?php esc_html_e( 'Minute' ); ?>
						</div>
					</div>
					<div style="margin: 15px; padding: 15px;">
						<?php submit_button( __( 'Save Changes' ), 'large', 'notifyoldblog-settings-apply', false ); ?>
						<?php submit_button( __( 'Default' ), 'large', 'default', false ); ?>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	/** ==================================================
	 * Credit
	 *
	 * @since 1.00
	 */
	private function credit() {

		$plugin_name    = null;
		$plugin_ver_num = null;
		$plugin_path    = plugin_dir_path( __DIR__ );
		$plugin_dir     = untrailingslashit( $plugin_path );
		$slugs          = explode( '/', wp_normalize_path( $plugin_dir ) );
		$slug           = end( $slugs );
		$files          = scandir( $plugin_dir );
		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file || is_dir( $plugin_path . $file ) ) {
				continue;
			} else {
				$exts = explode( '.', $file );
				$ext  = strtolower( end( $exts ) );
				if ( 'php' === $ext ) {
					$plugin_datas = get_file_data(
						$plugin_path . $file,
						array(
							'name'    => 'Plugin Name',
							'version' => 'Version',
						)
					);
					if ( array_key_exists( 'name', $plugin_datas ) && ! empty( $plugin_datas['name'] ) && array_key_exists( 'version', $plugin_datas ) && ! empty( $plugin_datas['version'] ) ) {
						$plugin_name    = $plugin_datas['name'];
						$plugin_ver_num = $plugin_datas['version'];
						break;
					}
				}
			}
		}
		$plugin_version = __( 'Version:' ) . ' ' . $plugin_ver_num;
		/* translators: FAQ Link & Slug */
		$faq       = sprintf( __( 'https://wordpress.org/plugins/%s/faq', 'notify-old-blog' ), $slug );
		$support   = 'https://wordpress.org/support/plugin/' . $slug;
		$review    = 'https://wordpress.org/support/view/plugin-reviews/' . $slug;
		$translate = 'https://translate.wordpress.org/projects/wp-plugins/' . $slug;
		$facebook  = 'https://www.facebook.com/katsushikawamori/';
		$twitter   = 'https://twitter.com/dodesyo312';
		$youtube   = 'https://www.youtube.com/channel/UC5zTLeyROkvZm86OgNRcb_w';
		$donate    = __( 'https://shop.riverforest-wp.info/donate/', 'notify-old-blog' );

		?>
		<span style="font-weight: bold;">
		<div>
		<?php echo esc_html( $plugin_version ); ?> | 
		<a style="text-decoration: none;" href="<?php echo esc_url( $faq ); ?>" target="_blank" rel="noopener noreferrer">FAQ</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $support ); ?>" target="_blank" rel="noopener noreferrer">Support Forums</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $review ); ?>" target="_blank" rel="noopener noreferrer">Reviews</a>
		</div>
		<div>
		<a style="text-decoration: none;" href="<?php echo esc_url( $translate ); ?>" target="_blank" rel="noopener noreferrer">
		<?php
		/* translators: Plugin translation link */
		echo esc_html( sprintf( __( 'Translations for %s' ), $plugin_name ) );
		?>
		</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-facebook"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-twitter"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $youtube ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-video-alt3"></span></a>
		</div>
		</span>

		<div style="width: 250px; height: 180px; margin: 5px; padding: 5px; border: #CCC 2px solid;">
		<h3><?php esc_html_e( 'Please make a donation if you like my work or would like to further the development of this plugin.', 'notify-old-blog' ); ?></h3>
		<div style="text-align: right; margin: 5px; padding: 5px;"><span style="padding: 3px; color: #ffffff; background-color: #008000">Plugin Author</span> <span style="font-weight: bold;">Katsushi Kawamori</span></div>
		<button type="button" style="margin: 5px; padding: 5px;" onclick="window.open('<?php echo esc_url( $donate ); ?>')"><?php esc_html_e( 'Donate to this plugin &#187;' ); ?></button>
		</div>

		<?php
	}

	/** ==================================================
	 * Update wp_options table.
	 *
	 * @since 1.00
	 */
	private function options_updated() {

		if ( isset( $_POST['notifyoldblog-settings-apply'] ) && ! empty( $_POST['notifyoldblog-settings-apply'] ) ) {
			if ( check_admin_referer( 'nob_settings', 'notifyoldblog_settings' ) ) {
				$nob_settings = get_option( 'notify_old_blog' );
				if ( ! empty( $_POST['check_interval'] ) ) {
					$nob_settings['check_interval'] = intval( $_POST['check_interval'] );
				}
				if ( ! empty( $_POST['mail_subject'] ) ) {
					$nob_settings['mail_subject'] = sanitize_text_field( wp_unslash( $_POST['mail_subject'] ) );
				}
				if ( ! empty( $_POST['mail_head'] ) ) {
					$nob_settings['mail_head'] = sanitize_text_field( wp_unslash( $_POST['mail_head'] ) );
				}
				if ( ! empty( $_POST['mail_caution'] ) ) {
					$nob_settings['mail_caution'] = sanitize_text_field( wp_unslash( $_POST['mail_caution'] ) );
				}
				if ( ! empty( $_POST['hour'] ) ) {
					$nob_settings['hour'] = intval( $_POST['hour'] );
				}
				if ( ! empty( $_POST['minute'] ) ) {
					$nob_settings['minute'] = intval( $_POST['minute'] );
				}
				update_option( 'notify_old_blog', $nob_settings );
				echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html__( 'Settings' ) . ' --> ' . esc_html__( 'Settings saved.' ) . '</li></ul></div>';
				wp_clear_scheduled_hook( 'notify_old_blog_cron' );
				do_action( 'notify_old_blog_cron_start' );
			}
		}

		if ( isset( $_POST['default'] ) && ! empty( $_POST['default'] ) ) {
			if ( check_admin_referer( 'nob_settings', 'notifyoldblog_settings' ) ) {
				delete_option( 'notify_old_blog' );
				$this->register_settings();
				echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html__( 'Settings' ) . ' --> ' . esc_html__( 'Default' ) . '</li></ul></div>';
				wp_clear_scheduled_hook( 'notify_old_blog_cron' );
				do_action( 'notify_old_blog_cron_start' );
			}
		}
	}

	/** ==================================================
	 * Settings register
	 *
	 * @since 1.01
	 */
	public function register_settings() {

		if ( ! get_option( 'notify_old_blog' ) ) {
			$nob_tbl = array(
				'check_interval' => 7,
				'mail_subject' => 'Notify Old Blog - ' . get_option( 'blogname' ),
				'mail_head' => __( 'This email is delivered to the administrator by the Notify Old Blog.', 'notify-old-blog' ),
				/* translators: %d is the number of days exceeded. */
				'mail_caution' => __( '%d days have passed since the last post.', 'notify-old-blog' ),
				'hour' => 0,
				'minute' => 0,
			);
			update_option( 'notify_old_blog', $nob_tbl );
		}
	}
}


