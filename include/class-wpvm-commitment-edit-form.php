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

require_once( WPVM_PLUGIN_PATH . '/include/class-wpvm-edit-form.php');
require_once( WPVM_PLUGIN_PATH . '/include/class-wpvm-opportunity-list.php');
require_once( WPVM_PLUGIN_PATH . '/include/class-wpvm-volunteer-list.php');

if (!class_exists("WPVM_Commitment_Edit_Form"))
{	class WPVM_Commitment_Edit_Form extends WPVM_Edit_Form
	{
		public $data = array();

		// PHP 4 Compatible Constructor
		function WPVM_Commitment_Edit_Form()
		{	$this->__construct();
		}
		
		// PHP 5 Constructor
		function __construct()
		{
		}

		function build()
		{	
			$out    = '';
			$submit = '';

			if($this->data['id'])    { $submit = 'Save';        }
			else                     { $submit = 'Create';      }

			// NEED TO EVENTUALLY DEBUG THIS
			//	check_admin_referer( 'bpur' );			// Check that nonce field

			$out  = '';
			$out .= '<form name="edit_commitment_form" method="POST">';
			$out .= wp_nonce_field( 'wpvm', 'wpvm_nonce', true, false );

			$out .= '' .                           self::hidden_field('id');
			$out .= '<p>Volunteer</p>' .           self::select_volunteer('volunteer'      , 100);
			$out .= '<p>Opportunity</p>' .         self::select_opportunity('opportunity'  , 100);
			$out .= '<p>Sign-In Time</p>' .        self::datetime('signin_time'            , 20);
			$out .= '<p>Sign-Out Time</p>' .       self::datetime('signout_time'           , 20);
			$out .= '<p>Notes</p>' .               self::textarea('notes'                  , 4, 100);
			$out .= '' .                           self::hidden_field('action');

			$out .= '<p><input type="submit" value="'. $submit .'"></input></p>';

			$out .= '</form>';

			$this->output = $out;

			return $out;
		}

		function select_opportunity()
		{
			$out      = '';
			$opp_list = new WPVM_OpportunityList();
			$opp_list->read();
			$options  = $opp_list->opportunities;

			$opp_id = $this->data['opportunity_id'];

//			$out .= WPVMUtils::pre_dump($options);
			$out .= '<select name="opportunity_id">';

			foreach($options as $option)
			{
				$selected = '';

				if($option->id === $opp_id)
				{	$selected = 'selected';
				}
				{	$out .= '<option value="'. $option->id .'" '.$selected.'>'. $option->title .'</option>';
				}

			}
			$out .= '</select>';

//			print_r($options);

			return $out;

		}


		function select_volunteer()
		{
			$out = '';
			$vol_list = new WPVM_VolunteerList();
			$vol_list->read();
			$options  = $vol_list->volunteers;

			$vol_id = $this->data['volunteer_id'];

			$out .= wp_dropdown_users( array('name'=>'volunteer_id', 'echo'=>false, 'selected'=> $vol_id ) );

			return $out;

		}


		function form_field_group_id()
		{
			$options = WPVMUtils::get_groups();
			$out = '<select name="group_id">';

			foreach($options as $option)
			{
				$out .= '<option value="'. $option->group_id .'">'. $option->name .'</option>';
			}
			$out .= '</select>';

//			print_r($options);

			return $out;

		}

	}
}

?>