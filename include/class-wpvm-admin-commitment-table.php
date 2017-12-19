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

// Code derived from great tutorial at:
// http://www.smashingmagazine.com/2011/11/native-admin-tables-wordpress/

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );


if (!class_exists('WPVM_Commitment_List_Table'))
{	
	class WPVM_Commitment_List_Table extends WP_List_Table
	{

		// Constructor, we override the parent to pass our own arguments
		// We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	   function __construct()
		{
			parent::__construct( array(
				'singular' => 'wp_list_wpvm_commitment',  // Singular label
				'plural'   => 'wp_list_wpvm_commitments', // Plural label, also this well be one of the table css class
				'ajax'     => false                 // We won't support Ajax for this table
			) );
		}

		// Add extra markup in the toolbars before or after the list
		// @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
		function extra_tablenav( $which )
		{	if ( $which == "top" )
			{	//The code that goes before the table is here
//				echo "Hello, I'm before the table";
			}
			if ( $which == "bottom" )
			{	//The code that goes after the table is there
//				echo "Hi, I'm after the table";
			}
		}

		// @return array
		protected function get_bulk_actions()
		{
			$actions = array();
			$actions['delete'] = __( 'Delete' );

			return $actions;
		}



		protected function column_cb($item)
		{
			return sprintf( '<input type="checkbox" name="items[]" value="%s" />',
								  $id );
		}


		// Define the columns that are going to be used in the table
		// @return array $columns, the array of columns to use with the table
		function get_columns()
		{
		   return $columns= array(
				'cb'                                    =>'<input type="checkbox" />',
		      'id'                   =>__('ID'),
		      'volunteer_id'         =>__('Volunteer'),
		      'opportunity_id'       =>__('Opportunity'),
		      'status'               =>__('Status'),
		      'signin_time'          =>__('Sign-In Time'),
		      'signout_time'         =>__('Sign-Out Time'),
 		      'actions'              =>__('Actions')
		   );
		}

		// Decide which columns to activate the sorting functionality on
		// @return array $sortable, the array of columns that can be sorted by the user
		public function get_sortable_columns()
		{
		  /*
		   return $sortable = array(
		      'id'                   =>__('ID'),
		      'volunteer_id'         =>__('Volunteer'),
		      'opportunity_id'       =>__('Opportunity'),
		      'status'               =>__('Status'),
		      'signin_time'          =>__('Sign-In Time'),
		      'signout_time'         =>__('Sign-Out Time'),
		   );
			*/

		   return $sortable = array(
				'id'                   => 'id',
				'volunteer_id'         => 'volunteer_id',
				'opportunity_id'       => 'opportunity_id',
				'status'               => 'status',
				'signin_time'          => 'signin_time',
				'signout_time'         => 'signout_time',
		   );

		}


		// Prepare the table with different parameters, pagination, columns and table elements
		public function prepare_items()
		{
		   global $wpdb, $_wp_column_headers;
			$link = mysqli_connect("localhost", DB_USER, DB_PASSWORD, DB_NAME);

		   $screen = get_current_screen();

			$wpvm_table = $wpdb->prefix. 'wpvm_commitments';
		
			/* -- Preparing your query -- */
			$query = "SELECT * FROM $wpvm_table";

			/* -- Ordering parameters -- */
		 	//Parameters that are going to be used to order the result
//			$orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
//			$order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';

			$orderby = !empty($_GET["orderby"]) ? mysqli_real_escape_string($link, $_GET["orderby"]) : '';
			$order = !empty($_GET["order"]) ? mysqli_real_escape_string($link, $_GET["order"]) : 'ASC';

			if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }

			/* -- Pagination parameters -- */
			//Number of elements in your table?
			$totalitems = $wpdb->query($query); //return the total number of affected rows
			//How many to display per page?
			$perpage = 15;
			//Which page is this?
			$paged = !empty($_GET["paged"]) ? mysqli_real_escape_string($link, $_GET["paged"]) : '';
			//Page Number
			if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
			//How many pages do we have in total?
			$totalpages = ceil($totalitems/$perpage);
			//adjust the query to take pagination into account
			if(!empty($paged) && !empty($perpage))
			{
				$offset=($paged-1)*$perpage;
				$query.=' LIMIT '.(int)$offset.','.(int)$perpage;
		   }
		
			/* -- Register the pagination -- */
		   $this->set_pagination_args( array(
		      "total_items" => $totalitems,
		      "total_pages" => $totalpages,
		      "per_page" => $perpage,
		   ) );
		   //The pagination links are automatically built according to those parameters
			
			/* -- Register the Columns -- */
		   $columns = $this->get_columns();
		   $_wp_column_headers[$screen->id]=$columns;

			/* -- Rea the items -- */
		   $this->items = $wpdb->get_results($query);

			/*
			echo 'SCREEN  = ' . print_r( $screen, true ) . "<br /><br />";
			echo 'COLUMNS = ' . print_r( $columns, true ) . "<br /><br />";
			echo 'QUERY  = ' . $query . "<br /><br />";
			echo 'ITEMS  = ' . print_r( $this->items, true ) . "<br /><br />";
			*/
		}



		// Message to be displayed when there are no items
		//
		// @since 3.1.0
		// @access public
		public function no_items()
		{
			_e( 'No items found.' );
		}




		//
		// Get a list of all, hidden and sortable columns, with filter applied
		//
		// @since 3.1.0
		// @access protected
		//
		// @return array
		//
		protected function get_column_info()
		{
			// $_column_headers is already set / cached
			if ( isset( $this->_column_headers ) && is_array( $this->_column_headers ) )
			{
				// Back-compat for list tables that have been manually setting $_column_headers for horse reasons.
				// In 4.3, we added a fourth argument for primary column.
				$column_headers = array( array(), array(), array(), $this->get_primary_column_name() );
				foreach ( $this->_column_headers as $key => $value )
				{
					$column_headers[ $key ] = $value;
				}


//				echo 'HEADERS J = ' . print_r( $this->_column_headers, true ) . "<br /><br />";

				return $column_headers;
			}

//			$columns = get_column_headers( $this->screen );
			$columns = $this->get_columns();

			$hidden = get_hidden_columns( $this->screen );

			$sortable_columns = $this->get_sortable_columns();
			//
			// Filter the list table sortable columns for a specific screen.
			//
			// The dynamic portion of the hook name, `$this->screen->id`, refers
			// to the ID of the current screen, usually a string.
			//
			// @since 3.5.0
			//
			// @param array $sortable_columns An array of sortable columns.
			//
			$_sortable = apply_filters( "manage_{$this->screen->id}_sortable_columns", $sortable_columns );
	
			$sortable = array();
			foreach ( $_sortable as $id => $data ) {
				if ( empty( $data ) )
					continue;
	
				$data = (array) $data;
				if ( !isset( $data[1] ) )
					$data[1] = false;
	
				$sortable[$id] = $data;
			}
	
			$primary = $this->get_primary_column_name();
			$this->_column_headers = array( $columns, $hidden, $sortable, $primary );

//			echo 'HEADERS K = ' . print_r( $this->_column_headers, true ) . "<br /><br />";

			return $this->_column_headers;
		}

		function action_links($id, $status)
		{
			// images url
			$images_url  = plugins_url() . '/wp-volunteer-manager/images/';

			// links
			$edit_link    = "javascript: wpvm_submit_commitment_action_form('edit', " . (int)$id . ");";
			$remove_link  = "javascript: wpvm_submit_commitment_action_form('delete', " . (int)$id . ");";
			$inout_link   = "javascript: wpvm_submit_commitment_action_form('inout', " . (int)$id . ");";
			$approve_link = "javascript: wpvm_submit_commitment_action_form('approve', " . (int)$id . ");";

			$out  = '';
			$out .= '<a href="' . $edit_link .'"><img src="'. $images_url .'edit.png" alt="Edit"></a>&nbsp;&nbsp;&nbsp;';
			$out .= '<a href="' . $remove_link .'"><img src="'. $images_url .'remove.png" alt="Delete"></a>&nbsp;&nbsp;&nbsp;';

			if($status === 'Approved')
			{
			}
			elseif($status === 'Signed-Out')
			{	$out .= '<a href="' . $approve_link .'"><img src="'. $images_url .'Apply.png" alt="Process"></a>&nbsp;&nbsp;&nbsp;';
			}
			else
			{	$out .= '<a href="' . $inout_link .'"><img src="'. $images_url .'Alarm.png" alt="Sign In/Out"></a>&nbsp;&nbsp;&nbsp;';
			}

			return $out;

		}

		// Display the rows of records in the table
		// @return string, echo the markup of the rows
		function display_rows()
		{
			//Get the records registered in the prepare_items method
			$records = $this->items;
		
			//Get the columns registered in the get_columns and get_sortable_columns methods
			list( $columns, $hidden ) = $this->get_column_info();

			//Loop for each record
			if(!empty($records))
			{	foreach($records as $rec)
				{

					$volunteer        = new WPVM_Volunteer($rec->volunteer_id);

//					$volunteer->read();
//					$volunteer_name   = $volunteer->data->user_nicename;

					$volunteer_name   = $volunteer->user_nicename;

//					print_r($volunteer);
//					die();

					$opportunity      = new WPVM_Opportunity($rec->opportunity_id);
					$opportunity->read();
					$opportunity_name = $opportunity->title;

					//Open the line
					echo '<tr id="record_'.$rec->wpvm_id.'">';
					foreach ( $columns as $column_name => $column_display_name )
					{
						//Style attributes for each col
						$class = "class='$column_name column-$column_name'";
						$style = "";
						if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
						$attributes = $class . $style;

						$id     = $rec->id;
						$status = $rec->status;
						
						//Display the cell
						switch ( $column_name )
						{
							case 'cb':                  echo '<td '.$attributes.'>'.$this->column_cb( stripslashes($rec->id) ).'</td>';   break;
							case 'id':                  echo '<td '.$attributes.'>'.stripslashes($rec->id).'</td>';   break;
							case 'volunteer_id':        echo '<td '.$attributes.'>'.stripslashes($volunteer_name).'</td>';   break;
							case 'opportunity_id':      echo '<td '.$attributes.'>'.stripslashes($opportunity_name).'</td>';   break;
							case 'status':              echo '<td '.$attributes.'>'.stripslashes($rec->status).'</td>';   break;
							case 'signin_time':         echo '<td '.$attributes.'>'.stripslashes($rec->signin_time).'</td>';   break;
							case 'signout_time':        echo '<td '.$attributes.'>'.stripslashes($rec->signout_time).'</td>';   break;
							case 'actions':             echo '<td '.$attributes.'>'.$this->action_links($id, $status).'</td>';   break;
						}
				   }
				
				   //Close the line
				   echo'</tr>';
				}
			}
		}



	}
}

?>