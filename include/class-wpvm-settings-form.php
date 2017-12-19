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

if (!class_exists("WPVM_Settings_Form"))
{	class WPVM_Settings_Form extends WPVM_Edit_Form
	{
		public $data = array();

		// PHP 4 Compatible Constructor
		function WPVM_Settings_Form()
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

			$out .= '<p>Notes</p>' .               self::textarea('notes', 4, 100);
			$out .= '' .                           self::hidden_field('action');

			$out .= '<p><input type="submit" value="'. $submit .'"></input></p>';

			$out .= '</form>';

			$this->output = $out;

			return $out;
		}


	}
}

?>