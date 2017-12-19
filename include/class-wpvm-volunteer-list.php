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



if (!class_exists("WPVM_VolunteerList"))
{	class WPVM_VolunteerList
	{
		public $volunteers = '';
		public $messages   = array();
		public $errors     = array();

		// PHP 4 Compatible Constructor
		function WPVM_VolunteerList()
		{	$this->__construct();
		}
		
		// PHP 5 Constructor
		function __construct()
		{
		}

		function db_table()
		{	global $wpdb;

			$db_table = $wpdb->prefix . 'wpvm_opportunities';

			return $db_table;
		}

		function permission()
		{	
			$permission = false;

			if(! ($this->user1_id) )
			{  $this->read();
			}

			if( $this->user1_id == get_current_user_id() )
			{	$permission = true;
			}
			elseif( $this->user2_id == get_current_user_id() )
			{	$permission = true;
			}
			elseif( current_user_can( 'manage_options' ) )
			{	$permission = true;
			}

			return $permission;
		}

		function read()
		{	
			global $wpdb;

			$query = 'select * from '. $this->db_table() .' where id='. $this->id;

			if( $result = $wpdb->get_row($query) )
			{			
				foreach($this->fields as $field)
				{
					$this->$field = $result->$field;
				}
			}
			else
			{  $out .= 'AN ERROR OCCURRED WHILE WRITING TO DATABASE ' . $result;

				$out .= $result;
				$out .= $wpdb->last_error;


				$out .= WPVMUtils::pre_dump($data);
			}

			return $result;
		}


		function create()
		{	
			global $wpdb;

			$out = '';

			$out .= '<h4>Adding record to DB.</h4>';

			$data = array();
			foreach($this->fields as $field)
			{
				$data[$field] = $_REQUEST[$field];
			}

			$data['creator_id'] = get_current_user_id();

			if($result = $wpdb->insert($this->db_table(), $data) )
			{  $out .= 'New Volunteer Created.';
				$this->id = $wpdb->insert_id;
			}
			else
			{  $out .= 'AN ERROR OCCURRED WHILE WRITING TO DATABASE ' . $result;

				$out .= $result;
				$out .= $wpdb->print_error();

				$out .= WPVMUtils::pre_dump($data);
			}

			return $out;
		}

		function save()
		{	
			global $wpdb;

			$out = '';

			$permission = $this->permission();

			if( $permission )
			{
				$data = array();

				if($_REQUEST['id'])
				{
					foreach($this->fields as $field)
					{
						$data[$field] = $_REQUEST[$field];
					}

					$where = array('id' => $_REQUEST['id']);

					if($result = $wpdb->update($this->db_table(), $data, $where) )
					{  $out .= 'Volunteer '.$id.' Updated';
					}
					else
					{  $out .= 'AN ERROR OCCURRED WHILE WRITING TO DATABASE ' . $result;

						$wpdb->print_error();

						$out .= WPVMUtils::pre_dump($data);
					}
				}
				else
				{  $this->create();
				}
			}

			return $out;
		}



		function delete()
		{	
			global $wpdb;

			$out = '';

			$id = $this->id;

			$permission = $this->permission();
			$out .= 'PERMISSION = '.$permission.'<br />';
			echo 'PERMISSION = '.$permission.'<br />';

			if( $permission )
			{
				$where = array('id' => $id);

				if($result = $wpdb->delete($this->db_table(), $where) )
				{  $out .= 'Volunteer '. $id .' Deleted.';
				}
				else
				{  $out .= 'AN ERROR OCCURRED WHILE DELETING RECORD FROM DATABASE ' . $result;

					$out .= 'TABLE';
					$out .= WPVMUtils::pre_dump($wpvm_db_table);
					$out .= 'WHERE';
					$out .= WPVMUtils::pre_dump($where);
					$out .= 'ITEM';
					$out .= WPVMUtils::pre_dump($wpvm_item);
					$out .= '$_GET';
					$out .= WPVMUtils::pre_dump($_GET);

					$wpdb->print_error();

				}

			}

			return $out;
		}


		function error_log($query, $error)
		{

		}



	}
}

?>