/**
 * Module Name:	WPMeetup JavaScript Lib
 * Description:	Currently loads the datepicker and the gallerystuff
 * Author:		Inpsyde GmbH
 * Version:		0.1
 * Author URI:	http://inpsyde.com
 *
 * Changelog
 * 
 * 0.1
 * - Initial Commit
 */

jQuery.noConflict();
( function( $ ) {
	/**
	 * Main Class for the advanced translator
	 * 
	 * @author	th
	 * @since	0.1
	 */
	wpmeetup = {
		
		/**
		 * Initialation Function
		 * 
		 * @author	th
		 * @since	0.1
		 * @return	void
		 */
		init : function() {
			inpsyde_galleries_pro.init();
			$( '#datepicker' ).datepicker();
		},
	};
	
    inpsyde_galleries_pro = {

		init : function () {
			this.pluploader();
			this.sortable();
			this.bind_delete_button();
		},
		
		sortable : function() {
		    
		    $( '#inpsyde_galleries_pro_media_items' ).sortable();
		    $( '#inpsyde_galleries_pro_media_items' ).bind( "sortupdate", function( event, ui ) {
		    	inpsyde_galleries_pro.sortable_change( $( this ).sortable( 'serialize' ) );
		    });
		    $( '#inpsyde_galleries_pro_media_items' ).disableSelection();
		    
		},
		
		sortable_change : function( order ) {
	    
		    var data = {
			order : order,
			action : 'save_items_order',
			gallery_id : inpsyde_galleries_pro_vars.multipart_params.post_id
		    };
		    
		    $.post( ajaxurl , data, function( response ) {
			//@TODO: handle response
			});
		},
	
		pluploader : function() {
		    
		    // create the uploader and pass the config from above
		    var uploader = new plupload.Uploader( inpsyde_galleries_pro_vars );
	
		    // checks if browser supports drag and drop upload, makes some css adjustments if necessary
		    uploader.bind('Init', function(up){
			var uploaddiv = $('#plupload-upload-ui');
	
			if(up.features.dragdrop){
			    uploaddiv.addClass('drag-drop');
			    $('#drag-drop-area')
			    .bind('dragover.wp-uploader', function(){
				uploaddiv.addClass('drag-over');
			    })
			    .bind('dragleave.wp-uploader, drop.wp-uploader', function(){
				uploaddiv.removeClass('drag-over');
			    });
	
			}else{
			    uploaddiv.removeClass('drag-drop');
			    $('#drag-drop-area').unbind('.wp-uploader');
			}
		    });
	
		    uploader.init();
	
		    // a file was added in the queue
		    uploader.bind('FilesAdded', function(up, files){
			var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
	
			plupload.each(files, function(file){
			    if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5'){
			    // file size error?
	
			    }else{
	
			// a file was added, you may want to update your DOM here...
	
			}
			});
	
			up.refresh();
			up.start();
		    });
	
		    // a file was uploaded 
		    uploader.bind('FileUploaded', function(up, file, response) {
			
			// Insert uploaded image into gallery
			$( 'ul#inpsyde_galleries_pro_media_items' ).prepend( response.response ).fadeIn( 'slow' ).sortable();
			
			// Save new gallery order
			inpsyde_galleries_pro.sortable_change( $( '#inpsyde_galleries_pro_media_items' ).sortable( 'serialize' ) );
	
		    }); 
			
		    uploader.bind('UploadComplete', function(up, files ) {
			
			// Refresh media gallery here?
	
			});
		},
	
		bind_delete_button : function() {
		    
		    $( '.igp_delete_image' ).live( 'click', function() {
			
			var href = $( this ).attr( 'href' );
			
			// get to the params
			// @TODO: nice solution :/
			var params = href.split( "?" );
			params = params[1].split( "&" );
	
			var attachment_id = params[1].split( "=" );
			
			// finaly :O
			// Something messed up? Then quit here.
			if ( 0 == parseInt( attachment_id[1] ) ) return false;
			
			var data = {
			    attachment_id : attachment_id[1],
			    gallery_id : inpsyde_galleries_pro_vars.multipart_params.post_id,
			    action : 'delete_gallery_item'
			};
			    
			$.post( ajaxurl , data, function( response ) {
			    //@TODO: error handling
			    $( '#inpsyde_galleries_pro_media_items' ).replaceWith( response );
			    $( '#inpsyde_galleries_pro_media_items' ).sortable();
			});
			
			return false;
			
		    });
		    
		},
    }
	// Kick-Off
	$( document ).ready( function( $ ) { wpmeetup.init(); } );
} )( jQuery );