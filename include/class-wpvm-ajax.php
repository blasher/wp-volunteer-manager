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


if (!class_exists('WPVM_Ajax'))
{	abstract class WPVM_Ajax
	{

		public function init()
		{
			add_action( 'wp_ajax_wpvm_opportunity_commit',       __CLASS__ . '::wpvm_opportunity_commit' );

			add_action( 'wp_ajax_wpvm_commitment_delete',        __CLASS__ . '::wpvm_commitment_delete' );
			add_action( 'wp_ajax_wpvm_commitment_inout',         __CLASS__ . '::wpvm_commitment_inout' );

			add_action( 'wp_ajax_wpvm_my_open_opportunities',    __CLASS__ . '::wpvm_my_open_opportunities' );
			add_action( 'wp_ajax_wpvm_my_upcoming_commitments',  __CLASS__ . '::wpvm_my_upcoming_commitments' );
			add_action( 'wp_ajax_wpvm_my_prior_commitments',     __CLASS__ . '::wpvm_my_prior_commitments' );
		}


		public function wpvm_opportunity_commit()
		{
			$data = array();
			$data['opportunity_id'] = $_REQUEST['opportunity_id'];
			$data['signin_time']    = '0000-00-00 00:00:00';
			$data['signout_time']   = '0000-00-00 00:00:00';
			$data['volunteer_id']   = get_current_user_id();
			$data['status']         = 'Committed';

			$commitment = new WPVM_Commitment('');
			$commitment->create( $data );

			$json = json_encode( array('result' => true) );
			echo $json;

			wp_die(); // this is required to terminate immediately and return a proper response
		}


		public function wpvm_commitment_delete()
		{
			$commitment = new WPVM_Commitment( $_REQUEST['id'] );
			$commitment->read();
			$commitment->delete();

			$json = json_encode( array('result' => true, 'id' => $_REQUEST['id'], 'commitment' => $commitment, 'errors' => $commitment->errors ) );
			echo $json;

			wp_die(); // this is required to terminate immediately and return a proper response
		}

		public function wpvm_commitment_inout()
		{
			$commitment = new WPVM_Commitment( $_REQUEST['id'] );
			$commitment->read();
			$commitment->inout();

			$json = json_encode( array('result' => true, 'id' => $_REQUEST['id'], 'commitment' => $commitment, 'errors' => $commitment->errors ) );

			echo $json;

			wp_die(); // this is required to terminate immediately and return a proper response
		}

		public function wpvm_my_open_opportunities()
		{
			$volunteer_id = get_current_user_id();
			$volunteer = new WPVM_Volunteer( $volunteer_id );

			$data = $volunteer->open_opportunities();

			$json = json_encode($data);
			echo $json;

			wp_die(); // this is required to terminate immediately and return a proper response
		}

		public function wpvm_my_upcoming_commitments()
		{
			$volunteer_id = get_current_user_id();
			$volunteer = new WPVM_Volunteer( $volunteer_id );

			$data = $volunteer->upcoming_commitments();

			$json = json_encode($data);
			echo $json;

			wp_die(); // this is required to terminate immediately and return a proper response
		}

		public function wpvm_my_prior_commitments()
		{
			$volunteer_id = get_current_user_id();
			$volunteer = new WPVM_Volunteer( $volunteer_id );

			$data = $volunteer->prior_commitments();

			$json = json_encode($data);
			echo $json;

			wp_die(); // this is required to terminate immediately and return a proper response
		}


	}
}

WPVM_Ajax::init();


?>