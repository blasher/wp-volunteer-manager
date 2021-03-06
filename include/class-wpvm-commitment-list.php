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



if (!class_exists("WPVM_CommitmentList"))
{	class WPVM_CommitmentList
	{
		public $commitments   = array();
		public $filters       = array();
		public $messages      = array();
		public $errors        = array();

		// PHP 4 Compatible Constructor
		function WPVM_CommitmentList()
		{	$this->__construct();
		}
		
		// PHP 5 Constructor
		function __construct()
		{
		}

		function db_table()
		{	global $wpdb;

			$db_table = $wpdb->prefix . 'wpvm_commitments';

			return $db_table;
		}

		function permission()
		{	
			$permission = false;

			if( $this->user1_id == get_current_user_id() )
			{	$permission = true;
			}
			elseif( $this->user2_id == get_current_user_id() )
			{	$permission = true;
			}
			elseif( current_user_can( 'manage_options' ) )
			{	$permission = true;
			}

			$errors[] = 'Permissions Error';

			return $permission;
		}

		function add_filter($string)
		{  $this->filters[] = $string;
		}


		function read()
		{	
			global $wpdb;

			$query = 'select * from '. $this->db_table();

			$filters = implode(' AND ', $this->filters);

			if($filters)
			{
			  $query .= ' WHERE '. $filters;
			}

			$query .= ' ORDER BY signin_time; ';

			$this->query = $query;

			if( $results = $wpdb->get_results($query) )
			{			
				$fields = $wpdb->get_col_info();
				foreach($results as $result)
				{
					$commitment = new WPVM_Commitment($result->id);

					foreach($fields as $field)
					{
						$commitment->$field = $result->$field;
					}

					$this->commitments[] = $commitment;
				}
				$this->messages[] = WPVMUtils::pre_dump($this->commitments);
			}
			else
			{  $out = 'AN ERROR OCCURRED WHILE WRITING TO DATABASE ' . $result;

				$out .= $result;
				$out .= $wpdb->last_error;
				$out .= WPVMUtils::pre_dump($data);
				$this->errors[] = $out;
			}

			return $result;
		}

	}

}

?>