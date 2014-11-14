<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<!-- TODO: Provide markup for your options page here. -->
	
	<?php
	
	$table_name = $wpdb->prefix . "pull_down_ad_entries";
	echo "<div id='section_container'>";
	
	if( isset( $_GET['id'] ) )
	{
		$entries = $wpdb->get_results( $wpdb->prepare( "
										SELECT first_name, last_name, street_address, street_address_2, city, state, zip, phone, dob, email_address, extra_fields 
										FROM {$table_name} 
										WHERE pulldown_ad_id = %d", $_GET['id'] ), ARRAY_A );
		
		echo "<table style='width: 100%;'>";
		echo "<tr>";
		echo "<td colspan='10'><b>" . $_GET['title'] . "</b> - For CSV Report, <a href='/wp-content/plugins/pulldown-ad/assets/createReport.php?id=" . $_GET['id'] . "'>Click here</a>.</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<th>First Name</th>";
		echo "<th>Last Name</th>";
		echo "<th>E-Mail Address</th>";
		echo "<th>Street Address</th>";
		echo "<th>City</th>";
		echo "<th>State</th>";
		echo "<th>Zip</th>";
		echo "<th>Phone</th>";
		echo "<th>D.O.B</th>";
		echo "<th>Extra Fields</th>";
		echo "</tr>";
		
		$counter = 0;
		
		foreach( $entries as $entry )
		{
			echo "<tr>";
			echo "<td style='text-align: center;'>" . $entry['first_name'] . "</td>";
			echo "<td style='text-align: center;'>" . $entry['last_name'] . "</td>";
			echo "<td style='text-align: center;'>" . $entry['email_address'] . "</td>";
			echo "<td style='text-align: center;'>" . $entry['street_address'];
			
			if( ! empty( $entry['street_address_2'] ) )
			{
				echo "<br />" . $entry['street_address_2'] . "</td>";
			}
			else
			{
				echo "</td>";
			}
			
			echo "<td style='text-align: center;'>" . $entry['city'] . "</td>";
			echo "<td style='text-align: center;'>" . $entry['state'] . "</td>";
			echo "<td style='text-align: center;'>" . $entry['zip'] . "</td>";
			echo "<td style='text-align: center;'>" . $entry['phone'] . "</td>";
			echo "<td style='text-align: center;'>" . $entry['dob'] . "</td>";
			
			$extra_fields = unserialize( $entry['extra_fields'] );
			
			if( count( $extra_fields ) > 0 )
			{
				echo "<td style='text-align: center;'><a href='javascript: openRow(" . $counter . ");'>View Entry Fields</a>";
				echo "<div style='display: none;'><div id='ef_" . $counter . "' >";
				echo "<h4> Extra Field Responses </h4>";
				echo "<table>";
				foreach( $extra_fields as $key=>$value )
				{
					echo "<tr><td><b>" . $key . "</b></td><td>" . $value['value'] . "</td></tr>";
					
				}
				echo "</table>";
				echo "</div></div>";
			}
			else
			{
				echo "<td style='text-align: center;'>No Extra Fields";
			}
			
			echo "</td></tr>";
			
			$counter++;
		}
		
		echo "</table>";
		
	}
	else
	{
		$pulldown_ads = $wpdb->get_results( 
			"SELECT DISTINCT (PDA_E.pulldown_ad_id ) , PDA.title_client 
			 FROM wp_pull_down_ad_entries AS PDA_E LEFT JOIN wp_pull_down_ad AS PDA 
			 ON PDA.id = PDA_E.pulldown_ad_id", ARRAY_N
		);
	
		echo "<h2>Select a Pulldown Ad</h2>";
		
		echo "<select name='pulldown_ad_selection' id='pulldown_ad_selection'>";
		echo "<option value='0'>Select Ad...</option>";
		
		foreach( $pulldown_ads as $ad )
		{
			echo "<option value='" . $ad[0] . "'>" . $ad[1] . "</option>";	
		}
		
		echo "</select>";
		echo "<br /><br />";
		echo "<div id='pulldown_status' style='display:none;'></div>";
	}
	?>
	
	</div>
</div>

<div id='create_pulldownad_report'></div>