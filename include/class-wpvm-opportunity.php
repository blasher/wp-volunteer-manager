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
(at your option) any later version.(at your option) any later version.
(at your option) any later version.(at your option) any later version.


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



if (!class_exists("WPVM_Opportunity"))
{	class WPVM_Opportunity extends WPVM_ObjectInDB
	{
		public $fields    = array(	'title',
											'type',
											'location',
											'start_time',
											'end_time',
											'duration',
											'min_participants',
											'max_participants',
											'notes');

		// PHP 4 Compatible Constructor
		function WPVM_Opportunity( $id )
		{	$this->__construct( $id );
		}
		
		// PHP 5 Constructor
		function __construct( $id )
		{
			$this->id = $id;
		}

		function db_table()
		{	global $wpdb;

			$db_table = $wpdb->prefix . 'wpvm_opportunities';

			return $db_table;
		}

		function permission()
		{	
			$permission = false;

			if( $this->creator_id == get_current_user_id() )
			{	$permission = true;
			}
			elseif( current_user_can( 'manage_options' ) )
			{	$permission = true;
			}

			return $permission;
		}


		//////////////////////////////////////////////
		// PROPERTY FUNCTIONS
		//////////////////////////////////////////////

		function id()
		{	return $this->id;
		}

		function title()
		{	return $this->title;
		}

		function type()
		{	return $this->type;
		}

		function location()
		{	return $this->location;
		}

		function start_time()
		{	return $this->start_time;
		}

		function end_time()
		{	return $this->end_time;
		}

		function duration()
		{	return $this->duration;
		}

		function min_participants()
		{	return $this->min_participants;
		}

		function max_participants()
		{	return $this->max_participants;
		}

		function notes()
		{	return $this->notes;
		}


		//////////////////////////////////////////////
		// OTHER FUNCTIONS
		//////////////////////////////////////////////

		function commmitments()
		{
			if($this->commitment_list)
			{
			}
			else
			{	$commitment_list = new WPVM_CommitmentList();
				$commitment_list->add_filter('opportunit_id='.$this->id);
				$commitment_list->read();
				$this->commitment_list = $commitment_list;
			}

			return $this->commitment_list;
		}


		function participants_ids()
		{
			if($this->participants_ids)
			{
			}
			else
			{	$commitment_list = $this->commitment_list();

				foreach ($commitment_list as $commitment)
				{
					$participant_id = $commitment->volunteer_id();
					$this->participants_ids[] = $participant_id;
				}
			}

			return $this->participants_ids;
		}

		function participants()
		{
			if($this->participants)
			{
			}
			else
			{	$participants_ids = $this->participants_ids();

				foreach ($participants_ids as $participant_id)
				{
					$participant = new Volunteer($participant_id);
					$this->participants[] = $participant;
				}
			}

			return $this->participants;
		}

	}
}

?>