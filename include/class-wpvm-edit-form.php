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


if (!class_exists("WPVM_Edit_Form"))
{	class WPVM_Edit_Form
	{
		public $data   = array();
		public $output = '';

		// PHP 4 Compatible Constructor
		function WPVM_Opportunity_Edit_Form()
		{	$this->__construct();
		}
		
		// PHP 5 Constructor
		function __construct()
		{
		}

		function populate($data)
		{	
			$this->data = $data;
		}

		function set_action($action)
		{	
			$this->action = $action;
		}


		function build()
		{	
			$out = '';

			$out  = '';
			$out .= '<form name="edit_form" method="POST">';
			wp_nonce_field( 'wpvm' );

			$out .= '<p>Title</p>' . 										self::text_field('title'            , 100);
			$out .= '<p><input type="submit" value="Submit"></input></p>';

			$out .= '</form>';

			$this->output = $out;

			return $out;
		}

		function display()
		{	
			if(!$this->output)
			{
				$this->build();
			}

			echo $this->output;
		}

		function text_field($name, $size)
		{	
			$out = '';

			$value = $this->data[$name];
			$out .= '<input type="input" name="' . $name . '" value="' . $value .'" size="' . $size . '"></input>';
			return $out;
		}

		function hidden_field($name)
		{	
			$out = '';

			$value = $this->data[$name];
			$out .= '<input type="hidden" name="' . $name . '" value="' . $value .'"></input>';
			return $out;
		}

		function textarea($name, $rows, $cols)
		{	
			$out = '';

			$value = $this->data[$name];
			$out .= '<textarea name="' . $name . '" rows="' . $rows .'" cols="' . $cols . '">'.$value.'</textarea>';
			return $out;
		}

		function dropdown_users($name)
		{	
			$out = '';

			$selected = $this->data[$name];
			$out .= wp_dropdown_users(array('name' => $name, 'echo' => false, 'selected' => $selected)); 

			return $out;
		}

		function datetime($name)
		{
			$out   = '';
			$value = $this->data[$name];

			/*
			if(!$value || $value === '0000-00-00 00:00:00')
			{
				$raw_time = mktime();
				$value    = date("Y-m-d H:i:s", $raw_time );
			}
			*/

			$out .= '<input type="input" name="' . $name . '" value=' . $value .'"></input>'."\n";
			$out .= '<script>';
			$out .= 'jQ_'. $name .' = jQuery.noConflict();';
			$out .= '';
			$out .= 'd = new Date("'. $value .'");';
			$out .= 'jQ_'. $name .'("*[name='. $name .']").appendDtpicker({"current": d});';
			$out .= '</script>';
			return $out;
		}

	}
}

?>