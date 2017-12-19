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


if (!class_exists('WPVM_ObjectInDB'))
{	class WPVM_ObjectInDB
	{
		public $id = '';
		public $fields    = array();
		public $data      = array();
		public $messages  = array();
		public $errors    = array();

		// PHP 4 Compatible Constructor
		function WPVM_ObjectInDB( $id )
		{	$this->__construct( $id );
		}
		
		// PHP 5 Constructor
		function __construct( $id )
		{
			$this->id = $id;
		}

		//////////////////////////////////////////////
		// MISC FUNCTIONS
		//////////////////////////////////////////////

		function data()
		{
			$data = array();
			foreach($this->fields as $field)
			{
				if($this->$field)
				{	$data[$field] = $this->$field;
				}
			}

			return $data;
		}

		function get_data_from_request()
		{
			$data = array();
			foreach($this->fields as $field)
			{
				if($_REQUEST[$field])
				{	$data[$field] = $_REQUEST[$field];
				}
			}

			return $data;
		}

		function current_time_stamp()
		{
			return( date("Y-m-d H:i:s" ) );
		}


		//////////////////////////////////////////////
		// CRUD OPERATIONS
		//////////////////////////////////////////////

		function create( $data )
		{	
			global $wpdb;

//			if( !$this->permission() )
//			{	$this->errors[] = 'PERMISSION FAIL';
//				return false;
//			}

			$out = '';

			$out .= '<h4>Adding record to DB.</h4>';

			$time = $this->current_time_stamp();

			$data['creator_id']    = get_current_user_id();
			$data['date_created']  = $time;
			$data['last_modified'] = $time;

			if($result = $wpdb->insert($this->db_table(), $data) )
			{  $out .= 'New record created.';
				$this->id = $wpdb->insert_id;
				$this->messages[] = $out;
			}
			else
			{  $out .= '<p>AN ERROR OCCURRED WHILE WRITING TO DATABASE ' . $result .'</p>';
				$out .= '<p>'.$wpdb->last_error.'</p>';
				$out .= WPVMUtils::pre_dump($data);
				$this->errors[] = $out;
			}

			return $out;
		}

		function read()
		{	
			global $wpdb;

//			if( !$this->permission() )
//			{	$this->errors[] = 'PERMISSION FAIL';
//				return false;
//			}

			$query = 'select * from '. $this->db_table() .' where id='. $this->id;

			if( $result = $wpdb->get_row($query) )
			{			
				foreach($this->fields as $field)
				{
					$this->$field = $result->$field;
				}
			}
			else
			{  $out .= '<p>AN ERROR OCCURRED WHILE READING FROM DATABASE ' . $result .'</p>';
				$out .= '<p>'.$wpdb->last_error.'</p>';
				$this->errors[] = $out;
			}

			return $result;
		}

		function update( $data )
		{	
			global $wpdb;

			$out = '';

			if( !$this->permission() )
			{	$this->errors[] = 'PERMISSION FAIL';
				return false;
			} 

			$where = array( 'id' => $this->id );

			$data['last_modified'] = $this->current_time_stamp();
			$out .= WPVMUtils::pre_dump($data);

			if($result = $wpdb->update($this->db_table(), $data, $where) )
			{  $out .= 'Record '.$id.' Updated<br />';
				$out .= 'REQUEST ' . print_r($_REQUEST, true);
				$this->messages[] = $out;
			}
			else
			{  $out .= '<p>AN ERROR OCCURRED WHILE WRITING TO DATABASE ' . $result .'</p>';
				$out .= '<p>'.$wpdb->last_error.'</p>';
				$out .= WPVMUtils::pre_dump($data);
				$this->errors[] = $out;
			}

			return $out;
		}


		function delete()
		{	
			global $wpdb;

			$out = '';

			$id = $this->id();

			if(!$id)
			{	return false;
			}

			if( !$this->permission() )
			{	$this->errors[] = 'PERMISSION FAIL';
				return false;
			}

			$where = array('id' => $id);

			if($result = $wpdb->delete($this->db_table(), $where) )
			{  $out .= 'Record '. $id .' deleted.';
				$this->messages[] = $out;
			}
			else
			{  $out .= '<p>AN ERROR OCCURRED WHILE DELETING RECORD FORM DATABASE ' . $result .'</p>';
				$out .= '<p>'.$wpdb->last_error.'</p>';

				$out .= 'DB_TABLE';
				$out .= WPVMUtils::pre_dump($wpvm_db_table);
				$out .= 'WHERE';
				$out .= WPVMUtils::pre_dump($where);
				$out .= '$_REQUEST';
				$out .= WPVMUtils::pre_dump($_GET);

				$this->errors[] = $out;
			}

			return $out;
		}
	}
}

?>