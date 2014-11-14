var fieldcount = 0;
var open_change_form = "";

(function ($) {
	"use strict";
	$(function () {
		
		$("#pulldownad_start_date, #pulldownad_end_date").datepicker(
		{
			dateFormat : 'yy-mm-dd',
			buttonImage : '/js/jquery-ui/themes/images/calendar.gif',
			buttonImageOnly : true
		});
		
		if( parseInt( $('input[name=extra_field_count]').val( ) ) > 0 )
		{
			fieldcount = $('input[name=extra_field_count]').val( );
		}
		
		$("input[name=textbox]").click( function( ){
		
			$("#extra_fields").append( addField('textbox') );
			
			$("input[name=extra_field_count]").val( fieldcount );
			
		});
		
		$("input[name=checkbox]").click( function( ){
			
			$("#extra_fields").append( addField('checkbox') );
			
			$("input[name=extra_field_count]").val( fieldcount );
			
		});
		
		$("input[name=radio]").click( function( ){
			
			$("#extra_fields").append( addField( 'radio' ) );
			
			$("input[name=extra_field_count]").val( fieldcount );
		});
		
		$("input[name=dropdown]").click( function( ){
			
			$("#extra_fields").append( addField( 'select' ) );
			
			$("input[name=extra_field_count]").val( fieldcount );
			
		});
		
		$("input[name=multi_select]").click( function( ){
			
	 		$("#extra_fields").append( addField( 'multi_select' ) );
	 		
	 		$("input[name=extra_field_count]").val( fieldcount );
			
		});
		
		$("input[name=textarea]").click( function( ){
			
			$("#extra_fields").append( addField( 'textarea' ) );
	
			$("input[name=extra_field_count]").val( fieldcount );		
		});
		
		$("input[name=add_checkbox]").live("click", function( ){
			
			var id = $(this).attr('id');;
			$("#checkbox_" + id).append( add_checkbox( id ) );
			
			$("input[name=extra_field_count]").val( fieldcount );
			
		});
		
		$("input[name=add_radio]").live("click", function( ){
			
			var id = $(this).attr("id");
			$("#radio_" + id).append( add_radio( id ) );
			
			$("input[name=extra_field_count]").val( fieldcount );
			
		});
		
		$("input[name=add_option]").live("click", function( ){
			
			var id = $(this).attr('id');
			$("#options_" + id).append( add_option( id ) );
			
			$("input[name=extra_field_count]").val( fieldcount );
			
		});

		//options will have the information and functionality for the AJAX Forms upload
		var options = 
		{
			beforeSubmit: function( fD, jqF, o)
			{
				$(".sub_error").hide( );
				//alert( $.param(fD) );
				
				$("#spinner").show( );
				$("#progress_verbiage").html("Validating Data...");
												
				var errors = 0;

				if( $("input[name=pulldownad_title_client]").val( ) == "" )
				{
					$("#pulldownad_title_client_error").html( "Please Enter a title/client" ).show( );
					errors = 1;
				}
				
				if( $("input[name=pulldownad_height]").val( ) == "" )
				{
					$("input[name=pulldownad_height]").val( 768 );
				}
				else
				{
					if( parseInt( $("#input[name=pulldownad_height]").val( ) ) > 768 )
					{
						$("#pulldownad_height_error").html( "The height for the pulldown ad CAN NOT exceed 768 px" ).show( );
						errors = 1;
					}
					else if( parseInt( $("#input[name=pulldownad_height]").val( ) ) <= 0 )
					{
						$("#pulldownad_height_error").html( "Please enter a height greater than Zero. ").show( );
						errors = 1;
					}
				}
				
				if( $("input[name='pulldownad_drop_height']").val( ) == "" )
				{
					$("input[name='pulldownad_drop_height']").val( 15 );
				}
				else
				{
					if( parseInt( $("#input[name='pulldownad_drop_height']").val( ) < 15 ) )
					{
						$("#pulldownad_drop_height_error").html( "The drop height can not be less than 15px" ).show( );
						errors = 1;
					}
				}
				
				if( ! $("input[name=pulldownad_contenttype]").is(':checked') )
				{
					$("#pulldownad_contenttype_error").html( "Please select a Content Type for this Ad.");
				}
				else
				{
					if( $("input[name=pulldownad_contenttype]:checked").val( ) == "image" )
					{
						if( $("input[name=pulldownad_image]").val( ) == "" )
						{
							$("#pulldownad_image_error").html("Please upload an image for this ad.");
						}
					}
					else if( $("input[name=pulldownad_contenttype]:checked").val( ) == "page" )
					{
						if( ! $.trim( $("textarea[name=pulldownad_page_content]").val( ) ) )
						{
							$("#pulldownad_page_content_error").html("Please enter content for the page.");
							
						}
					}
				}
				
				if( $("input[name=pulldownad_start_date]").val( ) == "" )
				{
					$("#start_date_error").html( "Please Enter a Start Date" ).show( );
					errors = 1;
				}
				
				if( $("input[name=pulldownad_end_date]").val( ) == "" )
				{
					$("#end_date_error").html( "Please Enter an End Date" ).show( );
					errors = 1;
				}
				
				if( $("input[name=pulldownad_tag_image]").val( ) == "" )
				{
					$("#pulldownad_tag_image_error").html( "Please Enter Tag Image.").show( );
				}
				
				
				
				
				var content_position 	= -1;
				var video_position 		= -1;
				var entry_position 		= -1;
				
				
				if( $("textarea[name=pulldownad_content]").val( ) != "" )
				{
					content_position = $("select[name=pulldownad_content_position] option:selected").val( );
					
					if( content_position == 0 )
					{
						errors = 1;
						$("#content_position_error").html("Please select a position for written content.").show( );
					}
				}
				
				if( ( $("input[name=video_mp4]").val( ) != "" || $("input[name=has_mp4_file]").val( ) == 1 )
					|| ( $("input[name=video_ogv]").val( ) != "" || $("input[name=has_ogv_file]").val( ) == 1  )
					|| ( $("input[name=video_webm]").val( ) != "" || $("input[name=has_webm_file]").val( ) == 1 )
					|| ( $("input[name=video_flv]").val( ) != "" || $("input[name=has_flv_file]").val( ) == 1 ) 
				)
				{
					video_position = $("select[name=pulldownad_video_position] option:selected").val( );
					
					if( video_position == 0 )
					{
						errors = 1;
						$("#video_position_error").html("Please select a position for video content.").show( );
					}
				}
				
				if( $("input[name=has_entry_form]").is(":checked") )
				{
					entry_position = $("select[name=pulldownad_entryform_position] option:selected").val( );
					
					if( entry_position == 0 )
					{
						errors = 1;
						$("#entryform_position_error").html("Please select a position for the entry form content.").show( );
					}
				}
					
					if( errors > 0 )
				{
					$("#spinner").hide( );
					$("#progress_verbiage").html("");
				
					return false;
				}
				
				$("#progress_verbiage").html(" Adding Pulldown Ad to Database...");
				
			},
			success: function( data )
			{
				//eval JSON object value returned from script.
				var obj = eval( data );
				
				//Set progress bar values to 100% 
								
				$("#spinner").after( 'Upload Complete' );
				
				$("#spinner").hide( );
				
				//location.href = "/wp-admin/admin.php?page=pull-down-ad";
			},
			type : 'post',
			dataType : 'json',
			clearForm : false
		};	

		$("#adding_new_pulldownad").ajaxForm( options );
		$("#edit_pulldownad").ajaxForm( options );
		
		var options_change = {
			beforeSubmit: function( fD, jqF, o)
			{
			
				if( open_change_form == "image" )
				{
					if( $("input[name=imagechange]").val( ) == "" )
					{
						alert( "Please enter an image to upload.");
						return false;
					}
					else
					{
						$("#change_image_verbiage").html( "Uploading Image Changes..." );
						$("#img_chng_spinner").show( );
					}
				}
				
				if( open_change_form == "video" )
				{
					if( $("input[name=videochange]").val( ) == "" )
					{
						alert("Please enter a video to upload.");
						return false;
					}
					else
					{
						$("#change_video_verbiage").html( "Uploading Video Changes..." );
						$("#vid_chng_spinner").show( );
					}
				}
				
			},	
			success: function( data )
			{
				if( data == 1 )
				{
					if( open_change_form == "image" )
					{
						$("#change_image_verbiage").html( "" );
						$("#img_chng_spinner").hide( );

						$("form[name=changeImgForm]").clearForm( );
					}
					else
					{
						$("#change_video_verbiage").html( "" );
						$("#vid_chng_spinner").hide( );

						$("form[name=changeVidForm]").clearForm( );
					}
					
					open_change_form = "";
					
					$.colorbox.close( );
					
					//location.href = "/wp-admin/admin.php?page=pull-down-ad";
				}
			},
			type: 'post',
			dataType: 'text',
			clearForm: false
		};
		
		$("form[name=changeImgForm]").ajaxForm( options_change );
		$("form[name=changeVidForm]").ajaxForm( options_change );
		
		$("#pulldownad_bg_image").colorbox({rel: 'Pulldown Ad Image', transitions: "none", width: "75%", height: "75%"});
		
		$("#pulldownad_tag_image").colorbox({rel: 'Pulldown Ad Image', transitions: "none", width: "75%", height: "75%"});
		
		$("select[name=pulldown_ad_selection]").on("change", function( ){
		
			var pda_id = $("select[name=pulldown_ad_selection] option:selected").val( );
			
			var pda_title = $("select[name=pulldown_ad_selection] option:selected").html( );
		
			$("#pulldown_status").html("<img src='/wp-content/plugins/pulldown-ad/assets/ajax-loader.gif' /><br />Loading Entries for " + pda_title ).show( );
			
			location.href = "/wp-admin/admin.php?page=pull-down-ad-entries&id=" + pda_id + "&title=" + encodeURI( pda_title );
			
		});	

	});
}(jQuery));

function addField( type )
{
	var output = "";
	
	switch( type )
	{
		case "textbox":
			
			output = "<label for='ef_" + fieldcount + "' style='font-weight: bold'>Question</label><br />";
			output += "<input type='hidden' name='ef_type_" + fieldcount + "' value='text' />";
			output += "<input type='text' name='ef_text_" + fieldcount + "' size='45' /><br />";
			output += "<input type='checkbox' name='ef_req_" + fieldcount + "' value='1' /> Is this Required? <br /><br />";
			
		break;
		case "checkbox":
			
			output += "<label for='ef_" + fieldcount + "' style='font-weight: bold'>Question</label><br />";
			output += "<input type='hidden' name='ef_type_" + fieldcount + "' value='checkbox' />";
			output += "<input type='text' name='ef_text_" + fieldcount + "' size='45' /><br />";
			output += "<input type='checkbox' name='ef_req_" + fieldcount + "' value='1' />  Is this Required? <br />";
			output += "<div id='checkbox_" + fieldcount + "'>";
			output += "Checkbox Value : <input type='text' name='ef_value_" + fieldcount + "[]' /><br />";
			output += "Checkbox Label : <input type='text' name='ef_label_" + fieldcount + "[]' /><br />";
			output += "</div>";
			output += "<input type='button' name='add_checkbox' id='" + fieldcount + "' value='Add Checkbox' /> <br /><br />";
			
		break;
		case "radio":
			
			output += "<label for='ef_" + fieldcount + "' style='font-weight: bold'>Question</label><br />";
			output += "<input type='hidden' name='ef_type_" + fieldcount + "' value='radio' />";
			output += "<input type='text' name='ef_text_" + fieldcount + "' size='45' /><br />";
			output += "<input type='checkbox' name='ef_req_" + fieldcount + "' value='1' />  Is this Required? <br />";
			output += "<div id='radio_" + fieldcount + "'>";
			output += "Radio Value : <input type='text' name='ef_value_" + fieldcount + "[]' /><br />";
			output += "Radio Label : <input type='text' name='ef_label_" + fieldcount + "[]' /><br />";
			output += "</div>";
			output += "<input type='button' name='add_radio' id='" + fieldcount + "' value='Add Radio Button' /><br /><br />";
		
		break;
		case "select":
		
			output += "<label for='ef_" + fieldcount + "' style='font-weight: bold'>Question</label><br />";
			output += "<input type='hidden' name='ef_type_" + fieldcount + "' value='select' />";
			output += "<input type='text' name='ef_text_" + fieldcount + "' size='45' /><br />";
			output += "<input type='checkbox' name='ef_req_" + fieldcount + "' value='1' />  Is this Required? <br />";
			output += "<div id='options_" + fieldcount + "'>";
			output += "Option Value : <input type='text' name='ef_value_" + fieldcount + "[]' /><br />";
			output += "Option Label : <input type='text' name='ef_label_" + fieldcount + "[]' /><br />";
			output += "</div>";
			output += "<input type='button' name='add_option' value='Add Option' id='" + fieldcount + "' />  <br /><br />";
		
		break;
		case "multi_select":
			
			output += "<label for='ef_" + fieldcount + "' style='font-weight: bold'>Question</label><br />";
			output += "<input type='hidden' name='ef_type_" + fieldcount + "' value='multi_select' />";
			output += "<input type='text' name='ef_text_" + fieldcount + "' size='45' /><br />";
			output += "<input type='checkbox' name='ef_req_" + fieldcount + "' value='1' />  Is this Required? <br />";
			output += "<div id='options_" + fieldcount + "'>";
			output += "Option Value : <input type='text' name='ef_value_" + fieldcount + "[]' /><br />";
			output += "Option Label : <input type='text' name='ef_label_" + fieldcount + "[]' /><br />";
			output += "</div>";
			output += "<input type='button' name='add_option' value='Add Option' id='" + fieldcount + "' /> <br /><br />";
			
		break;
		case "textarea":
			output = "<label for='ef_" + fieldcount + "' style='font-weight: bold'>Question</label><br />";
			output += "<input type='hidden' name='ef_type_" + fieldcount + "' value='textarea' />";
			output += "<input type='text' name='ef_text_" + fieldcount + "' size='45' /><br />";
			output += "<input type='checkbox' name='ef_req_" + fieldcount + "' value='1' /> Is this Required? <br /><br />";
		
		break;
		
	}
	
	fieldcount++;
	return output;
}

function add_checkbox( id ) 
{
	var new_checkbox = "";
	
	new_checkbox = "Checkbox Value : <input type='text' name='ef_value_" + id + "[]' /><br />";
	new_checkbox += "Checkbox Label : <input type='text' name='ef_label_" + id + "[]' /><br />";
	
	return new_checkbox;
}

function add_radio( id )
{
	var new_radio = "";
	
	new_radio = "Radio Value : <input type='text' name='ef_value_" + id + "[]' /><br />";
	new_radio += "Radio Label : <input type='text' name='ef_label_" + id + "[]' /><br />";
	
	return new_radio;

}

function add_option( id )
{
	var new_option = "";
	
	new_option = "Option Value : <input type='text' name='ef_value_" + id + "[]' /><br />";
	new_option += "Option Label : <input type='text' name='ef_value_" + id + "[]' /><br />";
	
	return new_option;
}

function changeAsset( index, name, section )
{
	if( section == "video" )
	{
		jQuery("#inline_content_video h3").html( "Change Video - " + name );
		
		jQuery("form[name=changeVidForm] input[name=vid_type]").val( index );
		
		jQuery("form[name=changeVidForm] input[name=vid_name]").val( name );
		
		open_change_form = "video";
		
		jQuery.colorbox({inline: true, href: "#inline_content_video"} );	
		
	}
	else 
	{
		jQuery("#inline_content_image h3").html( "Change Image - " + name);
		
		jQuery("form[name=changeImgForm] input[name=old_image_name]").val( name );
	
		jQuery("form[name=changeImgForm] input[name=old_image_section]").val( section );
		
		open_change_form = "image";

		jQuery.colorbox({inline: true, href: "#inline_content_image"} );	

	}
}

function deleteImage( i, n, s )
{
	jQuery.ajax({
		
		url : "/wp-admin/admin-ajax.php",
		type : "GET",
		data : { index : i, name : n, row_id : jQuery("input[name=id]").val( ), section : s, action : "pulldownad_delete_image" },
		dataType : "json",
		success : function( data )
		{
			location.reload( );
		}
	});
}

function deleteVideo( type )
{
	jQuery.ajax({
		url : "/wp-admin/admin-ajax.php",
		type : "GET",
		data : { vType : type, row_id : jQuery("input[name=id]").val( ), action : "pulldownad_delete_video"},
		dataType: "text",
		success: function( data )
		{
			location.reload( );
		}
	});
}

function getTemplate( file )
{
	jQuery.ajax({
		url : "/wp-admin/admin-ajax.php",
		type : "GET",
		data : { file : file, action : "pulldownad_get_template"},
		dataType : "html",
		success: function( data )
		{
			jQuery("#pulldownad_content").html( data );
		},
		failure : function( )
		{
			alert( 'no dice' );
		}
		
	});
}

function openRow( row_id )
{
	jQuery.colorbox({inline: true, href: "#ef_" + row_id } );
}

function deleteAd( id )
{
	jQuery.ajax({
		url : "/wp-admin/admin-ajax.php",
		type : "GET",
		data : { id : id, action : "pulldownad_delete_ad"},
		dataType : "text",
		success: function( data )
		{
			alert( "Pulldown Ad Deleted").
			location.reload( );
		},
		failure : function( )
		{
			alert( 'no dice' );
		}
		
	});

}

