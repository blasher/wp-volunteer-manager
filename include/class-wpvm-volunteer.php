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



if (!class_exists("WPVM_Volunteer"))
//{	class WPVM_Volunteer extends WPVM_ObjectinDB
{	class WPVM_Volunteer extends WP_User
	{
		public $id        = '';
		public $messages  = array();
		public $errors    = array();


		// PHP 4 Compatible Constructor
		function WPVM_Volunteer( $id )
		{	$this->__construct( $id );
			parent::__construct( $id );
		}
		
		// PHP 5 Constructor
		function __construct( $id )
		{
			$this->id = $id;
			parent::__construct( $id );
		}

		function db_table()
		{	global $wpdb;

			$db_table = $wpdb->prefix . 'wpvm_volunteers';

			return $db_table;
		}

		function permission()
		{	
			$permission = false;

			if( $this->id() == get_current_user_id() )
			{	$permission = true;
			}
			elseif( $this->creator_id() == get_current_user_id() )
			{	$permission = true;
			}
			elseif( $this->volunteer_id() == get_current_user_id() )
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


		//////////////////////////////////////////////
		// OTHER FUNCTIONS
		//////////////////////////////////////////////

		function open_opportunities()
		{	
			$commitments = new WPVM_CommitmentList();
			$commitments->add_filter('volunteer_id=' . $this->id );
			$commitments->add_filter('(signin_time = "0000-00-00 00:00:00" or signin_time > now())' );
			$commitments->read();

			$already_committed = array();

			foreach($commitments->commitments as $commitment)
			{  $already_committed[] = $commitment->opportunity_id();
			}

			$already = implode(',', $already_committed);

			$opportunities = new WPVM_OpportunityList();
			$opportunities->add_filter('(start_time = "0000-00-00 00:00:00" or start_time > now() or type="Ongoing" )' );

			if($already)
			{	$opportunities->add_filter('id not in ('. $already .')' );
			}

			$opportunities->read();

			$data = [];

			foreach($opportunities->opportunities as $opportunity)
			{
				$start_time = $opportunity->start_time();
				$data[]     = array(
										  'id'               => $opportunity->id(),
										  'title'            => $opportunity->title(),
										  'type'             => $opportunity->type(),
										  'location'         => $opportunity->location(),
										  'start_time'       => $opportunity->start_time(),
										  'duration'         => $opportunity->duration(),
										  'notes'            => $opportunity->notes(),
										  'already'          => $already
										  );
			}

			return $data;
		}


		function upcoming_commitments()
		{	
			if($this->upcoming_commitments)
			{
			}
			else
			{
				$upcoming_commitments = new WPVM_CommitmentList();
				$upcoming_commitments->add_filter('volunteer_id=' . $this->id() );
				$upcoming_commitments->add_filter('(status<>"Signed-Out" AND status<>"Approved")');
				$upcoming_commitments->read();

				$data = array();

				foreach($upcoming_commitments->commitments as $commitment)
				{
					$data[]     = array(
											  'id'               => $commitment->id(),
											  'opportunity_id'   => $commitment->opportunity_id(),
											  'opportunity_name' => $commitment->opportunity_name(),
											  'start_time'       => $commitment->start_time(),
											  'signin_time'      => $commitment->signin_time(),
											  'signout_time'     => $commitment->signout_time(),
											  'status'           => $commitment->status()
											  );


					$this->upcoming_commitments = $data;
				}
			}

			return $this->upcoming_commitments;
		}


		function prior_commitments()
		{	
			$data = array();

			if($this->prior_commitments)
			{
			}
			else
			{
				$prior_commitments = new WPVM_CommitmentList();
				$prior_commitments->add_filter('volunteer_id=' . $this->id() );
				$prior_commitments->add_filter('(status="Signed-Out" OR status="Approved")');
				$prior_commitments->read();

				foreach($prior_commitments->commitments as $commitment)
				{
					$data[]     = array(
											  'id'               => $commitment->id(),
											  'opportunity_id'   => $commitment->opportunity_id(),
											  'opportunity_name' => $commitment->opportunity_name(),
											  'start_time'       => $commitment->start_time(),
											  'signin_time'      => $commitment->signin_time(),
											  'signout_time'     => $commitment->signout_time(),
											  'total_time'       => $commitment->total_time(),
											  'status'           => $commitment->status()
											  );
				}

				$this->prior_commitments = $data;
			}

			return $this->prior_commitments;
		}



		function prior_commitments_ytd()
		{	
			$data = array();

			if($this->prior_commitments_ytd)
			{
			}
			else
			{
				$prior_commitments = $this->prior_commitments();

				foreach($prior_commitments->commitments as $commitment)
				{
					$data[]     = array(
											  'id'               => $commitment->id(),
											  'opportunity_id'   => $commitment->opportunity_id(),
											  'opportunity_name' => $commitment->opportunity_name(),
											  'start_time'       => $commitment->start_time(),
											  'signin_time'      => $commitment->signin_time(),
											  'signout_time'     => $commitment->signout_time(),
											  'total_time'       => $commitment->total_time(),
											  'status'           => $commitment->status()
											  );
				}

				$this->prior_commitments = $data;
			}

			return $this->prior_commitments;
		}


		function hours_volunteered_total()
		{	
			if($this->hours_volunteered_total)
			{
			}

			else
			{
				$total_time  = 0;
				$commitments = $this->prior_commitments();

				foreach($commitments as $commitment)
				{
					$total_time += $commitment->total_time();
				}

			}

			return $total_time;
		}



	}
}

?>