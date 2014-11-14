<?php

 $rows = $wpdb->get_results( "SELECT id, title_client, content, start_date, end_date FROM {$table_name}" );
 
?>
<style>
	
	#pulldownad_list th
	{
		background-color: #b1e0fb;
		border: 2px solid #000;
	}
	
	#pulldownad_list td:odd
	{
		background-color: #fcf7de;
	}



</style>

<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	
	
	<table width='100%' id='pulldownad_list'>
		<tr>
			<th>Title/Client</th>
			<th>Start Date</th>
			<th>Start Time</th>
			<th>End Date</th>
			<th>End Time</th>
			<th>Link</th>
			<th>Components</th>
			<th>Actions</th>
		</tr>
	
		<?php
			foreach( $rows as $row )
			{
				
				echo "<tr>";
				echo "	<td align='center'>{$row->title_client}</td>";
				
				list($s_date, $s_time) = explode(" ", $row->start_date);
				
				echo "	<td align='center'>{$s_date}</td>";
				echo " 	<td align='center'>{$s_time}<//td>";
				
				list($e_date, $e_time) = explode(" ", $row->end_date);
				
				echo "	<td align='center'>{$e_date}</td>";
				echo "	<td align='center'>{$e_time}</td>";
				
				$content = unserialize( $row->content );
				
				$link = $content['link'];
				
				echo "	<td align='center'>{$link}</td>";
				
				$components = "";
				
				if( ! empty( $content['content'] ) )
				{
					if( empty( $components ) )
					{
						$components = "Written Content";
					}
					else
					{
						$components .= ", Written Content";
					}
				}
				
				$videos = unserialize( $content['videos'] );
				
				if( count( $videos ) > 0 )
				{
					if( empty( $components ) )
					{
						$components = "Video";
					}
					else
					{
						$components .= ", Video";
					}
				}
				
				if( $content['has_entry'] == 1 )
				{
					if( empty( $components ) )
					{
						$components = "Entry Form";
					}
					else
					{
						$components .= ", Entry Form";
					}
				}
				
				echo "	<td align='center'>";

				echo $components;
								
				echo "</td>";
				
				echo "<td align='center'>";
				echo "<a href='" . sprintf("?page=%s&id=%s", "pull-down-ad-edit", $row->id) . "'>Edit</a>";
				echo " &nbsp; <a href='" . sprintf("http://%s/?pulldownad_id=%d", $_SERVER['SERVER_NAME'], $row->id) . "' target='_BLANK'>Preview</a>";
				echo "&nbsp; <a href='" . sprintf("javascript: deleteAd(%d);", $row->id) . "'>Delete</a>";
				echo "</td>";
				echo "</tr>";
			}
		?>
	
	
	</table>

</div>
