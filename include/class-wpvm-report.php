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


if (!class_exists('WPVM_Report'))
{	class WPVM_Report
	{
	  /*
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
		*/

		// PHP 4 Compatible Constructor
		function WPVM_Report( $id )
		{	$this->__construct( $id );
		}
		
		// PHP 5 Constructor
		function __construct( $id )
		{
			$this->id = $id;
		}



	}
}

?>