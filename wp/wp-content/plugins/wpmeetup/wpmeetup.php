<?php

/*
 Plugin Name: WPMeetup CTP
 Version: 0.1
 Author: Inpsyde GmbH
 Author URI: http://inpsyde.com
 */

if ( ! class_exists( 'Wpmeetup' ) ) {
	
	if ( ! defined( 'GOOGLE_API_KEY' ) ) {
		// get your own key
		// http://code.google.com/apis/maps/signup.html
		define( 'GOOGLE_API_KEY', 'ABQIAAAALKh_xJZ2AHg__In1-EMSdRT2yXp_ZAY8_ufC3CFXhHIE1NvwkxQbXpVNDP3gBdW5R5nGtenLOnXrKQ' );
	}

	if ( function_exists( 'add_filter' ) )
		add_filter( 'plugins_loaded', array( 'Wpmeetup', 'get_instance' ) );
	
	class Wpmeetup {
		
		/**
		 * Instance holder
		 *
		 * @static
		 * @since 0.1
		 * @var NULL | Wpmeetup
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @since 0.1
		 * @return Wpmeetup
		 */
		public static function get_instance() {
			if( ! self::$instance )
				self::$instance = new self;
			return self::$instance;
		}
		
		/**
		 * Setting up some data and start the hooks
		 *
		 * @since 0.1
		 * @return void
		 */
		public function __construct() {
			// Load Custom Post Type
			add_filter( 'init', array( $this, 'init_post_type' ) );
			
			if ( 'wpmeetups' == $_GET[ 'post_type' ] || 'wpmeetups' == get_post_type( $_GET[ 'post' ] ) ) {
				// Add the Meta-Boxes
				add_filter( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
			}
			
			add_filter( 'admin_menu', array( $this, 'admin_menu' ) );
			
			// Save Post-Meta
			add_filter( 'save_post', array( $this, 'save_post' ) );
		}
		
		/**
		 * Register the Custom Post Type
		 * 
		 * @since 0.1
		 * @return void
		 */
		public function init_post_type() {
			
			$labels = array(
				'name'				=> 'Meetup',
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
				'author'
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
		 * Adds the meta box to the post edition page
		 *
		 * @since 0.1
		 * @uses add_meta_box
		 * @return void
		 */
		public function add_meta_boxes() {
			add_meta_box(
				'wpmeetup_metabox', 
				'Wichtige Angaben zum Meetup',
				array( $this, 'display_meta_box' ),
				'wpmeetups', 'advanced', 'default'
			);
		}
		
		/**
		 * Displays the meta box
		 *
		 * @since 0.1
		 * @uses wp_nonce_field, plugin_basename
		 * @return void
		 */
		public function display_meta_box( $post ) {
			
			// Get Meetup Meta
			$meetup_meta = get_post_meta( $post->ID, 'meetup', TRUE );
			
			// Add Nonce Field for security options
			wp_nonce_field( plugin_basename( __FILE__ ), 'wpmeetup_nonce' );
			?>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="meetup[town]">Stadt</label>
						</th>
						<td>
							<input id="meetup[town]" name="meetup[town]" type="text" value="<?php echo $meetup_meta[ 'town' ]; ?>" tabindex="1" />
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">
							<label for="meetup[location]">Location</label>
						</th>
						<td>
							<input id="meetup[location]" name="meetup[location]" type="text" value="<?php echo $meetup_meta[ 'location' ]; ?>" tabindex="1" />
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">
							<label for="meetup[location_url]">Location URL</label>
						</th>
						<td>
							<input id="meetup[location_url]" name="meetup[location_url]" type="text" value="<?php echo $meetup_meta[ 'location_url' ]; ?>" tabindex="1" />
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">
							<label for="meetup[street]">Strasse</label>
						</th>
						<td>
							<input id="meetup[street]" name="meetup[street]" type="text" value="<?php echo $meetup_meta[ 'street' ]; ?>" tabindex="1" />
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">
							<label for="meetup[number]">Nummer</label>
						</th>
						<td>
							<input id="meetup[number]" name="meetup[number]" type="text" value="<?php echo $meetup_meta[ 'number' ]; ?>" tabindex="1" />
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">
							<label for="meetup[plz]">Postleitzahl</label>
						</th>
						<td>
							<input id="meetup[plz]" name="meetup[plz]" type="text" value="<?php echo $meetup_meta[ 'plz' ]; ?>" tabindex="1" />
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">
							<label for="meetup[date]">Datum (dd.mm.jjjj)</label>
						</th>
						<td>
							<input id="meetup[date]" name="meetup[date]" type="text" value="<?php echo $meetup_meta[ 'date' ]; ?>" tabindex="1" />
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">
							<label for="meetup[time]">Zeit (hh:mm)</label>
						</th>
						<td>
							<input id="meetup[time]" name="meetup[time]" type="text" value="<?php echo $meetup_meta[ 'time' ]; ?>" tabindex="1" />
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">
							<label for="meetup[latitude]">Latitude</label>
						</th>
						<td>
							<input id="meetup[latitude]" name="meetup[latitude]" type="text" value="<?php echo $meetup_meta[ 'latitude' ]; ?>" tabindex="1" />
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">
							<label for="meetup[longitude]">Longitude</label>
						</th>
						<td>
							<input id="meetup[longitude]" name="meetup[longitude]" type="text" value="<?php echo $meetup_meta[ 'longitude' ]; ?>" tabindex="1" />
						</td>
					</tr>
				</tbody>
			</table>
			<?php
		}
		
		/**
		 * Save the post to the blogs
		 *
		 * @since 0.1
		 * @return void
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
		
		public function admin_menu() {
			add_submenu_page( 'edit.php?post_type=wpmeetups', 'Geodaten ermitteln', 'Geodaten ermitteln', 'read', basename( __FILE__ ), array( $this, 'determine_geodata_settings_page' ) );
		}
		
		private function get_posts_without_geodata_query() {
			global $wpdb;
				
			$posts_with_geodata = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key IN ('meetup_lat', 'meetup_long') AND meta_value != ''" );
		
			$query = new WP_Query( array(
				'post_type' => 'wpmeetups',
				'posts_per_page' => '-1',
				'post__not_in' => $posts_with_geodata
			) );
				
			return $query;
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
								$meetup_data[ 'longitude' ] = $lng;
								$meetup_data[ 'latitude' ] = $lat;
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
	}
}