(function ($) {
	"use strict";
	$(function () 
	{
		var drag_length = 0;
		var initial_drag_pos = -265;
		var open_ad = false;
		var dragging = false;
		var start_descent = false;
		
		var video_playing = false;
		
		console.log( pulldownad_data );
		
		//The script has returned that there is a pulldown AD
		if( pulldownad_data.pulldownad == true )
		{
			//Extract Variables from JSON obj
			var id				= pulldownad_data.id;
			var name 			= pulldownad_data.name;
			var image  			= pulldownad_data.img;
			var repeat			= pulldownad_data.img_repeat;
			var ad_height		= ( parseInt( pulldownad_data.height ) );
			var drop_height		= ( parseInt( pulldownad_data.drop_height ) );
			var link 			= pulldownad_data.link;
			var content			= pulldownad_data.content;
			var video			= pulldownad_data.has_video;
			var video_assets	= pulldownad_data.video_assets;
			var tag_image		= pulldownad_data.tag_image;
			var position		= pulldownad_data.position;
			var entry			= pulldownad_data.has_entry;
			
			//Create #richmedia element after the header element
			$("body").prepend( $('<div>', {id : "ad_container"}) );
			$("body").append( $('<div>', {id : "overlay"}) );
			$("body").append( $('<div>', {id : "overlay-back"}) );
			
			$("#overlay, #overlay-back").css("height", $("body").css("height") );
			
			//Create #richmedia element after the header element
			$("#ad_container").append( $('<div>', {id : "draggable_container"}) );
			
			$("#draggable_container").append( $('<div>', {id : "draggable_content"}) );
			
			$("#draggable_container").append( $('<div>', {id : "draggable_handle"}) );
			
			$("#draggable_container").append( $('<div>', { id : "drag_text"}) );
			
			$("#draggable_container").append( $('<div>', { id : "click_text"}) );
			
			$("#drag_text, #click_text").addClass("arrowText");
			
			if( position == "right" )
			{
				var right_dist = parseInt( $("#ad_container").css("width") ) - parseInt( $("#draggable_handle").css("width") );
			
				$("#draggable_handle").css("left", right_dist + "px");
				
				$("#drag_text").html("Drag Down to Open <img src='/wp-content/plugins/pulldown-ad/assets/rightarrow.png' align='right' />");
				
				$("#click_text").html("Click Here to Close <img src='/wp-content/plugins/pulldown-ad/assets/rightarrow.png' align='right' />");
			}
			else
			{
				$("#drag_text").html("Drag Here to Open <img src='/wp-content/plugins/pulldown-ad/assets/leftarrow.png' align='left' />");
				
				$("#click_text").html("Click Here to Close <img src='/wp-content/plugins/pulldown-ad/assets/leftarrow.png' align='left' />");
			}
			
			if( repeat == "none" )
			{
				$("#draggable_content").css({
					background :  "url(" + image + ")",
					backgroundRepeat : "none",
					backgroundPosition : "top center"					
				});
			}
			else
			{
				if( repeat == "x" )
				{
					$("#draggable_content").css({
						background :  "url(" + image + ")",
						backgroundRepeat : "repeat-x"
					});
				}
				else if( repeat == "y" )
				{
					$("#draggable_content").css({
						background :  "url(" + image + ")",
						backgroundRepeat : "repeat-y"
					});
				}
				else
				{
					 $("#draggable_content").css({
						background :  "url(" + image + ")",
						backgroundRepeat : "repeat"
					});	
				}
			}
		
			$("#ad_container").css({
				height : parseInt( ad_height ) + "px",
				top : "-" + ad_height + "px"
			});
			
			$("#draggable_content").css("height", ( parseInt( ad_height ) ) + "px");
			
			$("#draggable_handle").css("top", ad_height + "px");
			
			$("#draggable_handle").css("background", "url( " + tag_image + ") no-repeat");
			
			$("#draggable_content").append( content );
			
			$("#pulldownad_video").parent( ).on("click",function( ){ 
				video_playing = true; 
			});
			
			$("#draggable_content").on({
				
				mouseover : function( )
				{
					$(this).css("cursor","pointer");
				},
				mouseout : function( )
				{
					$(this).css("cursor","auto");	
				},
				click : function( )
				{

					if( open_ad && ! dragging && ! video_playing)
		             {
		                _gaq.push(['_trackEvent',"PulldownAd - " + name, 'Click']);
						setTimeout( function( ){ window.location.href = link;}, 400 );   
		             }	
		             
		             if( video_playing === true )
		             {
			             video_playing = false;
		             }
		             else
		             {
			             
		             }
					
				}
			});
			
			$( "#draggable_container" ).draggable({
		        axis : 'y',  
		        handle : '#draggable_handle' , 
		        containment : '#ad_container',
		        scroll : false
		    });	
		    
		    $( "#draggable_container" ).on(
			{ 
				dragstart : function( e, ui )
			    {
					initial_drag_pos = ui.position.top;
				            
		            dragging = true;
		            
		            $("#click_text, #drag_text").hide( );

				},	
				drag : function( e, ui )
				{
					if( ( ui.position.top > initial_drag_pos ) && ( ui.position.top > 100 ) )
					{
						$('#overlay, #overlay-back').fadeIn( 1000 ); 
					}
					else 
					{
						if ( ( ui.position.top < initial_drag_pos ) && ( ui.position.top < ( initial_drag_pos - 100 ) ) )
						{
							$('#overlay, #overlay-back').fadeOut( 1000 ); 
						}
					}
				},
				dragstop: function( e, ui )
		        {
		             if( ( ui.position.top > initial_drag_pos ) && ( ui.position.top > 100 ) )
		             {
		             	
		                open_ad = true;
		                 
		                $(this).stop( ).animate({
		                	top : parseInt( ad_height ) + 'px'
			            },
			            1000,
			            function( ){
			            	dragging = false;
			            	
			            	if( video == 1 && ! $("#pulldownad_video").has("video").length )
			            	{
				            	//Check if the browser supports HTML5 Video
								if( supports_video( ) )
								{
									//Setup video tag and attributes
									$("#pulldownad_video").append( $('<video>', { id : "pullDownVideo", width : "320px", height : "240px" , controls : "controls"}) );
									
									//split video assets
									var assets = video_assets.split(",");
									
									//Loop through video assets and assign them as source tags underneath the video tag
									for( var i = 0 ; i < assets.length ; i++ )
									{
										var item  = assets[i].split("|");
										
										var name 	= item[0];
										//var type 	= item[1].split("/");
										var type 	= item[1];
										
										$("#pulldownad_video > video").append( $('<source>', { src : name, type : type}) );
									}
									
									//When richmedia ad is open play video player.
									$("#pullDownVideo").get( 0 ).play( );
									

								}
								else //Browser doesn NOT support HTML5 video - use flash player
								{
									//get video_assets
									var assets = video_assets.split(",");
									
									var embed_video_name = "";
									
									//Loop through assets and look for the 'FLV' file
									for( var i = 0 ; i < assets.length ; i++ )
									{
	
										var item  = assets[i].split(":");
										
										var name 	= item[0];
										var type 	= item[1];
										
										if( type == "video/flv" || type == "video/x-flv" )
										{
											embed_video_name = name;
										}
									}
									
									//If embed name is not empty (FLV file present) then build embed object in element #richmediaVideoContainer
									if( embed_video_name != "" )
									{
										
										$("#pulldownad_video").append( $('<embed>', {
											id : "#pullDownVideo",
											src: "/wp-content/plugins/pulldown-ad/assets/mediaplayer.swf?autostart=true&file=" + embed_video_name,
											quality: "high",
											bgcolor: "#fff",
											width: "320px",
											height: "240px",
											name: "scroller",
											align: "middle",
											allowScriptAccess: "sameDomain",
											type: "application/x-shockwave-flash",
											pluginspage: "http://www.macromedia.com/go/getflashplayer",
											wmode: "opaque",
											allowfullscreen: "true"
										}) );	
									}
								}	
							}
			            });   
		             }
		             else if( ( ui.position.top > initial_drag_pos ) && ( ui.position.top < 100 ) )
		             {
		             	
		             	$(this).stop( ).animate({
		                    top : '-15px'
		                },
		                1000,
		                function( ){
		                    dragging = false;
		                }); 
		                 
		             }
		       
					 if( ( ui.position.top < initial_drag_pos ) && ( ui.position.top < ( parseInt( initial_drag_pos ) - 100 ) ) ){
					 
					 	//console.log( '3' );
					 }
		        }
		    });
		    
			$("#draggable_handle").on({
				mouseover : function( )
				{
		            if( ! open_ad && ! dragging)
		            {
		            	$(this).css("cursor","pointer");
		            
		            	//if( ( parseInt( $("#draggable_container").css("top") ) + 15 ) == 0 )
		            	//{
			            	$("#draggable_container").stop( ).animate( { top : drop_height + 'px'}, 100);
			            	
		            	//}
		            	
		            	
			            var handle_position = $("#draggable_handle").position( );
			            
			            if( position == "right" )
			            {
				        	$("#drag_text").css({
					            top : (handle_position.top + 50 ) + "px",
					            left: (handle_position.left - 450 ) + "px"
				            }).show( );    
			            }
			            else
			            {
				    		$("#drag_text").css({
					            top : (handle_position.top + 50 ) + "px",
					            left: (handle_position.left + 100 ) + "px"
				            }).show( );        
			            }
			        }
		            else if( open_ad && ! dragging )
		            {
		            
		            	var handle_position = $("#draggable_handle").position( );
		            	
		            	if( position == "right" )
		            	{
				            $("#click_text").css({
					            top : (handle_position.top + 50 ) + "px",
					            left: (handle_position.left - 450 ) + "px"
				            }).show( );
				        }
				        else
				        {
					        $("#click_text").css({
					            top : (handle_position.top + 50 ) + "px",
					            left: (handle_position.left + 100 ) + "px"
				            }).show( );
				        }
		            }
		        },
		        mouseleave: function( )
		        {
		        	 if( ! open_ad && ! dragging )
		             {
		             	$(this).css("cursor","auto");
		             
					 	var sub = parseInt( $("#draggable_container").css("top") ) - drop_height;
					 	
					 	
		             
						//if( ( parseInt( $("#draggable_container").css("top") ) - drop_height ) == -15 )
		             	//{
			            	$("#draggable_container").stop( ).animate( {top : '-15px'}, 100);  
			            	
			            	
			            	
		             	//}
		             	
		             	
		             }
		             
		             $("#drag_text, #click_text").hide( );
		        },
		        click: function( )
		        {
		        	
		        	if( open_ad && ! dragging )
			        {
				    	$("#draggable_container").stop( ).animate({
							 top : '-15px'
						 },
						 1000,
						 function( )
						 {
							 dragging = false;
							 open_ad = false;
							 
							 //If has video remove all video players
							if( video == 1 )
							{
								$("#pullDownVideo").remove( );
								$("#pulldownad_video > embed").remove( );
							}
						 });  
						 $("#click_text, #drag_test").hide( );
						 $("body").css("overflow","auto");
						 $('#overlay, #overlay-back').fadeOut( 1000 );  
				    }
		        }
			});
			
			$("#draggable_container").stop( ).animate({ top : drop_height + "px"}, 1000, function( ){
				
				$(this).stop( ).animate({ top : "-15px"}, 1000);
			});
			
			if( entry == 1 )
			{
				var options = 
				{
					beforeSubmit: function( fD, jqF, o)
					{
						$(".sub_error").hide( );
						//alert( $.param(fD) );
						
						$("#spinner").show( );
						$("#progress_verbiage").html("Validating Data...");
														
						var errors = 0;
		
						if( $("input[name=pulldownform_" + id + "_first_name]").val( ) == "" )
						{
							$("#pulldownform_first_name_error").html( "Please Enter a First Name" ).show( );
							errors = 1;
						}
						
						if( $("input[name=pulldownform_" + id + "_last_name]").val( ) == "" )
						{
							$("#pulldownform_last_name_error").html( "Please Enter a Last Name" ).show( );
							errors = 1;
						}
						
						if( $("input[name=pulldownform_" + id + "_email]").val( ) == "" )
						{
							$("#pulldownform_email_error").html( "Please Enter an E-Mail Address" ).show( );
							errors = 1;
						}
						else if( ! validateEmail( $("input[name=pulldownform_" + id + "_email]").val( ) ) ) 
						{
							$("#pulldownform_email_error").html( "Please Enter a VALID E-Mail Address").show( );
						}
						
						if( $("input[name=pulldownform_" + id + "_street_address_1]").val( ) == "" )
						{
							$("#pulldownform_street_address_1_error").html( "Please Enter a Street Address" ).show( );
							errors = 1;
						}
						
						if( $("input[name=pulldownform_" + id + "_city]").val( ) == "" )
						{
							$("#pulldownform_city_error").html( "Please Enter a City" ).show( );
							errors = 1;
						}
						
						if( $("input[name=pulldownform_" + id + "_zip]").val( ) == "" )
						{
							$("#pulldownform_zip_error").html( "Please Enter a Zip Code" ).show( );
							errors = 1;
						}
						
						$("#pulldownform_phone_error").html("");
						
						if( $("input[name=pulldownform_" + id + "_phone_ac]").val( ) == "" )
						{
							$("#pulldownform_phone_error").append( "Please Enter an Area Code.<br />" ).show( );
							errors = 1;
						}
						/*else 
						{
							var ac_regex = new RegExp("^\d{3,3}");
							
						
							
							var valid_ac = ac_regex.test(  $("input[name=pulldownform_" + id + "_phone_ac]").val( )  );
		
							
							
							console.log( valid_ac );
							
							if( ! valid_ac )
							{
								$("#pulldownform_phone_error").append("Please enter a valid Area Code.<br />");
								errors = 1;
							}
						}*/
						
						if( $("input[name=pulldownform_" + id + "_phone_f3]").val( ) == "" )
						{
							$("#pulldownform_phone_error").append( "Please Enter the First Three Number of Phone Number.<br />" ).show( );
							errors = 1;
						}
						/*else 
						{
							var f3_regex = new RegExp("/^\d{3}/");
							
							var valid_f3 = f3_regex.test( $("input[name=pulldownform_" + id + "_phone_f3]").val( ) );
							
							if( ! valid_f3 )
							{
								$("#pulldownform_phone_error").append("Please enter valid First Three Digits of Phone Number.<br />");
								errors = 1;
							}
						}*/
						
						if( $("input[name=pulldownform_" + id + "_phone_l4]").val( ) == "" )
						{
							$("#pulldownform_phone_error").append( "Please Enter the Last Four Digits of the Phone Number. <br />" ).show( );
							errors = 1;
						}
						/*else 
						{
							var l4_regex = new RegExp("/^\d{4}/");
							
							var valid_l4 = l4_regex.test( $("input[name=pulldownform_" + id + "_phone_l4]").val( ) );
							
							if( ! valid_l4 )
							{
								$("#pulldownform_phone_error").append("Please enter valid Last Four Digits of the Phone Number. <br />");
								errors = 1;
							}
						}*/
						
						var extra_field_cnt = $("input[name=ef_count]").val( );
						
						if( extra_field_cnt > 0 )
						{
							for( var i = 0 ; i < extra_field_cnt ; i++ )
							{
								var input_type = $("input[name=pulldownform_" + id + "_ef_" + i + "_type]").val( );
							
								if( input_type == "radio" || input_type == "checkbox" )
								{
									if( ! $("input[name=pulldownform_" + id + "_ef_" + i + "]:checked").val( ) && $("input[name=pulldownform_" + id + "_ef_" + i + "_required]").val( ) == 1 )
									{
										$("#pulldownform_ef_" + i + "_error").html( "This field is required." ).show( );
										errors = 1;
									}
								}
								else if( input_type == "select" )
								{
									if( $("input[name=pulldownform_" + id + "_ef_" + i + "] option:selected").val( ) == 0 && $("input[name=pulldownform_" + id + "_ef_" + i + "_required]").val( ) == 1 )
									{
										$("#pulldownform_ef_" + i + "_error").html( "This field is required." ).show( );
										errors = 1;
									}
								}
								else if( input_type == "multi_select" )
								{
									var multi_num_selected = 0;
									
									$("input[name=pulldownform_" + id + "_ef_" + i + "] option:selected").each( function( i ){
										multi_num_selected++;
									});
									
									if( multi_num_selected == 0 && $("input[name=pulldownform_" + id + "_ef_" + i + "_required").val( ) == 1 )
									{
										$("#pulldownform_ef_" + i + "_error").html( "This field is required." ).show( );
										errors = 1;
									}
								}
								else // text field
								{
									if( $("input[name=pulldownform_" + id + "_ef_" + i + "]").val( ) == "" && $("input[name=pulldownform_" + id + "_ef_" + i + "_required]").val( ) == 1 )
									{
										$("#pulldownform_ef_" + i + "_error").html( "This field is required." ).show( );
										errors = 1;	
									}
								}
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
				
				$("form[name=pulldownadForm_" + id + "]").ajaxForm( options );
			}
		}		
	});
}(jQuery));




function supports_video() 
{
  return !!document.createElement('video').canPlayType;
}

function validateEmail(email)
{
	var emailReg = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
	var valid = emailReg.test(email);

	if( ! valid) 
	{
        return false;
    } 
    else 
    {
    	return true;
    }
}