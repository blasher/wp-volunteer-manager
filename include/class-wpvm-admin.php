<?php
/*

Plugin Name: WP Volunteer Manager
Plugin URI:  http://www.brianlasher.com/
Description: A plugin to manage volunteers and volunteering opportunities
Author:      <a href="http://www.brianlasher.com/">Brian Lasher</a>
Author URI:  http://brianlasher.com

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



if (!class_exists('WPVM_Admin'))
{	class WPVM_Admin
	{
		public static $messages = array();

		// PHP 4 Compatible Constructor
		function WPVM_Admin()
		{	$this->__construct();
		}
		
		// PHP 5 Constructor
		function __construct()
		{
			// Admin Pages
			add_action( 'admin_menu', array(&$this, 'menu_page' ) );
			$this->scripts();
			$this->styles();
		}

		// Load WPVM js scripts
		function scripts()
		{
//		  	wp_enqueue_script( 'jQuery' );
//			wp_enqueue_script( 'WPVM_Admin_JS',   WPVM_URL . '/js/wpvm_admin.js', array('jquery') );
		}	

		// Load WPVM css
		function styles()
		{
			wp_enqueue_style( 'WPVM_Admin_CSS', WPVM_URL . '/css/wpvm_admin.css' );
		}


		function menu_page()
		{
			add_menu_page( 'WP Volunteer Manager', 'WP Volunteer Manager', 'manage_options',
								'wpvm-options', array(&$this, 'my_plugin_menu'), 'dashicons-networking' , 100 );

			add_submenu_page( 'wpvm-options', 'Opportunities', 'Opportunities', 'manage_options',
			                  'wpvm-opportunities', array(&$this, 'wpvm_opportunities_controller') );

			add_submenu_page( 'wpvm-options', 'Commitments', 'Commitments', 'manage_options',
			                  'wpvm-commitments', array(&$this, 'wpvm_commitments_controller') );

			add_submenu_page( 'wpvm-options', 'Reports', 'Reports', 'manage_options',
			                  'wpvm-reports', array(&$this, 'wpvm_reports_controller') );

			add_submenu_page( 'wpvm-options', 'Settings', 'Settings', 'manage_options',
			                  'wpvm-settings', array(&$this, 'wpvm_settings_controller') );

			remove_submenu_page('wpvm-options','wpvm-options');
		}

		function my_plugin_menu()
		{	$this->wpvm_controller();
		}


		function permissions_check()
		{
			if ( current_user_can( 'wpvm_manage_options' ) )
			{	return true;
			}
			else
			{	self::$messages[] = 'You do not have sufficient permissions to access this page.';
			}
		}

		//////////////////////////////////////////////////////////////////////////
		// HEADER AND NAV FUNCTIONS
		//////////////////////////////////////////////////////////////////////////

		function admin_page_header()
		{
			$permissions = $this->permissions_check();

			$out  = '';
			$out .= '<h1>WP Volunteer Manager</h1>';

			$out .= $this->render_messages();
			$out .= $this->admin_notices();

			echo $out;

			if(!$permissions) { wp_die(); }
		}


		function admin_menu()
		{
			$out  = '';
			$out .= '<ul class="wpvm_admin_menu">';
			$out .= '<li class="wpvm_admin_menu_item"><a href="admin.php?page=wpvm-opportunities">Opportunities</a></li>';
			$out .= '<li class="wpvm_admin_menu_item">&middot;</li>';
			$out .= '<li class="wpvm_admin_menu_item"><a href="admin.php?page=wpvm-commitments">Commitments</a></li>';
			$out .= '<li class="wpvm_admin_menu_item">&middot;</li>';
			$out .= '<li class="wpvm_admin_menu_item"><a href="admin.php?page=wpvm-reports">Reports</a></li>';
			$out .= '<li class="wpvm_admin_menu_item">&middot;</li>';
			$out .= '<li class="wpvm_admin_menu_item"><a href="admin.php?page=wpvm-settings">Settings</a></li>';
			$out .= '</ul>';
			$out .= '<div class="clear"></div>';

			echo $out;
		}


		//
		// Returns the list of messages as a string.
		// An empty string is returned if there are no messages.
		// 
		// @return string
		//
		public static function render_messages()
		{	$output = '';
			if ( !empty( self::$messages ) )
			{	$output .= '<div class="wpvm_messages">';
				$output .= implode( '', self::$messages );
				$output .= '</div>';
			}

			return $output;
		}

		//
		// Prints admin notices.
		//
		public static function admin_notices()
		{	$output = '';

			global $wpvm_admin_notices;
			if ( !empty( $wpvm_admin_notices ) )
			{	foreach ( $wpvm_admin_notices as $notice )
				{	$output .= $notice;
				}
			}

			return $output;
		}

		//////////////////////////////////////////////////////////////////////////
		// CONTROLLER FUNCTIONS
		//////////////////////////////////////////////////////////////////////////

		function controller()
		{	
			$this->admin_page_header();
			$this->admin_menu();
		}

		function wpvm_opportunities_controller()
		{	
			require_once( WPVM_PLUGIN_PATH . '/include/view-wpvm-admin-opportunity-edit.php' );
			require_once( WPVM_PLUGIN_PATH . '/include/view-wpvm-admin-opportunity-list.php' );

			$id     = $_REQUEST['id'];
			$action = $_REQUEST['action'];
			$view   = '';

			switch ($action)
			{
				case 'new':
					$data = array();
					$view = new WPVM_View_Admin_Opportunity_Edit();
					$view->populate( $data );
					$view::$data['action'] = 'create';

					break;
				case 'create':
					$opportunity = new WPVM_Opportunity( $id );
					$data = (array) $opportunity->get_data_from_request();

					self::$messages[] = 'IN CREATE';

					if( $_POST['title'] )
					{	self::$messages[] = $opportunity->create( $data );
					}

					$data = (array) $opportunity->read();
					$view = new WPVM_View_Admin_Opportunity_Edit();
					$view->populate( $data );
					$view::$data['action'] = 'save';

					break;
				case 'edit':
					$opportunity = new WPVM_Opportunity( $id );

					self::$messages[] = 'IN EDIT';
					
					$data = (array) $opportunity->read();

					$view = new WPVM_View_Admin_Opportunity_Edit();
					$view->populate( $data );
					$view::$data['action'] = 'save';

					break;
				case 'save':
					$opportunity = new WPVM_Opportunity( $id );

					$obj_data = (array) $opportunity->read();
					$req_data = $opportunity->get_data_from_request();

//					$out .= WPVMUtils::pre_dump($obj_data);
//					$out .= WPVMUtils::pre_dump($req_data);

					$data = array_merge($obj_data, $req_data);

					self::$messages[] = 'IN SAVE';
					self::$messages[] = $opportunity->update( $data );

					$data = (array) $opportunity->read();
					$view = new WPVM_View_Admin_Opportunity_Edit();
					$view->populate( $data );
					$view::$data['action'] = 'save';

					break;
				case 'delete':
					$opportunity = new WPVM_Opportunity( $id );
					self::$messages[] = $opportunity->delete();

					$view = new WPVM_View_Admin_Opportunity_List();
					break;
				default:
					$view = new WPVM_View_Admin_Opportunity_List();
					break;
			}

			$this->admin_page_header();
			$this->admin_menu();

			$view->display();

		}


		function wpvm_commitments_controller()
		{	
			require_once( WPVM_PLUGIN_PATH . '/include/view-wpvm-admin-commitment-edit.php' );
			require_once( WPVM_PLUGIN_PATH . '/include/view-wpvm-admin-commitment-list.php' );

			$id     = $_REQUEST['id'];
			$action = $_REQUEST['action'];
			$view   = '';

			switch ($action)
			{
				case 'new':
					$data = array();
					$view = new WPVM_View_Admin_Commitment_Edit();
					$view->populate( $data );
					$view::$data['action'] = 'create';

					break;
				case 'create':
					$commitment = new WPVM_Commitment( $id );
					$data = (array) $commitment->get_data_from_request();

					if( !$data['volunteer_id'] )
					{
						$data['volunteer_id'] = get_current_user_id();
					}
					else
					{
						$data['status'] = 'Committed';
						self::$messages[] = $commitment->create( $data );
					}

					$data = (array) $commitment->read();
					$view = new WPVM_View_Admin_Commitment_Edit();
					$view->populate( $data );
					$view::$data['action'] = 'save';

					break;
				case 'edit':
					$commitment = new WPVM_Commitment( $id );

					self::$messages[] = 'IN EDIT';

					$data = (array) $commitment->read();

					$view = new WPVM_View_Admin_Commitment_Edit();
					$view->populate( $data );
					$view::$data['action'] = 'save';

					break;
				case 'save':
					$commitment = new WPVM_Commitment( $id );

					$obj_data = (array) $commitment->read();
					$req_data = $commitment->get_data_from_request();

//					$out .= WPVMUtils::pre_dump($obj_data);
//					$out .= WPVMUtils::pre_dump($req_data);

					$data = array_merge($obj_data, $req_data);

					self::$messages[] = 'IN SAVE';
					self::$messages[] = $commitment->update( $data );

					$data = (array) $commitment->read();
					$view = new WPVM_View_Admin_Commitment_Edit();
					$view->populate( $data );
					$view::$data['action'] = 'save';

					break;
				case 'inout':
					$commitment = new WPVM_Commitment( $id );
					self::$messages[] = $commitment->inout();

					$data = (array) $commitment->read();
					$view = new WPVM_View_Admin_Commitment_List();
					$view->populate( $data );
					$view::$data['action'] = 'save';

					break;
				case 'approve':
					$commitment = new WPVM_Commitment( $id );
					self::$messages[] = $commitment->approve();

					$data = (array) $commitment->read();
					$view = new WPVM_View_Admin_Commitment_List();
					$view->populate( $data );
					$view::$data['action'] = 'save';

					break;
				case 'delete':
					$commitment = new WPVM_Commitment( $id );
					self::$messages[] = $commitment->delete();

					$view = new WPVM_View_Admin_Commitment_List();
					break;
				default:
					$view = new WPVM_View_Admin_Commitment_List();
					break;
			}

			$this->admin_page_header();
			$this->admin_menu();

			$view->display();

		}


		function wpvm_reports_controller()
		{	
			$this->admin_page_header();
			$this->admin_menu();

			echo '<h3>Coming Soon</h3>';

		}

		function wpvm_settings_controller()
		{	
			require_once( WPVM_PLUGIN_PATH . '/include/view-wpvm-admin-settings.php' );

			$view = new WPVM_View_Admin_Settings();

			$this->admin_page_header();
			$this->admin_menu();

			$view->display();

		}

	}
}

$wpvm_admin = new WPVM_Admin();

?>