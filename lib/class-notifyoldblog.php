<?php
/**
 * Notify Old Blog
 *
 * @package    Notify Old Blog
 * @subpackage NotifyOldBlog Main Functions
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

$notifyoldblog = new NotifyOldBlog();

/** ==================================================
 * Main Functions
 */
class NotifyOldBlog {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_action( 'notify_old_blog_cron', array( $this, 'notify_old_blog_wp_cron' ) );
		add_action( 'notify_old_blog_cron_start', array( $this, 'cron_start' ) );
		register_activation_hook( plugin_dir_path( __DIR__ ) . 'notifyoldblog.php', array( $this, 'cron_start' ) );
		register_deactivation_hook( plugin_dir_path( __DIR__ ) . 'notifyoldblog.php', array( $this, 'cron_stop' ) );
	}

	/** ==================================================
	 * Cron Start
	 *
	 * @return bool $result fail >> false | success >> void
	 * @since 1.00
	 */
	public function cron_start() {

		if ( ! wp_next_scheduled( 'notify_old_blog_cron' ) ) {
			$result = wp_schedule_event( time() + $this->notify_diff_seconds(), 'daily', 'notify_old_blog_cron' );
		} else {
			wp_clear_scheduled_hook( 'notify_old_blog_cron' );
			$result = wp_schedule_event( time() + $this->notify_diff_seconds(), 'daily', 'notify_old_blog_cron' );
		}

		return $result;
	}

	/** ==================================================
	 * Cron Stop
	 *
	 * @since 1.00
	 */
	public function cron_stop() {

		wp_clear_scheduled_hook( 'notify_old_blog_cron' );
	}

	/** ==================================================
	 * Notify Old Blog wp cron for notify mail
	 *
	 * @since 1.00
	 */
	public function notify_old_blog_wp_cron() {

		$nob_settings = get_option( 'notify_old_blog' );

		$adminmail = get_option( 'admin_email' );
		$subject   = $nob_settings['mail_subject'];

		$caution = $this->notify_old_blog_caution( $nob_settings );
		if ( $caution ) {
			$message  = $nob_settings['mail_head'] . "\r\n\r\n";
			$message .= $caution . "\r\n\r\n";
			$message .= __( 'Site Address (URL)' ) . ' : ' . site_url() . "\r\n\r\n";

			wp_mail( $adminmail, $subject, $message );
		}
	}

	/** ==================================================
	 * Notify Old Blog Caution
	 *
	 * @param array $nob_settings  nob_settings.
	 * @return string $caution  caution.
	 * @since 1.00
	 */
	private function notify_old_blog_caution( $nob_settings ) {

		$caution  = false;
		$check_interval = $nob_settings['check_interval'] * 86400;

		$last_post = strtotime( get_lastpostdate( 'gmt' ) );
		$current   = time();
		$interval  = $current - $last_post;

		if ( $check_interval < $interval ) {
			$caution = sprintf( $nob_settings['mail_caution'], floor( $interval / 86400 ) );
		}

		return $caution;
	}

	/** ==================================================
	 * Notify Diff Seconds
	 *
	 * @return float $diff  diff.
	 * @since 1.02
	 */
	private function notify_diff_seconds() {

		do_action( 'notify_old_blog_settings' );

		$nob_settings = get_option( 'notify_old_blog' );

		$setting_time   = $nob_settings['hour'] + $nob_settings['minute'] * ( 1 / 60 );
		if ( function_exists( 'wp_date' ) ) {
			$current_hour   = intval( wp_date( 'H' ) );
			$current_minute = intval( wp_date( 'i' ) ) * ( 1 / 60 );
		} else {
			$current_hour   = intval( date_i18n( 'H' ) );
			$current_minute = intval( date_i18n( 'i' ) ) * ( 1 / 60 );
		}
		$current_time   = $current_hour + $current_minute;
		$diff_time      = $setting_time - $current_time;
		if ( 0 > $diff_time ) {
			$diff_time = $diff_time + 24;
		}
		$diff = $diff_time * 3600;

		return $diff;
	}
}
