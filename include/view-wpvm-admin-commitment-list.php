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

require_once( WPVM_PLUGIN_PATH . '/include/class-wpvm-view.php' );

if (!class_exists("WPVM_View_Admin_Commitment_List"))
{	class WPVM_View_Admin_Commitment_List extends WPVM_View
	{
		function display()
		{
			require_once( WPVM_PLUGIN_PATH . '/include/class-wpvm-admin-commitment-table.php' );

			self::wpvm_button_add_new();
			echo '<div id="wpvm_commitment_list_table" style="margin-right:15px;">';
			self::wpvm_commitments_table();
			echo '</div>';
		}

		function wpvm_button_add_new()
		{	
			$out = '';
			$out .= '<div style="float:left; margin-right:5px;">';
			$out .= '<form method="post" id="Commitment_Action_Form" name="Commitment_Action_Form" action="" />';
			$out .= '<input type="hidden" id="wpvm_id" name="id" value="" />';
			$out .= '<input type="hidden" id="wpvm_action" name="action" value="new" />';
			$out .= '<input type="submit" value="Add New Commitment" />';
			$out .= '</div>';
			$out .= '</form>';

			echo $out;
		}


		function wpvm_commitments_table()
		{
			require_once( ABSPATH . '/wp-admin/includes/class-wp-list-table.php' );

			//Prepare Table of elements
			$wp_list_table = new WPVM_Commitment_List_Table();
			$wp_list_table->prepare_items();

			//Table of elements
			$wp_list_table->display();
		}


	}
}

?>