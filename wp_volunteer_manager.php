<?php
/*

Plugin Name: WP Volunteer Manager
Plugin URI:  http://www.brianlasher.com/
Description: A plugin to manage volunteers and volunteering opportunities
Author:      <a href="http://www.brianlasher.com/">Brian Lasher</a>
Author URI:  http://brianlasher.com
Version:     0.4.002

**************************************************************************

Copyright 2012  Brian Lasher ( me@brianlasher.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software

Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}


// Define the plugin's version information
define( 'WPVM_VERSION', '0.4');
define( 'WPVM_MINOR_VERSION', '.002');
define( 'WPVM_PRESENTABLE_VERSION', WPVM_VERSION . WPVM_MINOR_VERSION );

// Define the URL to the plugin folder
define( 'WPVM_URL', plugins_url( '', __FILE__ ) );

// Define the path to the plugin folder
define( 'WPVM_PLUGIN_PATH', dirname( __FILE__ ) );

require_once( 'include/class-wpvm-utils.php' );
require_once( 'include/class-wpvm-object-in-db.php' );
require_once( 'include/class-wpvm-volunteer.php' );
require_once( 'include/class-wpvm-opportunity.php' );
require_once( 'include/class-wpvm-opportunity-list.php' );
require_once( 'include/class-wpvm-commitment.php' );
require_once( 'include/class-wpvm-commitment-list.php' );
require_once( 'include/class-wpvm-ajax.php' );
require_once( 'include/class-wpvm-shortcodes.php' );

if (!class_exists("WPVolunteerManager"))
{	class WPVolunteerManager
	{
		public static $db_init_sql = array();

		// PHP 4 Compatible Constructor
		function WPVolunteerManager()
		{	$this->__construct();
		}
		
		// PHP 5 Constructor
		function __construct()
		{
			// Load modules ASAP
			add_action('plugins_loaded', array(&$this, 'init'), 1);

			// Register activation / deactivation hooks.
			register_activation_hook(__FILE__, array(&$this, 'activate'));
			register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
		}


		function init()
		{  $this->scripts();
		   $this->styles();

			if(is_admin())
			{
				require_once('include/class-wpvm-admin.php');
			}

			$this->init_hooks();
		}

		function activate()
		{  
			$this->define_capabilities();
			$this->define_db_init_sql();
			$this->sync_db_tables();
			$this->create_profile_page();
		}

		function deactivate()
		{  
		}

		/**
		 * Hook into actions and filters
		 * @since  2.3
		 */
		private function init_hooks()
		{
//			register_activation_hook( __FILE__, array( 'WPVM_Install', 'install' ) );
			add_action( 'init', array( 'WPVM_Shortcodes', 'init' ) );
//			add_action( 'init', array( 'WPVM_Emails', 'init_transactional_emails' ) );
		}


		// Load wpvm scripts used form both admin and front end
		function scripts()
		{
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'underscore' );
			wp_enqueue_script( 'backbone' );

			if(!is_admin() )
			{	wp_enqueue_script( 'bootstrap' );
			}

			wp_enqueue_script( 'WPVM_DATEPICKER_JS',          WPVM_URL . '/lib/jquery-simple-datetimepicker-1.12.0/jquery.simple-dtpicker.js', array('jquery') );
			wp_enqueue_script( 'WPVM_STACKTABLE_JS',          WPVM_URL . '/js/stacktable.min.js', array('jquery') );
			wp_enqueue_script( 'WPVM_JS',                     WPVM_URL . '/js/wpvm.js', array('jquery') );
			wp_enqueue_script( 'WPVM_APP_JS',                 WPVM_URL . '/js/wpvm_app.js', array('jquery') );

			$local = array('site_url' => site_url(),
			               'ajax_url' => admin_url( 'admin-ajax.php' ),
			               'wpvm_url' => WPVM_URL );

			wp_localize_script( 'WPVM_JS',                          'wpvm_vars', $local );

		}	

		// Load wpvm styles used form both admin and front end
		function styles()
		{
			wp_enqueue_style( 'WPVM_CSS',              WPVM_URL . '/css/wpvm.css' );
			wp_enqueue_style( 'WPVM_DATEPICKER_CSS',   WPVM_URL . '/lib/jquery-simple-datetimepicker-1.12.0/jquery.simple-dtpicker.css' );
//			wp_enqueue_style( 'WPVM_BOOTSTRAP_CSS',    WPVM_URL . '/lib/bootstrap/css/bootstrap.min.css' );
		}


		function define_capabilities()
		{
			$role = get_role( 'administrator' );
			$role->add_cap( 'wpvm_manage_options' ); 

			$role = get_role( 'editor' );
			$role->add_cap( 'wpvm_manage_options' ); 
		}


		function define_db_init_sql()
		{
			self::$db_init_sql['wpvm_opportunities'] = <<<'EOT'
CREATE TABLE <TABLE_NAME> (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `type` varchar(40) DEFAULT NULL,
  `location` varchar(200) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `duration` bigint(20) NOT NULL,
  `min_participants` char(10) NOT NULL,
  `max_participants` char(10) NOT NULL,
  `notes` varchar(200),
  `creator_id` bigint(20) NOT NULL,
  `date_created` datetime NOT NULL,
  `last_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
EOT;

			self::$db_init_sql['wpvm_commitments'] = <<<'EOT'
CREATE TABLE <TABLE_NAME> (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `opportunity_id` bigint(20) NOT NULL,
  `volunteer_id` bigint(20) NOT NULL,
  `signin_time` datetime,
  `signout_time` datetime,
  `status` varchar(40),
  `notes` varchar(200),
  `creator_id` bigint(20) NOT NULL,
  `date_created` datetime NOT NULL,
  `last_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
EOT;
		}

		function sync_db_tables()
		{
			$tables = array_keys(self::$db_init_sql);

			foreach($tables as $table)
			{
				$this->sync_db_table($table);
			}
		}

		function sync_db_table($table)
		{  
			global $wpdb;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$table_name      = $wpdb->prefix . $table; 
			$charset_collate = $wpdb->get_charset_collate();

			$sql = self::$db_init_sql[$table];

			$pattern = '/<TABLE_NAME>/';
			$sql = preg_replace($pattern, $table_name, $sql);

			$pattern = '/\<CHARSET_COLLATE\>/';
			$sql = preg_replace($pattern, $charset_collate, $sql);

			dbDelta( $sql );
		}

		function create_profile_page()
		{  
			// Create post object
			$my_post = array(
			     'post_title' => 'WPVM_Profile',
			     'post_content' => '[wpvm_my_profile]',
			     'post_status' => 'publish',
			     'post_author' => 1,
			     'post_type' => 'page' /* this actually makes the entire backend to disappear so I have to put it in $defaults=array( for it to reappear. The page doesn't always want to show up though.*/
			);

			// Insert the post into the database
			$wpvm_profile_page_id = wp_insert_post( $my_post );
		}

		function plugin_check()
		{
			$out = '';

//			$required = ['Groups'];

			if ( ! function_exists( 'get_plugins' ) )
			{
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$all_plugins = get_plugins();

//			$out .= print_r($all_plugins, true);

			return $out;
		}

	}
}

$wpvm = new WPVolunteerManager();

?>