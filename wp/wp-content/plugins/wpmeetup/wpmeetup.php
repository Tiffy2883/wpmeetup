<?php
/**
 * Plugin Name:	MeetPress
 * Description:	
 * Author:		Inpsyde GmbH
 * Author URI:	http://inpsyde.com
 * Version:		0.2
 * License:		CC-BY-SA
 *
 * Changelog
 * 
 * 0.2
 * - Better Metabox Structure
 * - TODO Improved Geodata-Loader
 * - TODO Gallery-Feature
 * - TODO Multiple Dates with datepicker
 * - TODO Some styling stuff
 * - Removed author
 *
 * 0.1
 * - Initial Commit
 */

if ( ! class_exists( 'Wpmeetup' ) ) {
	
	if ( ! defined( 'GOOGLE_API_KEY' ) ) {
		// get your own key
		// http://code.google.com/apis/maps/signup.html
		define( 'GOOGLE_API_KEY', 'ABQIAAAALKh_xJZ2AHg__In1-EMSdRT2yXp_ZAY8_ufC3CFXhHIE1NvwkxQbXpVNDP3gBdW5R5nGtenLOnXrKQ' );
	}

	// Kick Off
	if ( function_exists( 'add_filter' ) )
		add_filter( 'plugins_loaded', array( 'Wpmeetup', 'get_instance' ) );
	
	class Wpmeetup {
		
		/**
		 * Instance holder
		 *
		 * @static
		 * @access	private
		 * @since	0.1
		 * @var		NULL | Wpmeetup
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @static
		 * @access	public
		 * @since	0.1
		 * @return	Wpmeetup
		 */
		public static function get_instance() {
			if( ! self::$instance )
				self::$instance = new self;
			return self::$instance;
		}
		
		/**
		 * Setting up some data and start the hooks
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	add_filter, get_post_type
		 * @return	void
		 */
		public function __construct() {
			// Load Custom Post Type
			add_filter( 'init', array( $this, 'init_post_type' ) );
			
			if ( 'wpmeetups' == $_GET[ 'post_type' ] || 'wpmeetups' == get_post_type( $_GET[ 'post' ] ) ) {
				// Scripts
				add_filter( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
				// Add Metaboxes on our pages
				add_filter( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
			}
			
			// Save Post-Meta
			add_filter( 'save_post', array( $this, 'save_post' ) );
			
			// The Gallery
			add_filter( 'init', array( $this, 'theme_support' ) );
			add_filter( 'wp_ajax_photo_gallery_upload', array( $this, 'handle_file_upload' ) );
			add_filter( 'wp_ajax_save_items_order', array( $this, 'save_items_order' ) );
			add_filter( 'wp_ajax_delete_gallery_item', array( $this, 'delete_gallery_item' ) );
			add_filter( 'wp_ajax_update_attachment', array( $this, 'update_attachment' ) );
			add_filter( 'wp_ajax_refresh_gallery', array( $this, 'draw_gallery_items' ) );
		}
		
		
		/**
		 * Set theme support options
		 *
		 * @access	public
		 * @since	0.2
		 * @uses	add_theme_support
		 * @return	void
		 */
		public function theme_support() {
			add_theme_support( 'post-thumbnails' );
		}
		
		/**
		 * Load admin javascript
		 *
		 * @access  public
		 * @since	0.2
		 * @uses	wp_enqueue_script, plugins_url, wp_localize_script, wp_enqueue_style
		 * @return  void
		 */
		public function admin_scripts() {
			wp_enqueue_style( 'wpmeetup', plugins_url( 'css/style.css', __FILE__ ) );
			wp_enqueue_script( 'wpmeetup', plugins_url( 'js/', __FILE__ ) . 'wpmeetup.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'plupload-all', 'jquery-ui-sortable' ) );
			wp_localize_script( 'wpmeetup', 'inpsyde_galleries_pro_vars', $this->load_js_vars() );
		}
		
		/**
		* Provide array with vars used
		* in the javascript
		*
		* @since 0.2
		* @return $plupload_init | array containing vars
		*/
		private function load_js_vars() {
		
			$plupload_init = array(
				'runtimes' => 'html5,silverlight,flash,html4',
				'browse_button' => 'plupload-browse-button',
				'container' => 'plupload-upload-ui',
				'drop_element' => 'drag-drop-area',
				'file_data_name' => 'async-upload',
				'multiple_queues' => TRUE,
				'max_file_size' => wp_max_upload_size() . 'b',
				'url' => admin_url( 'admin-ajax.php' ),
				'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
				'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
				'filters' => array( array( 'title' => 'Allowed Files', 'extensions' => '*' ) ),
				'multipart' => TRUE,
				'urlstream_upload' => TRUE,
				'multipart_params' => array(
					'_ajax_nonce' => wp_create_nonce( 'photo-upload' ),
					'action' => 'photo_gallery_upload', // the ajax action name
					'post_id' => get_the_ID()
				),
			);
		
			return $plupload_init;
		}
		
		/**
		 * Register the Custom Post Type
		 * 
		 * @access	public
		 * @since	0.1
		 * @uses	register_post_type
		 * @return	void
		 */
		public function init_post_type() {
			
			$labels = array(
				'name'				=> 'Meetups',
				'new_item'			=> 'Neues Meetup',
				'singular_name'		=> 'Meetup',
				'view_item'			=> 'Zeige Meetups',
				'edit_item'			=> 'Editiere Meetup',
				'add_new_item'		=> 'Meetup hinzuf&uuml;gen',
				'not_found'			=> 'Kein Meetup gefunden',
				'search_items'		=> 'Durchsuche Meetups',
				'parent_item_colon' => ''
			);
			
			$supports = array(
				'title',
				'editor',
				'comments',
			);
			
			$args = array(
				'public'				=> TRUE,
				'publicly_queryable'	=> TRUE,
				'show_ui'				=> TRUE, 
				'query_var'				=> TRUE,
				'capability_type'		=> 'post',
				'hierarchical'			=> FALSE,
				'menu_position'			=> NULL,
				'supports'				=> $supports,
				'has_archive'			=> TRUE,
				'rewrite'				=> TRUE,
				'labels'				=> $labels
			);
			
			register_post_type( 'wpmeetups', $args );
		}
		
		/**
		 * Adds the meta boxes to the post edition page
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	add_meta_box
		 * @return	void
		 */
		public function add_meta_boxes() {
			// Address
			add_meta_box(
				'wpmeetup_address', 
				'Adresse',
				array( $this, 'meta_box_address' ),
				'wpmeetups', 'side', 'default'
			);
			
			// Location
			add_meta_box(
				'wpmeetup_location', 
				'Location',
				array( $this, 'meta_box_location' ),
				'wpmeetups', 'side', 'default'
			);
			
			// Geodata
			add_meta_box(
				'wpmeetup_geodata', 
				'Geodaten',
				array( $this, 'meta_box_geodata' ),
				'wpmeetups', 'side', 'default'
			);
			
			// Date
			add_meta_box(
				'wpmeetup_dates', 
				'Datum und Zeit der Meetups',
				array( $this, 'meta_box_date' ),
				'wpmeetups', 'advanced', 'default'
			);
			
			// Gallery
			add_meta_box(
				'meetup_gallery',
				'Gallerie',
				array( $this, 'meta_box_gallery' ),
				'wpmeetups', 'advanced', 'default'
			);
		}
		
		/**
		 * Displays the meta box for the address
		 *
		 * @access	public
		 * @since	0.2
		 * @uses
		 * @return	void
		 */
		public function meta_box_address( $post ) {
			?>
			<input id="meetup[town]" name="meetup[town]" type="text" value="<?php echo $meetup_meta[ 'town' ]; ?>" tabindex="1" style="float: right;" />
			<label for="meetup[town]" style="display: block; float: right; padding: 5px 7px 0;">Stadt</label>
			<br class="clear" />
			<input id="meetup[plz]" name="meetup[plz]" type="text" value="<?php echo $meetup_meta[ 'plz' ]; ?>" tabindex="1" style="float: right;" />
			<label for="meetup[plz]" style="display: block; float: right; padding: 5px 7px 0;">Postleitzahl</label>
			<br class="clear" />
			<input id="meetup[street]" name=meetup[street] type="text" value="<?php echo $meetup_meta[ 'street' ]; ?>" tabindex="1" style="float: right;" />
			<label for="meetup[street]" style="display: block; float: right; padding: 5px 7px 0;">Strasse</label>
			<br class="clear" />
			<input id="meetup[number]" name="meetup[number]" type="text" value="<?php echo $meetup_meta[ 'number' ]; ?>" tabindex="1" style="float: right;" />
			<label for="meetup[number]" style="display: block; float: right; padding: 5px 7px 0;">Nummer</label>
			<br class="clear" />
			<?php	
		}
		
		/**
		 * Displays the meta box for the location
		 *
		 * @access	public
		 * @since	0.2
		 * @uses
		 * @return	void
		 */
		public function meta_box_location( $post ) {
			?>
			<input id="meetup[location]" name="meetup[location]" type="text" value="<?php echo $meetup_meta[ 'location' ]; ?>" tabindex="1" style="float: right;" />
			<label for="meetup[location]" style="display: block; float: right; padding: 5px 7px 0;">Name</label>
			<br class="clear" />
			<input id="meetup[location_url]" name="meetup[location_url]" type="text" value="<?php echo $meetup_meta[ 'location_url' ]; ?>" tabindex="1" style="float: right;" />
			<label for="meetup[location_url]" style="display: block; float: right; padding: 5px 7px 0;">Webseite</label>
			<br class="clear" />
			<?php	
		}
		
		/**
		 * Displays the meta box for the geodata
		 *
		 * @access	public
		 * @since	0.2
		 * @uses	
		 * @return	void
		 */
		public function meta_box_geodata( $post ) {
			?>
			<input id="meetup[longitude]" name=meetup[longitude] type="text" value="<?php echo $meetup_meta[ 'longitude' ]; ?>" tabindex="1" style="float: right;" />
			<label for="meetup[longitude]" style="display: block; float: right; padding: 5px 7px 0;">Longitude</label>
			<br class="clear" />
			<input id="meetup[latitude]" name="meetup[latitude]" type="text" value="<?php echo $meetup_meta[ 'latitude' ]; ?>" tabindex="1" style="float: right;" />
			<label for="meetup[latitude]" style="display: block; float: right; padding: 5px 7px 0;">Latitude</label>
			<br class="clear" />
			<p><em>Die Geodaten sollten automatisch geladen werden, wenn du auf den Button klickst. Sollte das nicht passieren, musst du dir die Geodaten <a href="">von dieser Seite</a> ziehen. Bitte speicher dein Meetup vor dieser Aktion.</em></p>
			<p><a href="#" id="locate_geodata" class="button-primary">Geodaten ermitteln</a></p>
			<?php
		}
		
		/**
		 * Displays the meta box for the date
		 *
		 * @access	public
		 * @since	0.1
		 * @uses	wp_nonce_field, plugin_basename
		 * @return	void
		 */
		public function meta_box_date( $post ) {
			
			// Add Nonce Field for security options
			wp_nonce_field( plugin_basename( __FILE__ ), 'wpmeetup_nonce' );
			?>
			<p><em>Du kannst jetzt mehrere Termine f&uuml;r dein Meetup angeben. Gib einfach das Datum und die Zeit in dem angegeben Format an und klick auf "hinzuf&uuml;gen". Alte oder fehlerhafte Daten kannst du &uuml;ber den L&ouml;schenbutton entfernen.</em></p>
			<?php
		}
		
		/**
		 * This is a dummy-function for showing a gallery-upload-metabox
	 	 *
		 * @access	public
		 * @since	0.2
		 * @param	object $page
		 * @return	void
		 */
		public function meta_box_gallery( $page ) {
			$this->draw_galleries_uploader();
		}
		
		/**
		 * Save the postmetas
		 *
		 * @since	0.1
		 * @return	void
		 */
		public function save_post( $post_id ) {
			// Preventing Autosave, we don't want that
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;
				
			// We don't need to save because there is Post Array
			if ( 0 >= count( $_POST ) )
				return;
			
			// Check permissions
			if ( 'page' == $_POST['post_type'] ) {
				if ( ! current_user_can( 'edit_page', $_POST[ 'ID' ] ) )
					return;
			}
			else {
				if ( ! current_user_can( 'edit_post', $_POST[ 'ID' ] ) )
					return;
			}
			
			// verify this came from the our screen and with proper authorization,
			// because save_post can be triggered at other times
			if ( ! wp_verify_nonce( $_POST[ 'wpmeetup_nonce' ], plugin_basename( __FILE__ ) ) )
				return;
			
			// Dateformat
			$date = explode( '.', $_POST[ 'meetup' ][ 'date' ] );
			$date = $date[ 2 ] . '-' . $date[ 1 ] . '-' . $date[ 0 ];
			
			update_post_meta( $post_id, 'meetup_date', $date );
			update_post_meta( $post_id, 'meetup_town', $_POST[ 'meetup' ][ 'town' ] );
			update_post_meta( $post_id, 'meetup_street', $_POST[ 'meetup' ][ 'street' ] );
			update_post_meta( $post_id, 'meetup_number', $_POST[ 'meetup' ][ 'number' ] );
			update_post_meta( $post_id, 'meetup_plz', $_POST[ 'meetup' ][ 'plz' ] );
			update_post_meta( $post_id, 'meetup_long', $_POST[ 'meetup' ][ 'longitude' ] );
			update_post_meta( $post_id, 'meetup_lat', $_POST[ 'meetup' ][ 'latitude' ] );
			update_post_meta( $post_id, 'meetup', $_POST[ 'meetup' ] );
		}
		
		public function determine_geodata_settings_page() {
			global $post;
				
			$api_base_url = 'http://maps.google.com/maps/geo?output=xml&key=' . GOOGLE_API_KEY;
		
			$query = $this->get_posts_without_geodata_query();
			
			$addresses_count = 10;
			$i = 0;
			if ( $_POST[ 'addresses_count' ] && (int) $_POST[ 'addresses_count' ] > 0 ) {
				$addresses_count = (int) $_POST[ 'addresses_count' ];
		
				if ( $query->have_posts() ) {
						
					while ( $query->have_posts() && $i++ < $addresses_count ) {
						$query->the_post();
		
						$place			= get_post_meta( get_the_ID(), 'meetup_town', TRUE );
						$postcode		= get_post_meta( get_the_ID(), 'meetup_plz', TRUE );
						$street			= get_post_meta( get_the_ID(), 'meetup_street', TRUE );
						$street_number	= get_post_meta( get_the_ID(), 'meetup_number', TRUE );
		
						$location_string = $street . ' ' . $street_number . ', ' . $postcode . ' ' . $place;
						
						// see http://code.google.com/apis/maps/articles/phpsqlgeocode.html
						$success = false;
						do {
							$request_url = $api_base_url . "&q=" . urlencode( $location_string );
							$xml = simplexml_load_file( $request_url ) or die( 'google geo api url not loading' );
		
							$status = $xml->Response->Status->code;
							if ( strcmp( $status, '200') == 0 ) {
								// Successful geocode
								$success = true;
		
								$coordinatesSplit = split(",", $xml->Response->Placemark->Point->coordinates);
		
								$lat = $coordinatesSplit[1];
								$lng = $coordinatesSplit[0];
								
								$meetup_data = get_post_meta( get_the_ID(), 'meetup', TRUE );
								$meetup_data[ 'latitude' ] = $lat;
								$meetup_data[ 'longitude' ] = $lng;
								update_post_meta( get_the_ID(), 'meetup', $meetup_data );
								
								update_post_meta( get_the_ID(), 'meetup_lat', $lat );
								update_post_meta( get_the_ID(), 'meetup_long', $lng );
							}
							else if ( strcmp( $status, '620' ) == 0 ) {
								// sent geocodes too fast
								$delay += 100000;
							}
							else {
								// failure to geocode
								$geocode_pending = false;
								echo wp_sprintf( __('Adresse "%1s" konnte nicht geocodiert werden. (Status %2s)', 'custom-field-template' ), $location_string, $status );
								// yep, it's a success because the google api responded
								// if we set $success = false then the loop will try to geolocate the same address endlessly
								$success = true;
							}
		
						} while ( ! $success );
					}
					wp_reset_postdata();
				}
			}
				
			$query = $this->get_posts_without_geodata_query();
				
			?>
			<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h2><?php echo __( 'Geodaten Ermitteln', 'custom-field-template' ); ?></h2>
				
				<div id="poststuff" class="metabox-holder has-right-sidebar">

					<!-- Main Column -->
					<div id="post-body">
						<div id="post-body-content">
							<div id="normal-sortables" class="meta-box-sortables ui-sortable">

								<div id="add_template" class="postbox">
									<h3 class="hndle"><span><?php _e( 'Geodaten Status', 'custom-field-template' ); ?></span></h3>
									<div class="inside">
										
										<?php if ( $query->have_posts() ): ?>

											<form action="" method="post">
												<p>
													<?php echo wp_sprintf( __( 'Orte die nächsten %1s Adressdatensätze.', 'custom-field-template' ), '<input type="text" name="addresses_count" value="' . $addresses_count . '" class="small-text">' ); ?>
												</p>
												<p class="submit">
													<input type="submit" class="button-primary" value="<?php _e( 'Daten ermitteln', 'custom-field-template' ) ?>" />
												</p>							
											</form>

											<h4><?php echo $query->post_count . ' ' . __( 'Adressdaten ohne Geodaten', 'custom-field-template' ); ?></h4>
											<ul>
											<?php while ( $query->have_posts() ): ?>
												<?php $query->the_post(); ?>
												<li><?php the_title(); ?></li>
											<?php endwhile; ?>
											</ul>
										<?php else: ?>
											<?php _e( 'Alle vorhanden Adressen besitzen Geodaten.', 'custom-field-template' ); ?>
										<?php endif ?>
										<?php wp_reset_postdata(); ?>


										<br class="clear" />

									</div> <!-- .inside -->

								</div> <!-- #add_template -->

							</div> <!-- #normal-sortables -->
						</div> <!-- #post-body-content -->
					</div> <!-- #post-body -->
					

				</div> <!-- #poststuff -->
			</div> <!-- .wrap -->
			<?php
		}
		
		/**
		 * The pluploader
		 *
		 * @access	private
		 * @since	0.2
		 * @return	void
		 */
		private function draw_galleries_uploader() {
			?>
			<div id="plupload-upload-ui" class="hide-if-no-js">
				<div id="drag-drop-area">
				<div class="drag-drop-inside">
					<p class="drag-drop-info">Bilder hier her legen</p>
				</div>
				</div>
			</div>
			<?php
			$this->draw_gallery_items();
		}
		
		/**
		 * Display the gallery items
		 *
		 * @access private
		 * @since 0.2
		 * @param int $gallery_id | ID of the gallery post type
		 * @return void 
		 */
		private function draw_gallery_items( $gallery_id = FALSE ) {
	
			// If this a refresh call, the POST parameters will be set
			extract( $_POST );
	
			$post_id = ( FALSE == $gallery_id ) ? get_the_ID() : intval( $gallery_id );
	
			if ( ISSET( $attachment_id ) )
			$attachment_id = intval( $attachment_id );
	
			// Get the gallery items order
			$meta = get_post_meta( $post_id, 'items_order', TRUE );
	
			// Get all attachments related to this gallery
			$attachments = array( );
			if ( $post_id ) {
			$post = get_post( $post_id );
			if ( $post && $post->post_type == 'attachment' )
				$attachments = array( $post->ID => $post );
			else
				$attachments = get_children( array( 'post_parent' => $post_id, 'post_type' => 'attachment', 'orderby' => 'menu_order ASC, ID', 'order' => 'DESC' ) );
			} else {
			if ( is_array( $GLOBALS[ 'wp_the_query' ]->posts ) )
				foreach ( $GLOBALS[ 'wp_the_query' ]->posts as $attachment )
				$attachments[ $attachment->ID ] = $attachment;
			}
	
			// Sort attachments according to saved order
			if ( $meta ) {
			$attachments_ordered = array( );
			foreach ( $meta[ 'igp' ] AS $item_id ) {
				if ( array_key_exists( $item_id, $attachments ) ) {
				$attachments_ordered[ $item_id ] = $attachments[ $item_id ];
				}
			}
			$attachments = $attachments_ordered;
			}
	
			// Start building output
			$output = "<ul id='inpsyde_galleries_pro_media_items'>";
			foreach ( ( array ) $attachments as $id => $attachment ) {
			if ( $attachment->post_status == 'trash' )
				continue;
	
			if ( ( $id = intval( $id ) ) && $thumb_url = wp_get_attachment_image_src( $id, 'thumbnail', true ) )
				$thumb_url = $thumb_url[ 0 ];
			else
				$thumb_url = false;
	
			$output.= $this->get_gallery_item( $post_id, $id );
			}
	
			$output.= "</ul><div style='clear:both'></div>";
	
			echo $output;
	
			// Is this an ajax call?
			if ( ISSET( $_POST[ 'action' ] ) && 'refresh_gallery' == $_POST[ 'action' ] )
			exit;
		}
		
		/**
		 * Do the file upload
		 * 
		 * @since 0.2
		 * @return void;
		 */
		function handle_file_upload() {
	
			check_ajax_referer( 'photo-upload' );
	
			// you can use WP's wp_handle_upload() function:
			$file = $_FILES[ 'async-upload' ];
			$post_id = intval( $_POST[ 'post_id' ] );
	
			$wp_filetype = wp_check_filetype( basename( $file[ 'name' ] ), null );
	
			$allowed_types = array( 'jpg', 'jpeg', 'gif', 'png', 'JPG', 'JPEG', 'GIF', 'PNG' );
	
			// If this is not an image, we don't want it in out gallery ;
			if ( !in_array( $wp_filetype[ 'ext' ], $allowed_types ) )
			return;
	
			$status = wp_handle_upload( $file, array( 'test_form' => true, 'action' => 'photo_gallery_upload' ) );
	
			//Adds file as attachment to WordPress
			$attach_id = wp_insert_attachment( array(
			'post_mime_type' => $status[ 'type' ],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $file[ 'name' ] ) ),
			'post_content' => '',
			'post_status' => 'inherit'
				), $status[ 'file' ], $post_id );
	
			$attach_data = wp_generate_attachment_metadata( $attach_id, $status[ 'file' ] );
			wp_update_attachment_metadata( $attach_id, $attach_data );
	
			// Draw gallery item
			$this->gallery_item( $post_id, $attach_id );
	
			exit;
		}
		
		/**
		 * Output an item in the gallery
		 * 
		 * @access private
		 * @param string $post_id | the gallery ID
		 * @param string $attach_id | ID of the attachment
		 * @since 0.2
		 * @return void
		 */
		private function gallery_item( $post_id, $attach_id ) {
		    
	        echo $this->get_gallery_item( $post_id, $attach_id );
   		}
	
		/**
		 * Return an item in the gallery
		 * 
		 * @param string $post_id | the gallery ID
		 * @param string $attach_id | ID of the attachment
		 * @return string | the list element 
		 */
		private function get_gallery_item( $post_id, $attach_id ) {
	
			if ( ( $attach_id = intval( $attach_id ) ) && $thumb_url = wp_get_attachment_image_src( $attach_id, 'thumbnail', true ) )
				$thumb_url = $thumb_url[ 0 ];
			else
				$thumb_url = false;
	
			$iframed_url = plugins_url( '/inc/', __FILE__ ) . 'class-inpsyde-media-manager.php';
			$edit = 'Drag&Drop to change order';
			$href_edit = "$iframed_url?type=image&post_id=$post_id&attachment_id=$attach_id&TB_iframe=1&height=400&width=600";
			$href_delete = "$iframed_url?post_id=$post_id&attachment_id=$attach_id";
			$editbutton = "<a class='thickbox' href='$href_edit' title='Edit Image'><img src='" . plugins_url( '/images/', __FILE__ ) . 'edit.png' . "' alt='Edit Image' /></a>";
			$deletebutton = "<a class='igp_delete_image' href='$href_delete' title='Delete Image'><img src='" . plugins_url( '/images/', __FILE__ ) . 'delete.png' . "' alt='Delete Image' /></a>";
	
			return "<li id='igp-$attach_id' class='inpsyde_galleries_pro_media_item'><div class='igp_edit_buttons'>$editbutton $deletebutton</div><a title='$edit' href='#'><img class='igp_thumbnail' alt='' src='$thumb_url' /></a></li>";
		}
		
		/**
		 * Save the gallery items order. 
		 * AJAX callback
		 * @since 0.2
		 */
		public function save_items_order() {
	
			parse_str( $_REQUEST[ 'order' ], $order );
	
			echo update_post_meta( intval( $_REQUEST[ 'gallery_id' ] ), 'items_order', $order );
	
			exit;
		}
	
		/**
		 * Update the attachment
		 * 
		 * @return type
		 * @since 0.2
		 */
		function update_attachment() {
	
			//check_admin_referer( 'media-form' );
	
			$errors = null;
	
			if ( ISSET( $_POST[ 'send' ] ) ) {
			$keys = array_keys( $_POST[ 'send' ] );
			$send_id = intval( array_shift( $keys ) );
			}
	
			if ( !empty( $_POST[ 'attachments' ] ) )
			foreach ( $_POST[ 'attachments' ] as $attachment_id => $attachment ) {
				$post = $_post = get_post( $attachment_id, ARRAY_A );
				$post_type_object = get_post_type_object( $post[ 'post_type' ] );
	
				if ( !current_user_can( $post_type_object->cap->edit_post, $attachment_id ) )
				continue;
	
				if ( ISSET( $attachment[ 'post_content' ] ) )
				$post[ 'post_content' ] = $attachment[ 'post_content' ];
				if ( ISSET( $attachment[ 'post_title' ] ) )
				$post[ 'post_title' ] = $attachment[ 'post_title' ];
				if ( ISSET( $attachment[ 'post_excerpt' ] ) )
				$post[ 'post_excerpt' ] = $attachment[ 'post_excerpt' ];
				if ( ISSET( $attachment[ 'menu_order' ] ) )
				$post[ 'menu_order' ] = $attachment[ 'menu_order' ];
	
				if ( ISSET( $send_id ) && $attachment_id == $send_id ) {
				if ( ISSET( $attachment[ 'post_parent' ] ) )
					$post[ 'post_parent' ] = $attachment[ 'post_parent' ];
				}
	
				$post = apply_filters( 'attachment_fields_to_save', $post, $attachment );
	
				if ( ISSET( $attachment[ 'image_alt' ] ) ) {
				$image_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
				if ( $image_alt != stripslashes( $attachment[ 'image_alt' ] ) ) {
					$image_alt = wp_strip_all_tags( stripslashes( $attachment[ 'image_alt' ] ), true );
					// update_meta expects slashed
					update_post_meta( $attachment_id, '_wp_attachment_image_alt', addslashes( $image_alt ) );
				}
				}
	
				if ( ISSET( $post[ 'errors' ] ) ) {
				$errors[ $attachment_id ] = $post[ 'errors' ];
				unset( $post[ 'errors' ] );
				}
	
				if ( $post != $_post )
				wp_update_post( $post );
	
				foreach ( get_attachment_taxonomies( $post ) as $t ) {
				if ( ISSET( $attachment[ $t ] ) )
					wp_set_object_terms( $attachment_id, array_map( 'trim', preg_split( '/,+/', $attachment[ $t ] ) ), $t, false );
				}
			}
	
			if ( ISSET( $_POST[ 'insert-gallery' ] ) || ISSET( $_POST[ 'update-gallery' ] ) ) {
			?>
			<script type="text/javascript">
				/* <![CDATA[ */
				var win = window.dialogArguments || opener || parent || top;
				win.tb_remove();
				/* ]]> */
			</script>
			<?php
			exit;
			}
	
			if ( ISSET( $send_id ) ) {
			$attachment = stripslashes_deep( $_POST[ 'attachments' ][ $send_id ] );
	
			$html = $attachment[ 'post_title' ];
			if ( !empty( $attachment[ 'url' ] ) ) {
				$rel = '';
				if ( strpos( $attachment[ 'url' ], 'attachment_id' ) || get_attachment_link( $send_id ) == $attachment[ 'url' ] )
				$rel = " rel='attachment wp-att-" . esc_attr( $send_id ) . "'";
				$html = "<a href='{$attachment[ 'url' ]}'$rel>$html</a>";
			}
	
			$html = apply_filters( 'media_send_to_editor', $html, $send_id, $attachment );
			return media_send_to_editor( $html );
			}
	
			return $errors;
		}
	
		/**
		 * Delete gallery item.
		 * AJAX Callback
		 * @since 0.2
		 */
		public function delete_gallery_item() {
	
			// Delete attachment
			wp_delete_attachment( intval( $_POST[ 'attachment_id' ] ) );
	
			// Return the updated gallery
			$this->draw_gallery_items( intval( $_POST[ 'gallery_id' ] ) );
	
			exit;
		}
	}
}