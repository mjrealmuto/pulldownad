<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<!-- TODO: Provide markup for your options page here. -->
	
	<?php
	if( isset( $_GET['updated'] ) )
	{
		if( (bool) $_GET['updated'] )
		{
			echo "<div id='success' style='display: inline; margin-top: 5px; margin-bottom: 5px; width: 100%;'>The record has been updated.</div>";
		}
		else
		{
			echo "<div id='error' style='display: inline; margin-top: 5px; margin-bottom: 5px; width: 100%;'>The record was not updated.</div>";
		}
		
	}
	?>
	
	<div id='section_container'>
		<?php 
			echo "<form id=\"edit_pulldownad\" method=\"post\" action=\"/wp-admin/admin-ajax.php?time=" . time( ) . "\" enctype=\"multipart/form-data\">";
			echo "<input type='hidden' name='id' value='" . $_GET['id'] . "' />";
			echo "<input type='hidden' name='action' value='edit_pulldownad' />";

			// Output the hidden fields, nonce, etc.
			//settings_fields( 'pulldownad_options' );
			   
			// Output the settings sections.
			do_settings_sections( $this->plugin_slug_edit );
				
			// Submit button.
			submit_button('Save Changes','primary','submit','true',array('id'=>'edit_pulldownad'));
				   
				  
						echo "</form>";
			echo "<img src='" . plugins_url( 'assets/ajax-loader.gif', dirname( __FILE__ ) ) . "' id='spinner' style='display:none;'/>";
				  
			?>
		  	<div style='display: none;'>
				<div id='inline_content_image' style='padding:10px; background:#fff; width:500px; height: 200px;'>
					<h3></h3>
					<form name='changeImgForm' method='post' enctype='multipart/form-data' action='/wp-admin/admin-ajax.php' >
						<input type='hidden' name='id_passed' value='<?= $_GET['id'] ?>' />
						<input type='hidden' name='old_image_section' value='' />
						<label for='imagechange'>Upload Image to swap</label><br />
						<input type='file' name='imagechange' />	
						<br />
						<input type='hidden' name='action' value='change_pulldownad_asset' />
						<input type='submit' value='Upload' name='upload_image_change' />
						<div id='change_image_verbiage'></div>
						<img src='<?php echo plugins_url( 'assets/ajax-loader.gif', dirname( __FILE__ ) ); ?>' id='img_chng_spinner' style='display:none;'/>
					</form>
				</div>
			</div>
			
			<div style='display: none;'>
				<div id='inline_content_video' style='padding:10px; background:#fff; width:500px; height: 200px;'>
					<h3></h3>
					<form name='changeVidForm' method='post' enctype='multipart/form-data' action='/wp-admin/admin-ajax.php'>
						<input type='hidden' name='id_passed' value='<?= $_GET['id'] ?>' />
						<input type='hidden' name='vid_type' value='' />
						<input type='hidden' name='vid_name' value='' />
						<label for='imagechange'>Upload Image to swap</label><br />
						<input type='file' name='videochange' />	
						<br />
						<input type='hidden' name='action' value='change_pulldownad_asset' />
						<input type='submit' value='Upload' name='upload_video_change' />
						<div id='change_video_verbiage'></div>
						<img src='<?php echo plugins_url( 'assets/ajax-loader.gif', dirname( __FILE__ ) ); ?>' id='vid_chng_spinner' style='display:none;'/>
					</form>
				</div>
			</div>
	</div>
</div>