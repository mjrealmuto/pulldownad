<?php
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=pulldownAdReport_" . $id . ".csv");
header("Pragma: no-cache");
header("Expires: 0");

require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php' );

global $wpdb;

$csv_output = "";

$pda_table 		= $wpdb->prefix . "pull_down_ad";

$pda_entries 	= $wpdb->prefix . "pull_down_ad_entries";

$pda_content = $wpdb->get_var( $wpdb->prepare( "SELECT content FROM {$pda_table} WHERE id = %d", $_GET['id'] ) );

$content = unserialize( $pda_content );

$form_data = unserialize( $content['form_data'] );

foreach( $form_data as $field )
{
	if( empty( $csv_output ) )
	{
		$csv_output = $field['field'];
	}
	else
	{
		$csv_output .= "," . $field['field'];
	}
}

$csv_output .= "\r";

$pda_entries = $wpdb->get_results( $wpdb->prepare( "SELECT first_name, last_name, street_address, street_address_2, city, state, zip, phone, dob, email_address, extra_fields 
									FROM {$pda_entries} 
									WHERE pulldown_ad_id = %d", $_GET['id'] ), ARRAY_A );
									
foreach( $pda_entries as $entry )
{
	$csv_output .= $entry['first_name'] . "," . $entry['last_name'] . "," . $entry['street_address'] . " " . $entry['street_address_2'] . ",";
	$csv_output .= $entry['city'] . "," . $entry['state'] . "," . $entry['zip'] . "," . $entry['email_address'] . "," . $entry['phone'] . "," . $entry['dob'];
	
	$extra_fields = unserialize( $entry['extra_fields'] );
	
	foreach( $extra_fields as $k=>$v)
	{
		$csv_output .= "," . $v['value'];
	}
	
	$csv_output .= "\r";
}

echo $csv_output;