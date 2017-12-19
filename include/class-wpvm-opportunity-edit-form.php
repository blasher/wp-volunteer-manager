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

require_once(  dirname(__FILE__) . '/class-wpvm-edit-form.php');

if (!class_exists("WPVM_Opportunity_Edit_Form"))
{	class WPVM_Opportunity_Edit_Form extends WPVM_Edit_Form
	{
		public $data = array();

		// PHP 4 Compatible Constructor
		function WPVM_Opportunity_Edit_Form()
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
			$out .= '<form name="edit_opportunity_form" method="POST">';
			$out .= wp_nonce_field( 'wpvm', 'wpvm_nonce', true, false );

			$out .= '' .                                          self::hidden_field('id');
			$out .= '<p>Title</p>' .                              self::text_field('title'            , 100);
			$out .= '<p>Type</p>' .                               self::opportunity_type_field();
			$out .= '<p>Location</p>' .                           self::text_field('location'         , 100);
			$out .= '<p>Start Time</p>' .                         self::datetime('start_time'         , 20);
			$out .= '<p>Duration (Hrs)</p>' .                     self::text_field('duration'         , 4);
			$out .= '<p>Minimum Participants Required</p>' .      self::text_field('min_participants' , 4);
			$out .= '<p>Maximum Participants Required</p>' .      self::text_field('max_participants' , 4);
			$out .= '<p>Notes</p>' .                              self::textarea('notes'              , 4, 100);
			$out .= '' .                                          self::hidden_field('action');
			$out .= '<p><input type="submit" value="'. $submit .'"></input></p>';

			$out .= '</form>';

			$this->output = $out;

			return $out;
		}

		function opportunity_type_field()
		{
			$options = array('Ongoing', 'Date Specific');
			$out = '<select name="type">';

			foreach($options as $option)
			{
				$out .= '<option value="'. $option .'">'. $option .'</option>';
			}
			$out .= '</select>';

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