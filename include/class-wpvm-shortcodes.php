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


if (!class_exists('WPVM_Shortcodes'))
{	class WPVM_Shortcodes
	{
		public static function init()
		{	$shortcodes = array(
				'wpvm_debug'                          => __CLASS__ . '::debug',

				'wpvm_volunteer_list'                 => __CLASS__ . '::volunteer_list',
				'wpvm_volunteer_edit_form'            => __CLASS__ . '::volunteer_edit_form',
				'wpvm_volunteer_profile'              => __CLASS__ . '::volunteer_profie',

				'wpvm_my_open_opportunities'          => __CLASS__ . '::wpvm_my_open_opportunities',
				'wpvm_my_upcoming_commitments'        => __CLASS__ . '::wpvm_my_upcoming_commitments',
				'wpvm_my_prior_commitments'           => __CLASS__ . '::wpvm_my_prior_commitments',

				'wpvm_report_volunteer_hours_total'   => __CLASS__ . '::wpvm_report_volunteer_hours_total',
				'wpvm_report_volunteer_hours_ytd'     => __CLASS__ . '::wpvm_report_volunteer_hours_ytd',
				'wpvm_report_volunteer_hours_qtd'     => __CLASS__ . '::wpvm_report_volunteer_hours_qtd',
				'wpvm_report_volunteer_hours_mtd'     => __CLASS__ . '::wpvm_report_volunteer_hours_mtd',
			);
	
			foreach ( $shortcodes as $shortcode => $function )
			{	add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
			}
		}


		//////////////////////////////////////////////
		// VOLUNTEER SHORTCODES
		//////////////////////////////////////////////

		public static function wpvm_volunteer_list()
		{
			$out = '';

			$out .= '<h2>Volunteers</h2>';

			$current_user_id = get_current_user_id();
			$list = new WPVM_Volunteer_List($current_user_id);
			$list->fetch();
//			$out .= $list->dump();
			$out .= $list->display();

//			$out .= '<h3>here</h3>';

			return $out;
		}


		public static function wpvm_volunteer_edit_form()
		{
			if(!class_exists('WPVM_Volunteer_Edit_Form'))
			{	require_once( WPVM_PLUGIN_PATH . '/include/class-wpvm-volunteer-edit-form.php' );
			}

			$action = $_REQUEST['action'];
			$id     = $_REQUEST['id'];

			switch ($action)
			{
				case 'create':
					echo '<h2>Create New Volunteer</h2>';

					$volunteer = new WPVM_Volunteer( $id );
					echo $volunteer->create();
					break;

				case 'edit':
					echo '<h2>Edit Volunteer</h2>';

					$volunteer = new WPVM_Volunteer( $id );
					$data = (array) $volunteer->fetch();
					break;

				case 'save':
					echo '<h2>Save Volunteer</h2>';

					$volunteer = new WPVM_Volunteer( $id );
					$volunteer->save();
					break;

				case 'delete':
					echo '<h2>Delete Volunteer '.$id.'</h2>';

					$volunteer = new WPVM_Volunteer( $id );
					$volunteer->delete();
					break;
			}


			$form = new WPVM_Volunteer_Edit_Form();

//			$form->populate( self::$data );
			$form->set_action( 'create' );
			$form->hide_user1_and_set_to_current_user();
			$out = $form->display();

			return $out;
		}


		public static function volunteer_profile()
		{
			$out = '';
			$out .= $this->wpvm_my_open_opportunities;
			$out .= $this->wpvm_my_upcoming_commitments;
			$out .= $this->wpvm_my_prior_commitments;

			return $out;
		}


		//////////////////////////////////////////////
		// OPPORTUNITY SHORTCODES
		//////////////////////////////////////////////

		public static function wpvm_my_open_opportunities()
		{
			wp_enqueue_script( 'WPVM_APP_MY_OPEN_OPP_JS',     WPVM_URL . '/js/wpvm_app_my_open_opportunities.js', array('WPVM_APP_JS') );

			$out = '';
			$out .= '<h3>My Open Opportunities</h3>';
			$out .= '<p>To find out more about one of the open opportunities below, simply click the opportunity name.  If you would like to signup for that opportunity, click the corresponding green plus icon.</p>';
			$out .= '<p>&nbsp;</p>';
			$out .= '<div id="wpvm_my_open_opportunities" style="text-align:center;">';
			$out .= '<img src="'. WPVM_URL .'/images/wpspin_light.gif" style="border:0">';
			$out .= '</div>';
			$out .= '<script type="text/javascript" src="'. WPVM_URL. '/js/wpvm_app_my_open_opportunities.js"></script>';
			$out .= '<script>jQ("#open_opportunities-table").stacktable({myClass:"your-class-name"});</script>';

			return $out;
		}


		//////////////////////////////////////////////
		// COMMITMENT SHORTCODES
		//////////////////////////////////////////////

		public static function wpvm_my_upcoming_commitments()
		{
			wp_enqueue_script( 'WPVM_APP_MY_UPCOM_COMMIT_JS', WPVM_URL . '/js/wpvm_app_my_upcoming_commitments.js', array('WPVM_APP_JS') );

			$out = '';
			$out .= '<h3>My Upcoming Commitments</h3>';
			$out .= '<p>Below you will find a list of volunteering opportunities for which you are currently oommitted.  To find out more about one of the opportunities below, simply click the opportunity name.  If you are no longer able to volunteer for whatever reason, click the corresponding red "X" to uncommmit.  On the day of your event, click the corresponding clock icon for one-touch sign in and sign out.</p>';
			$out .= '<p>&nbsp;</p>';
			$out .= '<div id="wpvm_my_upcoming_commitments" style="text-align:center;">';
			$out .= '<img src="'. WPVM_URL .'/images/wpspin_light.gif" style="border:0">';
			$out .= '</div>';

			$out .= '<script type="text/javascript" src="'. WPVM_URL. '/js/wpvm_app_my_upcoming_commitments.js"></script>';
	
			return $out;
		}


		public static function wpvm_my_prior_commitments()
		{
			wp_enqueue_script( 'WPVM_APP_MY_PRIOR_COMMIT_JS', WPVM_URL . '/js/wpvm_app_my_prior_commitments.js', array('WPVM_APP_JS') );

			$out = '';
			$out .= '<h3>My Prior Commitments</h3>';
			$out .= '<p>Below you will find a list of volunteering opportunities for which you have already completed.</p>';
			$out .= '<p>&nbsp;</p>';
			$out .= '<div id="wpvm_my_prior_commitments" style="text-align:center;">';
			$out .= '<img src="'. WPVM_URL .'/images/wpspin_light.gif" style="border:0">';
			$out .= '</div>';

			$out .= '<script type="text/javascript" src="'. WPVM_URL. '/js/wpvm_app_my_prior_commitments.js"></script>';

			return $out;
		}



		//////////////////////////////////////////////
		// REPORT SHORTCODES
		//////////////////////////////////////////////

		public static function wpvm_report_volunteer_hours_total()
		{
			$out = '';
			$out .= '<h3>Total Volunteer Hours</h3>';
			$out .= '<div id="wpvm_volunteer_hours_total" style="text-align:center;">';
			$out .= '<img src="'. WPVM_URL .'/images/wpspin_light.gif" style="border:0">';
			$out .= '</div>';

			$out .= '<script type="text/javascript" src="'. WPVM_URL. '/js/wpvm_app_volunteer_hours_total.js"></script>';

			return $out;
		}


		public static function wpvm_report_volunteer_hours_ytd()
		{
			$out = '';
			$out .= '<h3>YTD Volunteer Hours</h3>';
			$out .= '<div id="wpvm_volunteer_hours_ytd" style="text-align:center;">';
			$out .= '<img src="'. WPVM_URL .'/images/wpspin_light.gif" style="border:0">';
			$out .= '</div>';

			$out .= '<script type="text/javascript" src="'. WPVM_URL. '/js/wpvm_app_volunteer_hours_ytd.js"></script>';

			return $out;
		}

		public static function wpvm_report_volunteer_hours_qtd()
		{
			$out = '';
			$out .= '<h3>QTD Volunteer Hours</h3>';
			$out .= '<div id="wpvm_volunteer_hours_qtd" style="text-align:center;">';
			$out .= '<img src="'. WPVM_URL .'/images/wpspin_light.gif" style="border:0">';
			$out .= '</div>';

			$out .= '<script type="text/javascript" src="'. WPVM_URL. '/js/wpvm_app_volunteer_hours_qtd.js"></script>';

			return $out;
		}

		public static function wpvm_report_volunteer_hours_mtd()
		{
			$out = '';
			$out .= '<h3>MTD Volunteer Hours</h3>';
			$out .= '<div id="wpvm_volunteer_hours_mtd" style="text-align:center;">';
			$out .= '<img src="'. WPVM_URL .'/images/wpspin_light.gif" style="border:0">';
			$out .= '</div>';

			$out .= '<script type="text/javascript" src="'. WPVM_URL. '/js/wpvm_app_volunteer_hours_mtd.js"></script>';

			return $out;
		}


	}
}

?>