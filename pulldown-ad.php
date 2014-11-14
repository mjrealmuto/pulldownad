<?php
/**
 *
 * @package   Pulldown Ad
 * @author    Michael Realmuto <mrealmuto@hbi.com>
 * @license   GPL-2.0+
 * @link      http://www.hubbardbroadcasting.com
 * @copyright 2013 Hubbard Broadcasting
 *
 * @wordpress-plugin
 * Plugin Name: Pulldown Ad
 * Description: Creates an ad that will have an image tag hanging off the top right, or left, that will allow the user to 
 				to 'pull down' the ad.  The ad can have either an image with a link / video or a full webpage.
 	version 1.1 -> Changed the way the Ad gets the information.  Instead of using AJAX call via jQuery, I have wet up a shortcode
 	to retrieve the information directly from the database.  The following code will need to be used in the theme/child
 	header.php file:
 	
 	wp_call_plugins( $_GET );
 	
 	This will call a function in the functions.php page ( that needs to be placed in the script ):
 	
 	function wp_call_plugins( $get )
	{
		//RichMedia 
		
		if( isset( $get['richmedia_id'] ) )
		{
			echo do_shortcode("[get_richmediaAd id=" . $get['richmedia_id'] . "]");	
		}
		else if( isset( $get['richmedia_testdate'] ) )
		{
			if( isset( $get['richmedia_testtime'] ) )
			{
				echo do_shortcode("[get_richmediaAd testdate=" . $get['richmedia_testdate'] . " testtime=" . $get['richmedia_testtime'] . "]" );
			}
			else
			{
				echo do_shortcode("[get_richmediaAd testdate=" . $get['richmedia_id'] . "]" );
			}
		}
		else if( isset( $get['richmedia_testtime'] ) )
		{
			if( isset( $get['richmedia_testdate'] ) )
			{
				echo do_shortcode("[get_richmediaAd testdate=" . $get['richmedia_id'] . " testtime=" . $get['richmedia_testtime'] . "]" );
			}
			else
			{
				echo do_shortcode("[get_richmediaAd testtime=" . $get['richmedia_id'] . "]" );
			}
		}
		else
		{
			echo do_shortcode("[get_richmediaAd]" );
		}
		
		//Takeover 
		
		if( isset( $get['takeover_id'] ) )
		{
			echo do_shortcode("[get_takeover id=" . $get['takeover_id'] . "]");
		}
		else if( isset( $get['takeover_testdate'] ) )
		{
			if( isset( $get['takeover_testtime'] ) )
			{
				echo do_shortcode("[get_takeover testdate=" . $get['takeover_testdate'] . " testtime=" . $get['takeover_testtime'] . "]" );
			}
			else
			{
				echo do_shortcode("[get_takeover testdate=" . $get['takeover_testdate'] . "]" );
			}
		}
		else if( isset( $get['takeover_testtime'] ) )
		{
			if( isset( $get['takeover_testdate'] ) )
			{
				echo do_shortcode("[get_takeover testdate=" . $get['takeover_testdate'] . " testtime=" . $get['takeover_testtime'] . "]" );
			}
			else
			{
				echo do_shortcode("[get_takeover testtime=" . $get['takeover_testtime'] . "]" );
			}
		}
		else
		{
			echo do_shortcode("[get_takeover]" );	
		}
		
		//Pulldown Ad
		
		if( isset( $get['pulldownad_id'] ) )
		{
			echo do_shortcode("[get_pulldownad id=" . $get['pulldownad_id'] . "]" );
		}
		else if( isset( $get['pulldownad_testdate'] ) )
		{
			if( isset( $get['pulldownad_testtime'] ) )
			{
				echo do_shortcode( "[get_pulldownad testdate=" . $get['pulldownad_testdate'] . " testtime=" . $get['pulldownad_testtime'] . "]" );
			}	
			else
			{
				echo do_shortcode( "[get_pulldownad testdate=" . $get['pulldownad_testdate'] . "]" );
			}
		}
		else if( isset( $get['pulldownad_testtime'] ) )
		{
			if( isset( $get['pulldownad_testdate'] ) )
			{
				echo do_shortcode( "[get_pulldownad testdate=" . $get['pulldownad_testdate'] . " testtime=" . $get['pulldownad_testtime'] . "]" );
			}
			else
			{
				echo do_shortcode( "[get_pulldownad testtime=" . $get['pulldownad_testtime'] . "]");
			}
		}
		else
		{
			echo do_shortcode("[get_pulldownad]" );
		}
	}
	
	This function will take into account test case scenarios. IN case someone wants to test the Pulldown Ad via date/time
	or ID. If this is not necessary, then just use echo do_shortcode( "[get_pulldownad] "); in header.php
 				
 * Version:     1.1
 * Author:      Michael Realmuto
 * Author URI:  TODO
 * Text Domain: plugin-name-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// TODO: replace `class-plugin-name.php` with the name of the actual plugin's class file
require_once( plugin_dir_path( __FILE__ ) . 'class-pulldown-ad.php' );

/*
require 'plugin-update-checker.php';
$MyUpdateChecker = PucFactory::buildUpdateChecker(
    'http://wpupdate.hubstage.net/?action=get_metadata&slug=pulldown-ad', //Metadata URL.
    __FILE__, //Full path to the main plugin file.
    'pulldown-ad' //Plugin slug. Usually it's the same as the name of the directory.
);

*/

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
// TODO: replace Plugin_Name with the name of the plugin defined in `class-plugin-name.php`
register_activation_hook( __FILE__, array( 'PulldownAd', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PulldownAd', 'deactivate' ) );

// TODO: replace Plugin_Name with the name of the plugin defined in `class-plugin-name.php`
PulldownAd::get_instance();