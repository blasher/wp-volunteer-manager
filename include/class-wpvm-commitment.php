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


if (!class_exists('WPVM_Commitment'))
{	class WPVM_Commitment extends WPVM_ObjectInDB
	{
		public $id = '';
		public $fields = array(	'volunteer_id',
										'opportunity_id',
										'signin_time',
										'signout_time',
										'status',
										'notes',
										'creator_id',
										'date_created',
										'last_modified');
		public $data      = array();
		public $messages  = array();
		public $errors    = array();

		// PHP 4 Compatible Constructor
		function WPVM_Commitment( $id )
		{	$this->__construct( $id );
		}
		
		// PHP 5 Constructor
		function __construct( $id )
		{
			$this->id = $id;
		}

		function db_table()
		{	global $wpdb;

			$db_table = $wpdb->prefix . 'wpvm_commitments';

			return $db_table;
		}

		function permission()
		{	
			$permission = false;

			if( $this->creator_id() == get_current_user_id() )
			{	$permission = true;
			}
			if( $this->volunteer_id() == get_current_user_id() )
			{	$permission = true;
			}
			elseif( current_user_can( 'manage_options' ) )
			{	$permission = true;
			}

			$this->permission = $permission;

			return $permission;
		}

		//////////////////////////////////////////////
		// PROPERTY FUNCTIONS
		//////////////////////////////////////////////

		function id()
		{	return $this->id;
		}

		function volunteer_id()
		{	return $this->volunteer_id;
		}

		function opportunity_id()
		{	return $this->opportunity_id;
		}

		function signin_time()
		{	return $this->signin_time;
		}

		function signout_time()
		{	return $this->signout_time;
		}

		function status()
		{	return $this->status;
		}

		function notes()
		{	return $this->notes;
		}

		function creator_id()
		{	return $this->creator_id;
		}

		function date_created()
		{	return $this->date_created;
		}

		function last_modified()
		{	return $this->date_created;
		}


		//////////////////////////////////////////////
		// MISC COMMITMENT SPECIFIC FUNCTIONS
		//////////////////////////////////////////////

		function opportunity()
		{	
			if($this->opportunity)
			{
			}
			else
			{
				$opportunity = new WPVM_Opportunity( $this->opportunity_id() );
				$opportunity->read();
				$this->opportunity = $opportunity;
			}

			return $this->opportunity;
		}

		function opportunity_name()
		{	
			if($this->opportunity_name)
			{
			}
			else
			{
				$opportunity = $this->opportunity();
				$this->opportunity_name = $opportunity->title();
			}

			return $this->opportunity_name;
		}

		function volunteer()
		{	
			if($this->volunteer)
			{
			}
			else
			{
				$volunteer = new WPVM_Volunteer( $this->volunteer_id );
				$this->volunteer = $volunteer;
			}

			return $this->volunteer;
		}

		function volunteer_name()
		{	
			if($this->volunteer_name)
			{
			}
			else
			{
				$volunteer = $this->volunteer();
				$this->volunteer_name = $volunteer->display_name();
			}

			return $this->volunteer_name;
		}

		function start_time()
		{	
			$start_time = '';
			if($this->start_time)
			{
			}
			else
			{	$opportunity = $this->opportunity();
				$start_time = $opportunity->start_time;
				$this->start_time = $start_time;
			}

			return $this->start_time;
		}

		function total_time()
		{	
			$total_time = '';
			if($this->total_time)
			{
			}
			else
			{
				$signin_time  = date_create($this->signin_time);
				$signout_time = date_create($this->signout_time);
				$diff         = date_diff($signout_time,$signin_time);
				$total_time   = $diff->format("%h");
				$total_time   += ( $diff->format("%i") / 60 );
				$total_time   = sprintf("%.2f", $total_time );

				$this->total_time = $total_time;
			}

			return $this->total_time;
		}


		//////////////////////////////////////////////
		// OTHER CRUD-LIKE OPERATIONS
		//////////////////////////////////////////////

		function sign_in()
		{
			$time = $this->current_time_stamp();

			$this->signin_time  = $time;
			$this->signout_time = $time;
			$this->status       = 'Signed-In';

			$this->update( $this->data() );
		}

		function sign_out()
		{
			$this->signout_time = $this->current_time_stamp();
			$this->status       = 'Signed-Out';
			$this->update( $this->data() );
		}

		function approve()
		{
			$this->status       = 'Approved';
			$this->update( $this->data() );
		}

		function inout()
		{	
			$this->read();

			if( $this->status === 'Signed-In')
			{  $this->sign_out();
			}
			elseif(  $this->status === 'Committed')
			{  $this->sign_in();
			}
		}

	}
}

?>