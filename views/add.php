<div class="wrap">

	<?php screen_icon('plugins'); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<form id="adding_new_pulldownad" method="post" action="/wp-admin/admin-ajax.php" enctype="multipart/form-data">
		
	  <?php
	   
	  // Output the hidden fields, nonce, etc.
	 // settings_fields( 'pulldownad_options' );
	   
	  // Output the settings sections.
	  do_settings_sections( $this->plugin_slug_new );
	
	  // Submit button.
	  submit_button('Save Changes','primary','submit','true',array('id'=>'add_new_pulldownad'));
	   
	  ?>
	  <input type='hidden' name='action' value='new_pulldownad' />
	 </form>
	 <?php
	 	echo "<img src='" . plugins_url( 'assets/ajax-loader.gif', dirname( __FILE__ ) ) . "' id='spinner' style='display:none;'/><br />";
	 	echo "<div id='progress_verbiage'></div>";
	 ?>

</div>
